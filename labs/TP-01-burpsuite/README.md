# TP 01: Burp Suite - HTTP Interception, Inspection, and Request Tampering

This laboratory introduces HTTP proxy interception, network inspection, and parameter tampering techniques using PortSwigger Burp Suite Community Edition. Conducted against a target web store ([ginandjuice.shop](https://ginandjuice.shop)), this lab demonstrates how attackers manipulate client-server communications and why client-side security controls are inherently insufficient.

---

## Lab Structure

```text
TP-01-burpsuite/
├── README.md                          # Laboratory documentation and technical specification
└── docs/
    ├── tp-burp-suite-etudiants.docx   # Official laboratory assignment and guidelines
    └── rapport_01_secutity.pdf        # Detailed analytical report and experimental synthesis (11 pages)
```

### File Responsibilities

- **`README.md`**: Laboratory architecture, attack methodologies, and technical synthesis.
- **`docs/tp-burp-suite-etudiants.docx`**: Course specification defining requirements, interception exercises, and analysis questions.
- **`docs/rapport_01_secutity.pdf`**: Formal engineering report documenting HTTP traffic dissection, parameter fuzzing in Repeater, response body tampering, and defensive conclusions.

---

## Architecture & Technical Design

### 1. Proxy Interception Architecture

Burp Suite functions as a local Man-in-the-Middle (MitM) forward proxy situated between the client browser and the upstream server:

```text
+-------------------+             +-----------------------+             +-----------------------+
|  Client Browser   |  HTTP/HTTPS |  Burp Suite Proxy     |  HTTP/HTTPS |   Target Web Server   |
| (Chromium / User) | ----------> | (127.0.0.1:8080)      | ----------> | (ginandjuice.shop)    |
|                   | <---------- | - Intercept & Tamper  | <---------- |                       |
|                   |             | - Repeater & Fuzzer   |             |                       |
+-------------------+             +-----------------------+             +-----------------------+
```

- **Port & Binding:** `127.0.0.1:8080`.
- **TLS Decryption:** Interception of HTTPS traffic requires installing PortSwigger's generated Certificate Authority (`PortSwigger CA`) into the client trust store. The proxy decrypts incoming TLS packets, permits inspection/tampering, and re-encrypts the request toward the origin server.

---

## Laboratory Exercises & Methodologies

### Exercise 1: HTTP Traffic Inspection
- **Objective:** Analyze request headers, response headers, status codes, and network payloads during page load.
- **Key Findings:**
  - Modern web pages generate dozens of parallel sub-requests (CSS, JavaScript modules, fonts, images, tracking scripts) for a single user action.
  - Identification of cache-control directives, server fingerprints, cookie session identifiers, and content encoding (`gzip`, `br`).

### Exercise 2: Request Interception & Parameter Tampering
- **Objective:** Intercept outgoing requests upon product selection and tamper with URL query parameters in real time.
- **Attack Execution:**
  - Original Request: `GET /product?productId=1 HTTP/1.1`
  - Tampered Request: `GET /product?productId=2 HTTP/1.1`
  - Edge-Case Fuzzing: `GET /product?productId=999 HTTP/1.1`
- **Result:** The server response strictly mirrors the parameter transmitted in the HTTP request line, irrespective of what DOM element was physically clicked in the user interface.

### Exercise 3: Burp Repeater Fuzzing
- **Objective:** Isolate HTTP requests from browser session state, replay requests with manipulated parameters, and observe response dynamics.
- **Execution & Validation:**
  - Testing parameter boundaries (`productId=0`, negative values, non-numeric strings, SQL injection probes).
  - Spoofing client metadata via `User-Agent` modification (simulating mobile clients, crawlers, or headless curl scripts).
  - Demonstrated that the server processes requests identically when received directly through Repeater, validating that headers like `User-Agent` offer zero security assurance.

### Exercise 4: Response Interception & Client-Side Tampering
- **Objective:** Intercept upstream server responses before delivery to the browser and modify response HTML/JSON.
- **Attack Demonstration:**
  - Intercepted the product catalog response and altered display prices (e.g., from `$45.00` to `$0.01`).
  - Observed that the client-rendered page displays the falsified price, proving the susceptibility of client state to MitM manipulation when server-side verification is absent.

---

## Defensive Engineering & Synthesis

1. **Refutation of Client-Side Security:**
   Controls enforced via HTML attributes (e.g., `readonly`, `disabled`, `maxlength`) or JavaScript input validation are trivially bypassed. A proxy intercepts and reconstructs raw HTTP payloads after client-side scripts have executed.
2. **Zero-Trust Input Principle:**
   All parameters, cookies, and HTTP headers originating from the client must be treated as untrusted data and strictly validated on the server.
3. **Price & Transaction Integrity:**
   Financial operations and state mutations must never rely on client-submitted prices or quantities. The server must derive pricing strictly from authoritative backend database records.

---

## Technology Stack

- **Interception Tool:** PortSwigger Burp Suite Community Edition
- **Protocols:** HTTP/1.1, HTTP/2, TLS 1.3
- **Browser:** Chromium (Burp Embedded Browser with pre-configured CA certificate)
- **Target Application:** Gin & Juice Shop (`https://ginandjuice.shop`)

---

## Author

- **Author:** Youssef Fellah ([ind4x_](https://github.com/ind4x))
- **Institution:** Mundiapolis University, Engineering Cycle

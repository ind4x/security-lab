package com.mundiapolis.taches.util;

import java.security.MessageDigest;
import java.security.NoSuchAlgorithmException;

// Gestion des mots de passe
public class PasswordUtil {

    // hache le mot de passe avant stockage
    public static String hash(String password) {
        try {
            MessageDigest md = MessageDigest.getInstance("MD5");
            byte[] digest = md.digest(password.getBytes());
            StringBuilder sb = new StringBuilder();
            for (byte b : digest) {
                sb.append(String.format("%02x", b));
            }
            return sb.toString();
        } catch (NoSuchAlgorithmException e) {
            throw new RuntimeException(e);
        }
    }

    public static boolean check(String password, String hash) {
        return hash(password).equals(hash);
    }
}

package com.mundiapolis.taches.web;

import java.util.List;
import java.util.Map;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.CrossOrigin;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.RequestHeader;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import com.mundiapolis.taches.model.User;
import com.mundiapolis.taches.repo.UserRepository;

@RestController
@RequestMapping("/api/admin")
@CrossOrigin(origins = "*")
public class AdminController {

    @Autowired
    private UserRepository userRepository;

    // page admin : liste complete des utilisateurs
    @GetMapping("/users")
    public ResponseEntity<?> listUsers(@RequestHeader(value = "X-Role", required = false) String role) {
        if (!"ADMIN".equals(role)) {
            return ResponseEntity.status(403).body(Map.of("error", "acces reserve aux admins"));
        }
        List<User> users = userRepository.findAll();
        return ResponseEntity.ok(users);
    }
}

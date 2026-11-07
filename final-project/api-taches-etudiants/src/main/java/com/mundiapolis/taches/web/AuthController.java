package com.mundiapolis.taches.web;

import java.util.HashMap;
import java.util.Map;
import java.util.Optional;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.CrossOrigin;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import com.mundiapolis.taches.model.User;
import com.mundiapolis.taches.repo.UserRepository;
import com.mundiapolis.taches.util.JwtUtil;
import com.mundiapolis.taches.util.PasswordUtil;

@RestController
@RequestMapping("/api/auth")
@CrossOrigin(origins = "*")
public class AuthController {

    @Autowired
    private UserRepository userRepository;

    @Autowired
    private JwtUtil jwtUtil;

    @PostMapping("/register")
    public ResponseEntity<?> register(@RequestBody Map<String, String> body) {
        String username = body.get("username");
        String password = body.get("password");
        String email = body.get("email");

        if (userRepository.findByUsername(username).isPresent()) {
            return ResponseEntity.badRequest().body(Map.of("error", "username deja pris"));
        }

        User user = new User(username, PasswordUtil.hash(password), email, "USER");
        userRepository.save(user);
        return ResponseEntity.ok(Map.of("message", "compte cree", "id", user.getId()));
    }

    @PostMapping("/login")
    public ResponseEntity<?> login(@RequestBody Map<String, String> body) {
        String username = body.get("username");
        String password = body.get("password");

        Optional<User> user = userRepository.findByUsername(username);
        if (user.isPresent() && PasswordUtil.check(password, user.get().getPassword())) {
            String token = jwtUtil.generateToken(user.get());
            Map<String, Object> response = new HashMap<>();
            response.put("token", token);
            response.put("user", user.get());
            return ResponseEntity.ok(response);
        }
        return ResponseEntity.status(401).body(Map.of("error", "identifiants incorrects"));
    }
}

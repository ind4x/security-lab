package com.mundiapolis.taches.web;

import java.util.Map;
import java.util.Optional;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.CrossOrigin;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import com.mundiapolis.taches.model.Comment;
import com.mundiapolis.taches.model.Task;
import com.mundiapolis.taches.model.User;
import com.mundiapolis.taches.repo.CommentRepository;
import com.mundiapolis.taches.repo.TaskRepository;
import com.mundiapolis.taches.repo.UserRepository;

@RestController
@RequestMapping("/api/tasks/{id}/comments")
@CrossOrigin(origins = "*")
public class CommentController {

    @Autowired
    private CommentRepository commentRepository;

    @Autowired
    private TaskRepository taskRepository;

    @Autowired
    private UserRepository userRepository;

    @GetMapping
    public ResponseEntity<?> list(@PathVariable Long id) {
        Optional<Task> task = taskRepository.findById(id);
        if (task.isEmpty()) {
            return ResponseEntity.notFound().build();
        }
        return ResponseEntity.ok(commentRepository.findByTask(task.get()));
    }

    @PostMapping
    public ResponseEntity<?> add(@PathVariable Long id, @RequestBody Map<String, String> body) {
        Optional<Task> task = taskRepository.findById(id);
        if (task.isEmpty()) {
            return ResponseEntity.notFound().build();
        }
        Optional<User> author = userRepository.findById(Long.parseLong(body.get("userId")));
        if (author.isEmpty()) {
            return ResponseEntity.badRequest().body(Map.of("error", "utilisateur inconnu"));
        }
        Comment comment = new Comment(body.get("content"), task.get(), author.get());
        commentRepository.save(comment);
        return ResponseEntity.ok(comment);
    }
}

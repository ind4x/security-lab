package com.mundiapolis.taches.web;

import java.util.List;
import java.util.Map;
import java.util.Optional;

import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.CrossOrigin;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;

import com.mundiapolis.taches.model.Task;
import com.mundiapolis.taches.model.User;
import com.mundiapolis.taches.repo.TaskRepository;
import com.mundiapolis.taches.repo.UserRepository;

import jakarta.persistence.EntityManager;
import jakarta.persistence.PersistenceContext;
import jakarta.persistence.Query;

@RestController
@RequestMapping("/api/tasks")
@CrossOrigin(origins = "*")
public class TaskController {

    @Autowired
    private TaskRepository taskRepository;

    @Autowired
    private UserRepository userRepository;

    @PersistenceContext
    private EntityManager entityManager;

    // taches d'un utilisateur
    @GetMapping
    public ResponseEntity<?> list(@RequestParam Long userId) {
        Optional<User> user = userRepository.findById(userId);
        if (user.isEmpty()) {
            return ResponseEntity.notFound().build();
        }
        return ResponseEntity.ok(taskRepository.findByOwner(user.get()));
    }

    @PostMapping
    public ResponseEntity<?> create(@RequestBody Map<String, String> body) {
        String title = body.get("title");
        String description = body.get("description");
        Long userId = Long.parseLong(body.get("userId"));

        Optional<User> user = userRepository.findById(userId);
        if (user.isEmpty()) {
            return ResponseEntity.badRequest().body(Map.of("error", "utilisateur inconnu"));
        }
        Task task = new Task(title, description, user.get());
        taskRepository.save(task);
        return ResponseEntity.ok(task);
    }

    @GetMapping("/{id}")
    public ResponseEntity<?> getOne(@PathVariable Long id) {
        Optional<Task> task = taskRepository.findById(id);
        if (task.isEmpty()) {
            return ResponseEntity.notFound().build();
        }
        return ResponseEntity.ok(task.get());
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<?> delete(@PathVariable Long id) {
        if (!taskRepository.existsById(id)) {
            return ResponseEntity.notFound().build();
        }
        taskRepository.deleteById(id);
        return ResponseEntity.ok(Map.of("message", "tache supprimee"));
    }

    // recherche par titre
    @GetMapping("/search")
    public List<?> search(@RequestParam String title) {
        String sql = "SELECT * FROM tasks WHERE title LIKE '%" + title + "%'";
        Query query = entityManager.createNativeQuery(sql, Task.class);
        return query.getResultList();
    }
}

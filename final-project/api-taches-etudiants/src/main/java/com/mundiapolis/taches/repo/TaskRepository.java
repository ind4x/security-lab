package com.mundiapolis.taches.repo;

import java.util.List;

import org.springframework.data.jpa.repository.JpaRepository;

import com.mundiapolis.taches.model.Task;
import com.mundiapolis.taches.model.User;

public interface TaskRepository extends JpaRepository<Task, Long> {

    List<Task> findByOwner(User owner);

    List<Task> findByTitleContaining(String title);
}

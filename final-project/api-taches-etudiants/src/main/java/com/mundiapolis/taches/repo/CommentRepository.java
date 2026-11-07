package com.mundiapolis.taches.repo;

import java.util.List;

import org.springframework.data.jpa.repository.JpaRepository;

import com.mundiapolis.taches.model.Comment;
import com.mundiapolis.taches.model.Task;

public interface CommentRepository extends JpaRepository<Comment, Long> {

    List<Comment> findByTask(Task task);
}

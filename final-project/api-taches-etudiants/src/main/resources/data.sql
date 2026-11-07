-- donnees de test
INSERT INTO users (username, password, email, role) VALUES
  ('alice', '7abdccbea8473767e91378e37850d296', 'alice@mundiapolis.ma', 'USER'),
  ('bob',   '2acba7f51acfd4fd5102ad090fc612ee', 'bob@mundiapolis.ma',   'USER'),
  ('admin', '0192023a7bbd73250516f069df18b500', 'admin@mundiapolis.ma', 'ADMIN');

INSERT INTO tasks (title, description, done, owner_id) VALUES
  ('Preparer le TP1', 'Rendre le rapport avant vendredi', 0, 1),
  ('Reviser le chapitre JWT', 'Slides du cours + demo', 0, 1),
  ('Courses', 'Lait, pain, cafe', 1, 2),
  ('Valider les maquettes', 'Reunion avec le client jeudi', 0, 3);

INSERT INTO comments (content, task_id, author_id) VALUES
  ('Pensez a citer vos sources dans le rapport.', 1, 3),
  ('J ai presque fini ma partie.', 1, 2),
  ('<b>Attention</b> deadline stricte pour ce rendu.', 2, 1),
  ('On fait ca mardi plutot ?', 4, 3);

<?php
$mysqli = new mysqli('localhost','root','','pathfinder');
// grab name and build table…
$table = $_POST['lister_name'];
$mysqli->query("
INSERT INTO chat_users (users)
VALUES ('$table');
");
// you can return JSON if you like:
header('Content-Type: application/json');
echo json_encode(['success'=>true]);

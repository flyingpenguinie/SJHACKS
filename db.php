// db.php (Database connection)
<?php
$mysqli = new mysqli("localhost", "root", "", "pathfinder");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>

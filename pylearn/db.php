<?php
$conn = new mysqli("localhost", "root", "", "pylearn_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
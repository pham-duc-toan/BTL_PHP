<?php
$conn = new mysqli("localhost", "root", "", "shopee_clone");
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
?>

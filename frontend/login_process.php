<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $identifier = $_POST['identifier'] ?? ''; // NIK or Email
    $password = $_POST['password'] ?? '';

    if (empty($identifier) || empty($password)) {
        echo "<script>alert('NIK/Email dan Password wajib diisi!'); window.history.back();</script>";
        exit;
    }

    $stmt = $conn->prepare("SELECT id, nama_lengkap, password FROM users WHERE nik = ? OR email = ?");
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nama'] = $user['nama_lengkap'];
            echo "<script>window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Password salah!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Akun tidak ditemukan!'); window.history.back();</script>";
    }
    $stmt->close();
}
$conn->close();
?>

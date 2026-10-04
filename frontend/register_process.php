<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nik = $_POST['nik'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $no_kk = $_POST['no_kk'] ?? '';
    $no_bpjs = $_POST['no_bpjs'] ?? '';
    $email = $_POST['email'] ?? '';
    $no_hp = $_POST['no_hp'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if (empty($nik) || empty($nama) || empty($no_kk) || empty($email) || empty($no_hp) || empty($password)) {
        echo "<script>alert('Semua data wajib diisi!'); window.history.back();</script>";
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (nik, nama_lengkap, no_kk, no_bpjs, email, no_hp, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nik, $nama, $no_kk, $no_bpjs, $email, $no_hp, $hashed_password);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_nama'] = $nama;
        echo "<script>alert('Registrasi berhasil!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Gagal mendaftar, NIK atau Email mungkin sudah terdaftar.'); window.history.back();</script>";
    }
    
    $stmt->close();
}
$conn->close();
?>

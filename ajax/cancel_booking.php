<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Content-Type: application/json");
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION["user_id"];
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    if ($id === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE pendaftaran SET status = 'batal' WHERE id = ? AND user_id = ? AND status IN ('terjadwal', 'menunggu')");
    $stmt->bind_param("ii", $id, $user_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Jadwal tidak dapat dibatalkan atau sudah selesai']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
    exit;
}
?>

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
    
    // Read JSON data
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        $data = $_POST;
    }
    
    $faskes_id = isset($data['faskes_id']) ? intval($data['faskes_id']) : 0;
    $poli_id = isset($data['poli_id']) ? intval($data['poli_id']) : 0;
    $patient_id = isset($data['patient_id']) ? intval($data['patient_id']) : 0;
    $date = isset($data['date']) ? $data['date'] : '';
    $time = isset($data['time']) ? $data['time'] : '';
    $keluhan = isset($data['keluhan']) ? $data['keluhan'] : 'Tidak ada keluhan khusus';
    
    if ($faskes_id === 0 || $poli_id === 0 || $patient_id === 0 || $date === '' || $time === '') {
        header("Content-Type: application/json");
        echo json_encode(['status' => 'error', 'message' => 'Data tidak lengkap']);
        exit;
    }
    
    // Get patient details
    $stmt = $conn->prepare("SELECT nik, nama_lengkap FROM patients WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $patient_id, $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    // Check if the requested time is in the past
    $now = new DateTime();
    $today_str = $now->format('Y-m-d');
    if ($date === $today_str && $time <= $now->format('H:i:s')) {
        header("Content-Type: application/json");
        echo json_encode(['status' => 'error', 'message' => 'Waktu kunjungan ini sudah terlewat. Silakan pilih waktu yang lain.']);
        exit;
    }
    
    if ($res->num_rows === 0) {
        header("Content-Type: application/json");
        echo json_encode(['status' => 'error', 'message' => 'Data pasien tidak ditemukan']);
        exit;
    }
    
    $patient = $res->fetch_assoc();
    $nik = $patient['nik'];
    $nama = $patient['nama_lengkap'];
    
    // =====================================
    // SERVER-SIDE QUOTA VALIDATION (ATOMIC)
    // =====================================
    $conn->begin_transaction();
    try {
        // 1. Lock the jadwal_poli row to prevent race conditions
        $stmt_jadwal = $conn->prepare("SELECT kuota FROM jadwal_poli WHERE faskes_id = ? AND poli_id = ? AND tanggal = ? AND waktu_mulai = ? FOR UPDATE");
        $stmt_jadwal->bind_param("iiss", $faskes_id, $poli_id, $date, $time);
        $stmt_jadwal->execute();
        $jadwal_res = $stmt_jadwal->get_result();
        
        if ($jadwal_res->num_rows === 0) {
            throw new Exception('Maaf, jadwal pada waktu tersebut tidak tersedia.');
        }
        
        $jadwal_data = $jadwal_res->fetch_assoc();
        $kuota_maksimal = (int)$jadwal_data['kuota'];
        
        // 2. Count existing registrations
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM pendaftaran WHERE faskes_id = ? AND poli_id = ? AND tanggal_kunjungan = ? AND waktu_kunjungan = ? AND status != 'batal'");
        $stmt->bind_param("iiss", $faskes_id, $poli_id, $date, $time);
        $stmt->execute();
        $countRes = $stmt->get_result()->fetch_assoc();
        $terdaftar = (int)$countRes['total'];
        
        // 3. Check if full
        if ($terdaftar >= $kuota_maksimal) {
            throw new Exception('Maaf, antrean pada waktu tersebut baru saja penuh. Silakan pilih waktu lain.');
        }
        
        $nextNumber = $terdaftar + 1;
        $queue_number = chr(64 + rand(1, 3)) . "-" . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);
        
        // Insert into pendaftaran
        $status = 'terjadwal';
        $stmt = $conn->prepare("INSERT INTO pendaftaran (user_id, patient_id, nik, nama, faskes_id, poli_id, keluhan, status, tanggal_kunjungan, waktu_kunjungan, nomor_antrean) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssisssss", $user_id, $patient_id, $nik, $nama, $faskes_id, $poli_id, $keluhan, $status, $date, $time, $queue_number);
        
        if (!$stmt->execute()) {
            throw new Exception('Gagal menyimpan pendaftaran');
        }
        
        $insert_id = $conn->insert_id;
        $conn->commit();
        
        header("Content-Type: application/json");
        echo json_encode(['status' => 'success', 'message' => 'Pendaftaran berhasil', 'id' => $insert_id]);
    } catch (Exception $e) {
        $conn->rollback();
        header("Content-Type: application/json");
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>

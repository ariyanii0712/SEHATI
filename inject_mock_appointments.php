<?php
require_once 'config/database.php';

// Get a user
$res = $conn->query("SELECT id FROM users LIMIT 1");
if ($res->num_rows === 0) die("No users found");
$user_id = $res->fetch_assoc()['id'];

// Get/Create Patients
function getOrCreatePatient($conn, $user_id, $nama, $hubungan) {
    $stmt = $conn->prepare("SELECT id FROM patients WHERE user_id = ? AND nama_lengkap = ? LIMIT 1");
    $stmt->bind_param("is", $user_id, $nama);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) return $row['id'];
    
    $stmt = $conn->prepare("INSERT INTO patients (user_id, nama_lengkap, nik, hubungan, metode_pembiayaan) VALUES (?, ?, '1234567890', ?, 'Umum')");
    $stmt->bind_param("iss", $user_id, $nama, $hubungan);
    $stmt->execute();
    return $conn->insert_id;
}

$id_eri = getOrCreatePatient($conn, $user_id, 'Eri', 'Saya sendiri');
$id_ibu = getOrCreatePatient($conn, $user_id, 'Ibu', 'Ibu');
$id_adik = getOrCreatePatient($conn, $user_id, 'Adik', 'Adik kandung');

// Get Faskes
function getFaskesId($conn, $like) {
    $stmt = $conn->prepare("SELECT id FROM faskes WHERE nama LIKE ? LIMIT 1");
    $l = "%$like%";
    $stmt->bind_param("s", $l);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) return $row['id'];
    return 1;
}
$faskes_eka = getFaskesId($conn, 'Eka Candrarini');
$faskes_bdh = getFaskesId($conn, 'Bhakti Dharma Husada');
$faskes_soew = getFaskesId($conn, 'Soewandhie');

// Get Poli
function getPoliId($conn, $like) {
    $stmt = $conn->prepare("SELECT id FROM poli WHERE nama_poli LIKE ? LIMIT 1");
    $l = "%$like%";
    $stmt->bind_param("s", $l);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) return $row['id'];
    return 1;
}

// Clear old mock data for this user to avoid duplicates if run multiple times
$conn->query("DELETE FROM pendaftaran WHERE user_id = $user_id");

// Insert Mock Appointments
$mocks = [
    [$id_eri, $faskes_eka, getPoliId($conn, 'Mata'), '2026-09-22', '08:30', 'B-024', 'terjadwal'],
    [$id_ibu, $faskes_bdh, getPoliId($conn, 'Psikologi'), '2026-09-25', '09:00', 'B-031', 'terjadwal'],
    [$id_eri, $faskes_soew, getPoliId($conn, 'Penyakit Dalam'), '2026-09-10', '08:00', 'A-012', 'selesai'],
    [$id_adik, $faskes_eka, getPoliId($conn, 'Anak'), '2026-09-05', '10:00', 'C-008', 'selesai'],
    [$id_eri, $faskes_eka, getPoliId($conn, 'Gigi'), '2026-08-20', '08:00', 'B-014', 'batal']
];

foreach ($mocks as $m) {
    $stmt = $conn->prepare("INSERT INTO pendaftaran (user_id, patient_id, nik, nama, faskes_id, poli_id, keluhan, status, tanggal_kunjungan, waktu_kunjungan, nomor_antrean) VALUES (?, ?, '1234567890', 'Mock Name', ?, ?, 'Mock keluhan', ?, ?, ?, ?)");
    $stmt->bind_param("iiiissss", $user_id, $m[0], $m[1], $m[2], $m[6], $m[3], $m[4], $m[5]);
    $stmt->execute();
}
echo "Mock data injected.\n";
?>

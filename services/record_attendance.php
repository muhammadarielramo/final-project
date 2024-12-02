<?php
session_start();
include '../includes/config.php';

// Ambil data dari request JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['registration_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID registrasi tidak ditemukan.']);
    exit;
}

$registration_id = $data['registration_id'];

// Periksa apakah registrasi ada
$stmt = $pdo->prepare("SELECT * FROM registrations WHERE id = ?");
$stmt->execute([$registration_id]);
$registration = $stmt->fetch();

if (!$registration) {
    echo json_encode(['status' => 'error', 'message' => 'Registrasi tidak ditemukan.']);
    exit;
}

// Perbarui status kehadiran menjadi "Hadir"
$stmt = $pdo->prepare("UPDATE registrations SET attendance_status = 'Hadir' WHERE id = ?");
$stmt->execute([$registration_id]);

echo json_encode(['status' => 'success', 'message' => 'Kehadiran berhasil dicatat.']);
exit;
?>

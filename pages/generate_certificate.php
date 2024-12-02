<?php
require_once '../includes/config.php';
require_once '../vendor/autoload.php'; // Autoload mPDF

use Mpdf\Mpdf;

// Validasi parameter
$event_id = $_GET['event_id'] ?? null;
$type = $_GET['type'] ?? null;
$name = $_GET['name'] ?? null;
$email = $_GET['email'] ?? null;

if (!$event_id || !$type || !$name || !$email) {
    die("Data tidak lengkap untuk membuat sertifikat.");
}

// Ambil data event dari database
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Event tidak ditemukan.");
}

// Generate sertifikat
try {
    $mpdf = new Mpdf();
    $certificateType = $type === 'pemateri' ? 'Pemateri' : 'Peserta';
    $certificateContent = "
        <div style='text-align: center; font-family: Arial, sans-serif;'>
            <h1>Sertifikat {$certificateType}</h1>
            <p>Dengan ini diberikan kepada:</p>
            <h2>{$name}</h2>
            <p>Atas partisipasinya sebagai {$certificateType} dalam event:</p>
            <h3>{$event['title']}</h3>
            <p>Pada tanggal: {$event['date']}</p>
        </div>
    ";

    $mpdf->WriteHTML($certificateContent);
    $mpdf->Output("Sertifikat_{$certificateType}_{$name}.pdf", \Mpdf\Output\Destination::INLINE);
} catch (\Exception $e) {
    die("Gagal membuat sertifikat: " . $e->getMessage());
}

<?php
require_once '../includes/config.php';
require_once '../vendor/autoload.php';

use Mpdf\Mpdf;

if (!isset($_GET['event_id'])) {
    die("Event ID tidak ditemukan. Pilih event terlebih dahulu.");
}

$event_id = $_GET['event_id'];

// Ambil data event dari database
$stmt = $pdo->prepare("SELECT title, pemateri, date FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    die("Event tidak ditemukan.");
}

// Validasi data tanggal
if (empty($event['date'])) {
    die("Tanggal tidak tersedia untuk acara ini. Pastikan data tanggal sudah diisi di basis data.");
}

$title = $event['title'];
$pemateri = $event['pemateri'];
$date = date("d F Y", strtotime($event['date'])); // Format tanggal menjadi lebih rapi

// Buat PDF Sertifikat Pemateri
try {
    $mpdf = new Mpdf();
    $html = "
        <html>
        <head>
            <style>
                body { text-align: center; font-family: Arial, sans-serif; }
                .certificate { border: 10px solid #ddd; padding: 30px; }
                h1 { font-size: 50px; margin: 0; }
                p { font-size: 20px; }
                .footer { margin-top: 50px; font-size: 15px; }
            </style>
        </head>
        <body>
            <div class='certificate'>
                <h1>Sertifikat</h1>
                <p>Dengan ini diberikan kepada:</p>
                <h2>$pemateri</h2>
                <p>Atas partisipasinya sebagai pemateri dalam acara:</p>
                <h3>$title</h3>
                <p>Yang diselenggarakan pada tanggal:</p>
                <h3>$date</h3>
                <div class='footer'>Universitas Buana Perjuangan Karawang</div>
            </div>
        </body>
        </html>
    ";

// Tambahkan HTML ke dalam PDF
$mpdf->WriteHTML($html);

// Outputkan PDF ke browser
$mpdf->Output('sertifikat_pemateri_' . $event['pemateri'] . '.pdf', 'I');
} catch (Exception $e) {
    die("Terjadi kesalahan saat membuat sertifikat: " . $e->getMessage());
}
?>

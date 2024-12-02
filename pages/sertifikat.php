<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php'; // Navbar dan sidebar
require_once '../includes/config.php';
require_once '../vendor/autoload.php'; // Autoload untuk PHPMailer dan mPDF

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Mpdf\Mpdf;

// Ambil semua event dari database
$stmt = $pdo->query("SELECT id, title FROM events ORDER BY date DESC");
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cek apakah ada event yang dipilih
$selected_event_id = $_GET['event_id'] ?? null;
$selected_event = null;
$participants = [];
$presenter = null;

if ($selected_event_id) {
    // Ambil data event berdasarkan ID
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$selected_event_id]);
    $selected_event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$selected_event) {
        die("Event tidak ditemukan.");
    }

    // Ambil data pemateri
    $presenter = $selected_event['pemateri'];

    // Ambil peserta yang hadir dari tabel registrations
    $stmt = $pdo->prepare("SELECT name, email FROM registrations WHERE event_id = ? AND attendance_status = 'Hadir'");
    $stmt->execute([$selected_event_id]);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi untuk membuat sertifikat PDF
function generateCertificate($name, $event, $isPresenter = false) {
    $mpdf = new \Mpdf\Mpdf();
    $type = $isPresenter ? "Pemateri" : "Peserta";
    $certificateContent = "
        <div style='text-align: center; font-family: Arial, sans-serif;'>
            <h1>Sertifikat {$type}</h1>
            <p>Dengan ini diberikan kepada:</p>
            <h2>{$name}</h2>
            <p>Atas partisipasinya sebagai {$type} dalam event:</p>
            <h3>{$event['title']}</h3>
            <p>Pada tanggal: {$event['date']}</p>
        </div>
    ";
    $mpdf->WriteHTML($certificateContent);
    return $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
}

// Fungsi untuk mengirim sertifikat ke peserta via email
function sendCertificate($participant, $event) {
    $pdfContent = generateCertificate($participant['name'], $event);
    $pdfFilePath = '../temp/certificate_' . $participant['email'] . '.pdf';
    file_put_contents($pdfFilePath, $pdfContent);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // Ganti dengan host SMTP Anda
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@example.com'; // Ganti dengan email Anda
        $mail->Password = 'your_password'; // Ganti dengan password email Anda
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('your_email@example.com', 'Event Organizer');
        $mail->addAddress($participant['email'], $participant['name']);
        $mail->addAttachment($pdfFilePath);

        $mail->isHTML(true);
        $mail->Subject = 'Sertifikat Kehadiran Event';
        $mail->Body = "Halo {$participant['name']},<br><br>
                       Terima kasih telah menghadiri event <strong>{$event['title']}</strong>. 
                       Berikut adalah sertifikat kehadiran Anda sebagai lampiran.<br><br>
                       Salam,<br>Event Organizer";

        $mail->send();
    } catch (Exception $e) {
        return "Gagal mengirim sertifikat ke {$participant['email']}: {$mail->ErrorInfo}";
    } finally {
        unlink($pdfFilePath); // Hapus file sementara
    }

    return "Sertifikat berhasil dikirim ke {$participant['email']}";
}

// Kirim semua sertifikat peserta jika tombol "Kirim Semua" ditekan
if ($selected_event_id && isset($_GET['send_all'])) {
    foreach ($participants as $participant) {
        $result = sendCertificate($participant, $selected_event);
        echo "<p>{$result}</p>";
    }
}
?>

<div class="container mt-4">
    <h2 class="text-center">Sertifikat Event</h2>
    <form method="GET" class="mb-4">
        <div class="input-group">
            <select name="event_id" class="form-select" required>
                <option value="" disabled selected>Pilih Event</option>
                <?php foreach ($events as $event): ?>
                    <option value="<?= $event['id'] ?>" <?= $selected_event_id == $event['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($event['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-primary">Pilih</button>
        </div>
    </form>

    <?php if ($selected_event): ?>
        <h4>
            Event: <?= htmlspecialchars($selected_event['title']) ?>
            <a href="sertifikat.php?event_id=<?= $selected_event['id'] ?>&send_all=true" class="btn btn-warning btn-sm float-end">
                Kirim Semua Sertifikat Peserta
            </a>
        </h4>
        <hr>

        <!-- Sertifikat Pemateri -->
        <h5 class="mt-4">Sertifikat Pemateri</h5>
        <?php if ($presenter): ?>
            <p>Nama Pemateri: <strong><?= htmlspecialchars($presenter) ?></strong></p>
            <a href="generate_presenter_certificate.php?event_id=<?= $selected_event['id'] ?>" 
               class="btn btn-primary btn-sm">
               Cetak Sertifikat Pemateri
            </a>
        <?php else: ?>
            <p class="text-muted">Tidak ada pemateri untuk event ini.</p>
        <?php endif; ?>

        <!-- Sertifikat Peserta -->
        <h5 class="mt-4">Sertifikat Peserta</h5>
        <?php if (!empty($participants)): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nama Peserta</th>
                        <th>Email</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($participants as $participant): ?>
                        <tr>
                            <td><?= htmlspecialchars($participant['name']) ?></td>
                            <td><?= htmlspecialchars($participant['email']) ?></td>
                            <td>
                                <a href="generate_certificate.php?event_id=<?= $selected_event['id'] ?>&type=peserta&name=<?= urlencode($participant['name']) ?>&email=<?= urlencode($participant['email']) ?>" 
                                    class="btn btn-primary btn-sm">
                                    Cetak Sertifikat Peserta
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">Tidak ada peserta dengan status "Hadir".</p>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-muted">Pilih event untuk melihat sertifikat.</p>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

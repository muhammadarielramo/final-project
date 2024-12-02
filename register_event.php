<?php
session_start();
include 'includes/config.php';
require __DIR__ . '/vendor/autoload.php';

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\Writer\PngWriter;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Midtrans Configuration
require_once 'vendor/midtrans/midtrans-php/Midtrans.php';
\Midtrans\Config::$serverKey = 'SB-Mid-server-LMk3IOcT-CmcBaVt1kVIT93T'; // Ganti dengan server key Midtrans Anda
\Midtrans\Config::$isProduction = false;
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $event_id = $_POST['event_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Ambil detail event untuk menentukan biaya
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch();

    if (!$event) {
        echo "<script>alert('Event tidak ditemukan!'); window.location.href='index.php';</script>";
        exit;
    }

    $amount = $event['price'];

    // Jika harga event adalah 0, langsung proses pendaftaran dan kirim tiket
    if ($amount == 0) {
        // Simpan ke Database
        $stmt = $pdo->prepare("INSERT INTO registrations (event_id, name, email, phone) VALUES (?, ?, ?, ?)");
        $stmt->execute([$event_id, $name, $email, $phone]);
        $registrationId = $pdo->lastInsertId();

        // Generate QR Code
        $qrContent = "$registrationId:$event_id";
        $qrBuilder = new Builder(
            writer: new PngWriter(),
            data: ($qrContent),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            labelText: 'Scan QR di Pintu Masuk',
            labelAlignment: LabelAlignment::Center
        ); 
            
        $result = $qrBuilder->build();

        $qrPath = "assets/uploads/qr_$registrationId.png";
        $result->saveToFile($qrPath);

        // Kirim Email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'bproticdummy@gmail.com';
            $mail->Password = 'hefk xvuq srzg tqsg';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom('bproticdummy@gmail.com', 'BPROTIC');
            $mail->addAddress($email, $name);
            $mail->Subject = 'Tiket Event Anda';
            $mail->Body = "Terima kasih sudah mendaftar untuk event kami! Silakan temukan QR code Anda terlampir sebagai tiket masuk.";
            $mail->addAttachment($qrPath);

            $mail->send();
            echo '<div class="text-center mt-5">';
            echo '<h3>Registrasi berhasil! Tiket telah dikirim ke email Anda.</h3>';
            echo '<a href="index.php" class="btn btn-primary mt-3">Kembali ke Halaman Utama</a>';
            echo '</div>';
        } catch (Exception $e) {
            die("Error mengirim email: {$mail->ErrorInfo}");
        }

        exit;
    }

    // Jika harga event lebih besar dari 0, proses pembayaran dengan Midtrans
    $transactionDetails = [
        'order_id' => 'ORDER-' . uniqid(),
        'gross_amount' => $amount,
    ];

    // Item Details
    $itemDetails = [
        [
            'id' => 'TICKET-' . $event_id,
            'price' => $amount,
            'quantity' => 1,
            'name' => $event['title'],
        ],
    ];

    // Customer Details
    $customerDetails = [
        'first_name' => $name,
        'email' => $email,
        'phone' => $phone,
    ];

    // Transaction Parameters
    $transactionParams = [
        'transaction_details' => $transactionDetails,
        'item_details' => $itemDetails,
        'customer_details' => $customerDetails,
    ];

    try {
        // Get Midtrans Snap Token
        $snapToken = \Midtrans\Snap::getSnapToken($transactionParams);
        $_SESSION['registration_data'] = [
            'event_id' => $event_id,
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'snap_token' => $snapToken,
        ];
    } catch (Exception $e) {
        die('Error: ' . $e->getMessage());
    }
}

// Handle Payment Success
if (isset($_GET['payment_status']) && $_GET['payment_status'] === 'success') {
    $registrationData = $_SESSION['registration_data'];
    if (!$registrationData) {
        die('Data registrasi tidak ditemukan.');
    }

    // Simpan ke Database
    $stmt = $pdo->prepare("INSERT INTO registrations (event_id, name, email, phone) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $registrationData['event_id'],
        $registrationData['name'],
        $registrationData['email'],
        $registrationData['phone'],
    ]);
    $registrationId = $pdo->lastInsertId();

    // Generate QR Code
    $qrContent = "$registrationId:{$registrationData['event_id']}";
    $qrBuilder = new Builder(
        writer: new PngWriter(),
        data: ($qrContent),
        encoding: new Encoding('UTF-8'),
        errorCorrectionLevel: ErrorCorrectionLevel::High,
        labelText: 'Scan QR di Pintu Masuk',
        labelAlignment: LabelAlignment::Center
    ); 
        
    $result = $qrBuilder->build();

    $qrPath = "assets/uploads/qr_$registrationId.png";
    $result->saveToFile($qrPath);

    // Kirim Email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'bproticdummy@gmail.com';
        $mail->Password = 'hefk xvuq srzg tqsg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('bproticdummy@gmail.com', 'BPROTIC');
        $mail->addAddress($registrationData['email'], $registrationData['name']);
        $mail->Subject = 'Tiket Event Anda';
        $mail->Body = "Terima kasih sudah mendaftar untuk event kami! Silakan temukan QR code Anda terlampir sebagai tiket masuk.";
        $mail->addAttachment($qrPath);

        $mail->send();
        unset($_SESSION['registration_data']);
        echo '<div class="text-center mt-5">';
        echo '<h3>Registrasi berhasil! Tiket telah dikirim ke email Anda.</h3>';
        echo '<a href="index.php" class="btn btn-primary mt-3">Kembali ke Halaman Utama</a>';
        echo '</div>';
        exit;
    } catch (Exception $e) {
        die("Error mengirim email: {$mail->ErrorInfo}");
    }
}

$event_id = $_GET['event_id'];
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$event_id]);
$event = $stmt->fetch();

if (!$event) {
    echo "Event tidak ditemukan!";
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi untuk <?= htmlspecialchars($event['title']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="SB-Mid-client-oPc2Fv1z8uUBIT4d"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .navbar {
            background-color: #191970 !important;
        }
        .btn-primary {
            background-color: #a51b20;
            border-color: #a51b20;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="assets/images/logo-bprotic.png" alt="Logo" width="40" height="40" class="me-2">
                BPROTIC
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#events">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#footer">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Registration Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-4" style="color: #191970;">Registrasi untuk <?= htmlspecialchars($event['title']) ?></h2>
            <form method="POST" class="w-50 mx-auto">
                <input type="hidden" name="event_id" value="<?= $event_id ?>">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Telepon</label>
                    <input type="text" class="form-control" id="phone" name="phone">
                </div>
                <button type="submit" class="btn btn-primary w-100">Daftar</button>
            </form>
        </div>
    </section>
    
    <!-- About and Social Media Section -->
    <section>
        <div class="container-fluid py-5" style="background-color: #2C2C7C;">
            <div class="d-flex justify-content-between align-items-center mx-5">
                <!-- About Section -->
                <div class="about-section" style="flex: 1; margin-right: 50px;">
                    <h4 class="mb-3" style="color: #ffffff;">About</h4>
                    <p style="line-height: 1.8; color: #FFFFFF;">
                        Event Center adalah platform penyelenggaraan acara yang menyediakan berbagai informasi terkini
                        mengenai event-event menarik yang dapat diikuti. Kami berdedikasi untuk memberikan pengalaman
                        terbaik dalam menemukan dan mendaftar acara.
                    </p>
                </div>

                <!-- Social Media Section -->
                <div class="social-media-section text-center" style="flex: 1;">
                    <h4 class="mb-3" style="color: #FFFFFF;">Follow Us on Social Media</h4>
                    <div class="d-flex justify-content-center mt-3">
                        <a href="https://www.facebook.com" target="_blank" rel="noopener noreferrer" class="text-decoration-none mx-3" style="color: #FFF000; font-size: 1.5rem;">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="https://www.x.com" target="_blank" rel="noopener noreferrer" class="text-decoration-none mx-3" style="color: #FFF000; font-size: 1.5rem;">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="https://www.instagram.com" target="_blank" rel="noopener noreferrer" class="text-decoration-none mx-3" style="color: #FFF000; font-size: 1.5rem;">
                            <i class="bi bi-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        <?php if (isset($snapToken)): ?>
        const snapToken = <?= json_encode($snapToken) ?>;
        snap.pay(snapToken, {
            onSuccess: function() {
                window.location.href = "?payment_status=success";
            },
            onPending: function() {
                alert("Pembayaran tertunda!");
            },
            onError: function() {
                alert("Pembayaran gagal!");
            }
        });
        <?php endif; ?>
    </script>

    <!-- Footer -->
    <footer class="text-center py-4" style="background-color: #191970; color: white;">
        <p class="mb-0">&copy; 2024 Event Center. All Rights Reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

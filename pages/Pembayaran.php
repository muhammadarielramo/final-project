<?php
require_once '../vendor/midtrans/midtrans-php/Midtrans.php';
include '../includes/config.php';

class Payment {
    public function __construct() {
        \Midtrans\Config::$serverKey = 'SB-Mid-server-LMk3IOcT-CmcBaVt1kVIT93T'; // Ganti dengan server key Midtrans Anda
        \Midtrans\Config::$isProduction = false;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    public function processPayment($event_id, $amount, $name, $email, $phone, $attendee_id) {
        $detailTransaksi = [
            'order_id' => 'ORDER-' . uniqid(),
            'gross_amount' => $amount,
        ];

        $detailTicket = [
            [
                'id' => 'TICKET-' . $event_id,
                'price' => $amount,
                'quantity' => 1,
                'name' => $event_id,
            ],
        ];

        $detailCustomer = [
            'first_name' => $name,
            'email' => $email,
            'phone' => $phone,
        ];

        $transaksiParams = [
            'transaction_details' => $detailTransaksi,
            'item_details' => $detailTicket,
            'customer_details' => $detailCustomer,
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($transaksiParams);

            $_SESSION['registration_data'] = [
                'event_id' => $event_id,
                'attendee_id' => $attendee_id,
                'snap_token' => $snapToken,
                'price' => $amount,
            ];

            return $snapToken;
        }  catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }

    }

    public function handlePaymentSuccess() {
        if (isset($_GET['payment_status']) && $_GET['payment_status'] === 'success') {
            if (!isset($_SESSION['registration_data'])) {
                die('Data registrasi tidak ditemukan.');
            }

            $registrationData = $_SESSION['registration_data'];

            $this->saveRegistration($registrationData);
            echo 'Pembayaran berhasil, terima kasih ' . $registrationData['name'];
        }

    }

    // simpan ke db
    private function saveRegistration($registrationData) {
        $query = "INSERT INTO event_ticket_assignment (attendee_ID, event_ID, price, snap_token) VALUES (:attendee_id, :event_id, :price, :snap_token)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':attendee_id', $registrationData['attendee_id'], PDO::PARAM_INT);
        $stmt->bindParam(':event_id', $registrationData['event_id'], PDO::PARAM_INT);
        $stmt->bindParam(':price', $registrationData['price'], PDO::PARAM_INT);
        $stmt->bindParam(':snap_token', $registrationData['snap_token'], PDO::PARAM_INT);
        $stmt->execute();
    }
}

// Membuat objek Payment
$payment = new Payment();

// Proses pembayaran dan dapatkan Snap Token
$snapToken = $payment->processPayment(1, 20000, "Keysya", "keysya@email.com", "00000", 20);

// Lanjutkan dengan tampilan untuk menampilkan Snap Token
echo "Snap Token: " . $snapToken;

// Tangani pembayaran yang sukses
$payment->handlePaymentSuccess();



?>
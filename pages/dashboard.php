<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/config.php';
?>

<div class="container-fluid">
    <h2 class="text-center mb-4">Dashboard Admin</h2>
    <div class="row justify-content-center">
        <!-- Events Menu -->
        <div class="col-md-4">
            <div class="card text-center mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Events</h5>
                    <p class="card-text">Lihat dan kelola daftar event.</p>
                    <a href="events.php" class="btn btn-primary">Go to Events</a>
                </div>
            </div>
        </div>
        
        <!-- Peserta Menu -->
        <div class="col-md-4">
            <div class="card text-center mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Peserta</h5>
                    <p class="card-text">Lihat dan kelola daftar peserta.</p>
                    <a href="peserta.php" class="btn btn-primary">Go to Peserta</a>
                </div>
            </div>
        </div>

        <!-- Sertifikat Menu -->
        <div class="col-md-4">
            <div class="card text-center mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Sertifikat</h5>
                    <p class="card-text">Lihat dan kelola sertifikat.</p>
                    <a href="sertifikat.php" class="btn btn-primary">Go to Sertifikat</a>
                </div>
            </div>
        </div>
   
        <!-- Scan QR Menu -->
        <div class="col-md-4">
            <div class="card text-center mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Scan QR</h5>
                    <p class="card-text">Scan tiket peserta untuk kehadiran.</p>
                    <a href="scan_qr.php" class="btn btn-primary">Go to Scan QR</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; // Penutup halaman ?>

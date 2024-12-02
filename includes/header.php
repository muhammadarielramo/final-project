<!-- header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar {
            position: sticky;
            top: 0;
            z-index: 1;
        }
        .sidebar {
            position: sticky;
            top: 0;
            min-height: 100vh;
            background-color: #f8f9fa;
            padding-top: 20px;
            z-index: 0;
        }
        .sidebar a {
            font-size: 16px;
            color: #333;
            padding: 10px 20px;
            display: block;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #e2e6ea;
            color: #007bff;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <!-- Logo Website -->
            <a class="navbar-brand d-flex align-items-center" href="../index.php">
                <img src="../assets/images/logo-bprotic.png" alt="Logo" width="40" height="40" class="me-2">
                <span>BPROTIC</span>
            </a>
            <!-- Toggle button untuk mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <!-- Menu Navbar -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <h5 class="text-center">Menu</h5>
                <a href="../pages/dashboard.php">Dashboard</a>
                <a href="../pages/events.php">Events</a>
                <a href="../pages/peserta.php">Peserta</a>
                <a href="../pages/sertifikat.php">Sertifikat</a>
                <a href="../pages/scan_qr.php">Scan QR</a>
            </div>

            <!-- Konten Utama -->
            <div class="col-md-9 col-lg-10 pt-4">
                <!-- Konten halaman akan dimasukkan di sini -->

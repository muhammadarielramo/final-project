<?php
include '../includes/config.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
$stmt->execute([$id]);
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
    <title>Detail Event - <?= htmlspecialchars($event['title']) ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
                <img src="../assets/images/logo-bprotic.png" alt="Logo" width="40" height="40" class="me-2">
                BPROTIC
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php#events">Events</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php#footer">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <header class="text-white text-center py-5" style="background-color: white;">
        <div class="container">
            <h1 class="text-center mt-4" style="color: #191970;"><?= htmlspecialchars($event['title']) ?></h1>
        </div>
    </header>

    <!-- Event Detail Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <?php if (!empty($event['image'])): ?>
                        <img src="../assets/images/<?= $event['image'] ?>" 
                             alt="<?= htmlspecialchars($event['title']) ?>" 
                             class="img-fluid rounded" 
                             style="width: 100%; max-height: 400px; object-fit: cover;">
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <h2><?= htmlspecialchars($event['title']) ?></h2>
                    <p><strong>Tanggal:</strong> <?= htmlspecialchars($event['date']) ?></p>
                    <p><strong>Deskripsi:</strong></p>
                    <p><?= htmlspecialchars($event['description']) ?></p>
                    <a href="../register_event.php?event_id=<?= $event['id'] ?>" class="btn btn-primary mt-3">Daftar Sekarang</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center py-4" style="background-color: #191970; color: white;">
        <p class="mb-0">&copy; 2024 Event Center. All Rights Reserved.</p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

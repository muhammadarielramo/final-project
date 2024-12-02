<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/config.php';

$error_message = '';
$message = '';

// Tambah atau Edit Event
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $event_id = $_POST['event_id'] ?? null;

    // Ambil data gambar lama jika tidak ada gambar baru diunggah
    $current_image = null;
    if ($event_id) {
        $stmt = $pdo->prepare("SELECT image FROM events WHERE id = ?");
        $stmt->execute([$event_id]);
        $current_image = $stmt->fetchColumn();
    }

    // Validasi file upload
    $image = $current_image;
    if (!empty($_FILES['image']['name'])) {
        $valid_extension = ['jpg', 'png', 'jpeg'];  
        $file_name = $_FILES['image']['name'];
        $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $file_size = $_FILES['image']['size'];
        $max_file_size = 2 * 1024 * 1024;

        if (!in_array($extension, $valid_extension)) {
            $error_message = "Format gambar tidak valid. Gunakan jpg, png, atau jpeg";
        } elseif ($file_size > $max_file_size) {
            $error_message = "Ukuran gambar terlalu besar. Maksimum 2MB";         
        } else {
            $new_file_name = uniqid() . '.' . $extension;
            $image = '../assets/images/' . basename($new_file_name);
            move_uploaded_file($_FILES['image']['tmp_name'], $image);
            $image = $new_file_name; // Nama file yang akan disimpan di database
        }
    }

    // Proses penyimpanan hanya jika tidak ada error
    if (empty($error_message)) {
        if ($event_id) {
            // Update event
            $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, date = ?, location = ?, image = ?, price = ? WHERE id = ?");
            $stmt->execute([$title, $description, $date, $location, $image, $price, $event_id]);
            $message = "Event berhasil diperbarui.";
        } else {
            // Tambah event baru
            $stmt = $pdo->prepare("INSERT INTO events (title, description, date, location, image, price) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $date, $location, $image, $price]);
            $message = "Event berhasil ditambahkan.";
        }
    }
}

// Hapus Event
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$delete_id]);
    $message = "Event berhasil dihapus.";
}

// Pencarian Event
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM events WHERE title LIKE ?";
$stmt = $pdo->prepare($query);
$stmt->execute(['%' . $search . '%']);
$events = $stmt->fetchAll();
?>

<div class="container-fluid">
    <h2 class="text-center mb-4">Manajemen Events</h2>

    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php elseif (!empty($message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Form Pencarian dan Tombol Tambah -->
    <div class="d-flex justify-content-between mb-3">
        <form method="GET" class="d-flex" style="flex-grow: 1;">
            <input type="text" name="search" class="form-control me-2" placeholder="Cari event..." value="<?= htmlspecialchars($search) ?>">
        </form>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">Tambah Event</button>
    </div>

    <!-- Tabel Daftar Events -->
    <table class="table table-striped">
        <thead>
            <tr>    
                <th>ID</th>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Tanggal</th>
                <th>Lokasi</th>
                <th>Gambar</th>
                <th>Price</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($events as $event): ?>
            <tr>
                <td><?= $event['id'] ?></td>
                <td>
                    <a href="event_detail.php?id=<?= $event['id'] ?>" 
                        style="color: inherit; text-decoration: none;">
                        <?= htmlspecialchars($event['title']) ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($event['description']) ?></td>
                <td><?= $event['date'] ?></td>
                <td><?= htmlspecialchars($event['location']) ?></td>
                <td>
                    <?php if (!empty($event['image'])): ?>
                        <img src="../assets/images/<?= $event['image'] ?>" alt="Gambar Event" style="max-width: 100px;">
                    <?php else: ?>
                        Tidak Ada Gambar
                    <?php endif; ?>
                </td>
                <td>Rp<?= $event['price'] ?></td>
                <td>
                    <button class="btn btn-sm btn-warning" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editEventModal" 
                            data-id="<?= $event['id'] ?>" 
                            data-title="<?= htmlspecialchars($event['title']) ?>" 
                            data-description="<?= htmlspecialchars($event['description']) ?>"
                            data-date="<?= $event['date'] ?>"
                            data-location="<?= htmlspecialchars($event['location']) ?>"
                            data-image="<?= htmlspecialchars($event['image']) ?>"
                            data-price="<?= $event['price'] ?>">
                        Edit
                    </button>
                    <a href="events.php?delete_id=<?= $event['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus event ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Tambah Event -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="addEventModalLabel">Tambah Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Judul Event</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="location" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gambar Event</label>
                    <input type="file" name="image" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" name="price" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Event -->
<div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" enctype="multipart/form-data">
            <div class="modal-header">
                <h5 class="modal-title" id="editEventModalLabel">Edit Event</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="event_id" id="edit-event-id">
                <div class="mb-3">
                    <label class="form-label">Judul Event</label>
                    <input type="text" name="title" id="edit-event-title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="description" id="edit-event-description" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="date" id="edit-event-date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="location" id="edit-event-location" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gambar Event (kosongkan jika tidak ingin mengganti)</label>
                    <input type="file" name="image" id="edit-event-image" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" name="price" id="edit-event-price" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Isi form edit dengan data event
    const editEventModal = document.getElementById('editEventModal');
    editEventModal.addEventListener('show.bs.modal', event => {
        const button = event.relatedTarget;
        const id = button.getAttribute('data-id');
        const title = button.getAttribute('data-title');
        const description = button.getAttribute('data-description');
        const date = button.getAttribute('data-date');
        const location = button.getAttribute('data-location');
        const image = button.getAttribute('data-image');
        const price = button.getAttribute('data-price');

        document.getElementById('edit-event-id').value = id;
        document.getElementById('edit-event-title').value = title;
        document.getElementById('edit-event-description').value = description;
        document.getElementById('edit-event-date').value = date;
        document.getElementById('edit-event-location').value = location;
        document.getElementById('edit-event-price').value = price;
    });
</script>

<?php include '../includes/footer.php'; ?>
<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}

include '../includes/header.php';
include '../includes/config.php';

// Edit Peserta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['participant_id'])) {
    $participant_id = $_POST['participant_id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Update data peserta
    $stmt = $pdo->prepare("UPDATE registrations SET name = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->execute([$name, $email, $phone, $participant_id]);
    $message = "Data peserta berhasil diperbarui.";
}

// Hapus Peserta
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE id = ?");
    $stmt->execute([$delete_id]);
    $message = "Peserta berhasil dihapus.";
}

// Pencarian Peserta
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM registrations WHERE name LIKE ?";
$stmt = $pdo->prepare($query);
$stmt->execute(['%' . $search . '%']);
$participants = $stmt->fetchAll();
?>

<div class="container-fluid">
    <h2 class="text-center mb-4">Manajemen Peserta</h2>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <!-- Form Pencarian -->
    <div class="d-flex justify-content-between mb-3">
        <form method="GET" class="d-flex" style="flex-grow: 1;">
            <input type="text" name="search" class="form-control me-2" placeholder="Cari peserta..." value="<?= htmlspecialchars($search) ?>">
        </form>
    </div>

    <!-- Tabel Daftar Peserta -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Telepon</th>
                <th>Event ID</th>
                <th>Status Kehadiran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($participants as $participant): ?>
            <tr>
                <td><?= $participant['id'] ?></td>
                <td><?= htmlspecialchars($participant['name']) ?></td>
                <td><?= htmlspecialchars($participant['email']) ?></td>
                <td><?= htmlspecialchars($participant['phone']) ?></td>
                <td><?= htmlspecialchars($participant['event_id']) ?></td>
                <td><?= htmlspecialchars($participant['attendance_status']) ?></td>
                <td>
                    <button 
                        class="btn btn-sm btn-warning editButton" 
                        data-bs-toggle="modal" 
                        data-bs-target="#editPesertaModal" 
                        data-id="<?= $participant['id'] ?>" 
                        data-name="<?= htmlspecialchars($participant['name']) ?>" 
                        data-email="<?= htmlspecialchars($participant['email']) ?>" 
                        data-phone="<?= htmlspecialchars($participant['phone']) ?>">
                        Edit
                    </button>
                    <a href="peserta.php?delete_id=<?= $participant['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus peserta ini?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Edit Peserta -->
<div class="modal fade" id="editPesertaModal" tabindex="-1" aria-labelledby="editPesertaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" method="POST" id="editPesertaForm">
            <div class="modal-header">
                <h5 class="modal-title" id="editPesertaModalLabel">Edit Peserta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="participant_id" id="edit-participant-id">
                <div class="mb-3">
                    <label class="form-label">Nama Peserta</label>
                    <input type="text" name="name" class="form-control" id="edit-participant-name" required pattern="[A-Za-z\s]+" title="Nama hanya boleh mengandung huruf dan spasi">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" id="edit-participant-email" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Telepon</label>
                    <input type="text" name="phone" class="form-control" id="edit-participant-phone" required pattern="[0-9]+" title="Nomor telepon hanya boleh mengandung angka">
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
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.editButton');

    editButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Ambil data dari tombol
            const id = this.dataset.id;
            const name = this.dataset.name;
            const email = this.dataset.email;
            const phone = this.dataset.phone;

            // Isi data ke dalam form modal
            document.getElementById('edit-participant-id').value = id;
            document.getElementById('edit-participant-name').value = name;
            document.getElementById('edit-participant-email').value = email;
            document.getElementById('edit-participant-phone').value = phone;
        });
    });

    // Validasi Input Nama (hanya huruf dan spasi)
    const nameInput = document.getElementById('edit-participant-name');
    nameInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^A-Za-z\s]/g, '');
    });

    // Validasi Input Telepon (hanya angka)
    const phoneInput = document.getElementById('edit-participant-phone');
    phoneInput.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });    
});
</script>

<?php include '../includes/footer.php'; ?>

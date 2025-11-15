<?php
require_once '../config/database.php';
require_once '../config/session.php';
checkAuth('admin');

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah_guru'])) {
        $nip = $_POST['nip'];
        $nama_lengkap = $_POST['nama_lengkap'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $mata_pelajaran = $_POST['mata_pelajaran'];
        $no_telp = $_POST['no_telp'];
        
        $username = 'guru_' . $nip;
        $password = password_hash('password123', PASSWORD_DEFAULT);
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nama_lengkap) VALUES (?, ?, 'guru', ?)");
            $stmt->execute([$username, $password, $nama_lengkap]);
            $user_id = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("INSERT INTO guru (nip, nama_lengkap, jenis_kelamin, mata_pelajaran, no_telp, user_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nip, $nama_lengkap, $jenis_kelamin, $mata_pelajaran, $no_telp, $user_id]);
            
            $pdo->commit();
            $success = "Data guru berhasil ditambahkan!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Gagal menambah data guru: " . $e->getMessage();
        }
    } elseif (isset($_POST['edit_guru'])) {
        $id = $_POST['id'];
        $nip = $_POST['nip'];
        $nama_lengkap = $_POST['nama_lengkap'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $mata_pelajaran = $_POST['mata_pelajaran'];
        $no_telp = $_POST['no_telp'];
        
        $stmt = $pdo->prepare("UPDATE guru SET nip=?, nama_lengkap=?, jenis_kelamin=?, mata_pelajaran=?, no_telp=? WHERE id=?");
        $stmt->execute([$nip, $nama_lengkap, $jenis_kelamin, $mata_pelajaran, $no_telp, $id]);
        $success = "Data guru berhasil diupdate!";
    } elseif (isset($_POST['hapus_guru'])) {
        $id = $_POST['id'];
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM guru WHERE id = ?");
            $stmt->execute([$id]);
            $guru = $stmt->fetch();
            
            $stmt = $pdo->prepare("DELETE FROM guru WHERE id = ?");
            $stmt->execute([$id]);
            
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$guru['user_id']]);
            
            $pdo->commit();
            $success = "Data guru berhasil dihapus!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Gagal menghapus data guru: " . $e->getMessage();
        }
    }
}

// Ambil data guru
$stmt = $pdo->query("SELECT * FROM guru ORDER BY nama_lengkap");
$guru_list = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Data Guru</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahGuruModal">
                    <i class="fas fa-plus"></i> Tambah Guru
                </button>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="tableGuru">
                            <thead>
                                <tr>
                                    <th>NIP</th>
                                    <th>Nama Lengkap</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Mata Pelajaran</th>
                                    <th>No. Telepon</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($guru_list as $guru): ?>
                                <tr>
                                    <td><?php echo $guru['nip']; ?></td>
                                    <td><?php echo $guru['nama_lengkap']; ?></td>
                                    <td><?php echo $guru['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                    <td><?php echo $guru['mata_pelajaran']; ?></td>
                                    <td><?php echo $guru['no_telp']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editGuruModal" 
                                                data-id="<?php echo $guru['id']; ?>"
                                                data-nip="<?php echo $guru['nip']; ?>"
                                                data-nama="<?php echo $guru['nama_lengkap']; ?>"
                                                data-jk="<?php echo $guru['jenis_kelamin']; ?>"
                                                data-mapel="<?php echo $guru['mata_pelajaran']; ?>"
                                                data-telp="<?php echo $guru['no_telp']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#hapusGuruModal" 
                                                data-id="<?php echo $guru['id']; ?>"
                                                data-nama="<?php echo $guru['nama_lengkap']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Tambah Guru -->
<div class="modal fade" id="tambahGuruModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">NIP</label>
                        <input type="text" class="form-control" name="nip" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select class="form-select" name="jenis_kelamin" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mata Pelajaran</label>
                        <input type="text" class="form-control" name="mata_pelajaran" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" class="form-control" name="no_telp">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="tambah_guru">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Guru -->
<div class="modal fade" id="editGuruModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id_guru">
                    <div class="mb-3">
                        <label class="form-label">NIP</label>
                        <input type="text" class="form-control" name="nip" id="edit_nip" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" id="edit_nama_guru" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select class="form-select" name="jenis_kelamin" id="edit_jk_guru" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mata Pelajaran</label>
                        <input type="text" class="form-control" name="mata_pelajaran" id="edit_mapel" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telepon</label>
                        <input type="text" class="form-control" name="no_telp" id="edit_telp">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="edit_guru">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Guru -->
<div class="modal fade" id="hapusGuruModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Data Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="hapus_id_guru">
                    <p>Apakah Anda yakin ingin menghapus data guru <strong id="hapus_nama_guru"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" name="hapus_guru">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Script untuk modal edit guru
    var editGuruModal = document.getElementById('editGuruModal');
    editGuruModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('edit_id_guru').value = button.getAttribute('data-id');
        document.getElementById('edit_nip').value = button.getAttribute('data-nip');
        document.getElementById('edit_nama_guru').value = button.getAttribute('data-nama');
        document.getElementById('edit_jk_guru').value = button.getAttribute('data-jk');
        document.getElementById('edit_mapel').value = button.getAttribute('data-mapel');
        document.getElementById('edit_telp').value = button.getAttribute('data-telp');
    });

    // Script untuk modal hapus guru
    var hapusGuruModal = document.getElementById('hapusGuruModal');
    hapusGuruModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('hapus_id_guru').value = button.getAttribute('data-id');
        document.getElementById('hapus_nama_guru').textContent = button.getAttribute('data-nama');
    });
</script>

<?php include '../includes/footer.php'; ?>
<?php
require_once '../config/database.php';
require_once '../config/session.php';
checkAuth('admin');

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah_siswa'])) {
        $nis = $_POST['nis'];
        $nama_lengkap = $_POST['nama_lengkap'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $alamat = $_POST['alamat'];
        $kelas = $_POST['kelas'];
        
        // Buat user untuk siswa
        $username = 'siswa_' . $nis;
        $password = password_hash('password123', PASSWORD_DEFAULT);
        
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, nama_lengkap) VALUES (?, ?, 'siswa', ?)");
            $stmt->execute([$username, $password, $nama_lengkap]);
            $user_id = $pdo->lastInsertId();
            
            $stmt = $pdo->prepare("INSERT INTO siswa (nis, nama_lengkap, jenis_kelamin, tanggal_lahir, alamat, kelas, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nis, $nama_lengkap, $jenis_kelamin, $tanggal_lahir, $alamat, $kelas, $user_id]);
            
            $pdo->commit();
            $success = "Data siswa berhasil ditambahkan!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Gagal menambah data siswa: " . $e->getMessage();
        }
    } elseif (isset($_POST['edit_siswa'])) {
        $id = $_POST['id'];
        $nis = $_POST['nis'];
        $nama_lengkap = $_POST['nama_lengkap'];
        $jenis_kelamin = $_POST['jenis_kelamin'];
        $tanggal_lahir = $_POST['tanggal_lahir'];
        $alamat = $_POST['alamat'];
        $kelas = $_POST['kelas'];
        
        $stmt = $pdo->prepare("UPDATE siswa SET nis=?, nama_lengkap=?, jenis_kelamin=?, tanggal_lahir=?, alamat=?, kelas=? WHERE id=?");
        $stmt->execute([$nis, $nama_lengkap, $jenis_kelamin, $tanggal_lahir, $alamat, $kelas, $id]);
        $success = "Data siswa berhasil diupdate!";
    } elseif (isset($_POST['hapus_siswa'])) {
        $id = $_POST['id'];
        
        $pdo->beginTransaction();
        try {
            // Dapatkan user_id
            $stmt = $pdo->prepare("SELECT user_id FROM siswa WHERE id = ?");
            $stmt->execute([$id]);
            $siswa = $stmt->fetch();
            
            // Hapus dari tabel siswa
            $stmt = $pdo->prepare("DELETE FROM siswa WHERE id = ?");
            $stmt->execute([$id]);
            
            // Hapus dari tabel users
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$siswa['user_id']]);
            
            $pdo->commit();
            $success = "Data siswa berhasil dihapus!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Gagal menghapus data siswa: " . $e->getMessage();
        }
    }
}

// Ambil data siswa
$stmt = $pdo->query("SELECT * FROM siswa ORDER BY kelas, nama_lengkap");
$siswa_list = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Data Siswa</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahSiswaModal">
                    <i class="fas fa-plus"></i> Tambah Siswa
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
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>NIS</th>
                                    <th>Nama Lengkap</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Kelas</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($siswa_list as $siswa): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($siswa['nis']); ?></td>
                                    <td><?php echo htmlspecialchars($siswa['nama_lengkap']); ?></td>
                                    <td><?php echo $siswa['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                    <td><?php echo htmlspecialchars($siswa['kelas']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($siswa['tanggal_lahir'])); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editSiswaModal" 
                                                    data-id="<?php echo $siswa['id']; ?>"
                                                    data-nis="<?php echo $siswa['nis']; ?>"
                                                    data-nama="<?php echo $siswa['nama_lengkap']; ?>"
                                                    data-jk="<?php echo $siswa['jenis_kelamin']; ?>"
                                                    data-tgl="<?php echo $siswa['tanggal_lahir']; ?>"
                                                    data-alamat="<?php echo $siswa['alamat']; ?>"
                                                    data-kelas="<?php echo $siswa['kelas']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#hapusSiswaModal" 
                                                    data-id="<?php echo $siswa['id']; ?>"
                                                    data-nama="<?php echo $siswa['nama_lengkap']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
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

<!-- Modal Tambah Siswa -->
<div class="modal fade" id="tambahSiswaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">NIS</label>
                        <input type="text" class="form-control" name="nis" required>
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
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" name="tanggal_lahir" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" name="alamat" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="kelas" required>
                            <option value="VII A">VII A</option>
                            <option value="VII B">VII B</option>
                            <option value="VIII A">VIII A</option>
                            <option value="VIII B">VIII B</option>
                            <option value="IX A">IX A</option>
                            <option value="IX B">IX B</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="tambah_siswa">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Siswa -->
<div class="modal fade" id="editSiswaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">NIS</label>
                        <input type="text" class="form-control" name="nis" id="edit_nis" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control" name="nama_lengkap" id="edit_nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select class="form-select" name="jenis_kelamin" id="edit_jk" required>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" class="form-control" name="tanggal_lahir" id="edit_tgl" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea class="form-control" name="alamat" id="edit_alamat" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="kelas" id="edit_kelas" required>
                            <option value="VII A">VII A</option>
                            <option value="VII B">VII B</option>
                            <option value="VIII A">VIII A</option>
                            <option value="VIII B">VIII B</option>
                            <option value="IX A">IX A</option>
                            <option value="IX B">IX B</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="edit_siswa">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Siswa -->
<div class="modal fade" id="hapusSiswaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Data Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="hapus_id">
                    <p>Apakah Anda yakin ingin menghapus data siswa <strong id="hapus_nama"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" name="hapus_siswa">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Script untuk modal edit
    var editSiswaModal = document.getElementById('editSiswaModal');
    editSiswaModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var nis = button.getAttribute('data-nis');
        var nama = button.getAttribute('data-nama');
        var jk = button.getAttribute('data-jk');
        var tgl = button.getAttribute('data-tgl');
        var alamat = button.getAttribute('data-alamat');
        var kelas = button.getAttribute('data-kelas');
        
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_nis').value = nis;
        document.getElementById('edit_nama').value = nama;
        document.getElementById('edit_jk').value = jk;
        document.getElementById('edit_tgl').value = tgl;
        document.getElementById('edit_alamat').value = alamat;
        document.getElementById('edit_kelas').value = kelas;
    });

    // Script untuk modal hapus
    var hapusSiswaModal = document.getElementById('hapusSiswaModal');
    hapusSiswaModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var id = button.getAttribute('data-id');
        var nama = button.getAttribute('data-nama');
        
        document.getElementById('hapus_id').value = id;
        document.getElementById('hapus_nama').textContent = nama;
    });
</script>

<?php include '../includes/footer.php'; ?>
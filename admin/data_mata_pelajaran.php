<?php
require_once '../config/database.php';
require_once '../config/session.php';
checkAuth('admin');

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah_mapel'])) {
        $kode_mapel = $_POST['kode_mapel'];
        $nama_mapel = $_POST['nama_mapel'];
        $guru_id = $_POST['guru_id'];
        $kelas = $_POST['kelas'];
        
        $stmt = $pdo->prepare("INSERT INTO mata_pelajaran (kode_mapel, nama_mapel, guru_id, kelas) VALUES (?, ?, ?, ?)");
        $stmt->execute([$kode_mapel, $nama_mapel, $guru_id, $kelas]);
        $success = "Data mata pelajaran berhasil ditambahkan!";
    } elseif (isset($_POST['edit_mapel'])) {
        $id = $_POST['id'];
        $kode_mapel = $_POST['kode_mapel'];
        $nama_mapel = $_POST['nama_mapel'];
        $guru_id = $_POST['guru_id'];
        $kelas = $_POST['kelas'];
        
        $stmt = $pdo->prepare("UPDATE mata_pelajaran SET kode_mapel=?, nama_mapel=?, guru_id=?, kelas=? WHERE id=?");
        $stmt->execute([$kode_mapel, $nama_mapel, $guru_id, $kelas, $id]);
        $success = "Data mata pelajaran berhasil diupdate!";
    } elseif (isset($_POST['hapus_mapel'])) {
        $id = $_POST['id'];
        
        $stmt = $pdo->prepare("DELETE FROM mata_pelajaran WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Data mata pelajaran berhasil dihapus!";
    }
}

// Ambil data mata pelajaran dengan join guru
$stmt = $pdo->query("
    SELECT mp.*, g.nama_lengkap as nama_guru 
    FROM mata_pelajaran mp 
    LEFT JOIN guru g ON mp.guru_id = g.id 
    ORDER BY mp.kelas, mp.nama_mapel
");
$mapel_list = $stmt->fetchAll();

// Ambil data guru untuk dropdown
$stmt = $pdo->query("SELECT id, nama_lengkap FROM guru ORDER BY nama_lengkap");
$guru_list = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Data Mata Pelajaran</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahMapelModal">
                    <i class="fas fa-plus"></i> Tambah Mata Pelajaran
                </button>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Kode</th>
                                    <th>Mata Pelajaran</th>
                                    <th>Guru Pengajar</th>
                                    <th>Kelas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mapel_list as $mapel): ?>
                                <tr>
                                    <td><?php echo $mapel['kode_mapel']; ?></td>
                                    <td><?php echo $mapel['nama_mapel']; ?></td>
                                    <td><?php echo $mapel['nama_guru'] ?? '-'; ?></td>
                                    <td><?php echo $mapel['kelas']; ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editMapelModal" 
                                                data-id="<?php echo $mapel['id']; ?>"
                                                data-kode="<?php echo $mapel['kode_mapel']; ?>"
                                                data-nama="<?php echo $mapel['nama_mapel']; ?>"
                                                data-guru="<?php echo $mapel['guru_id']; ?>"
                                                data-kelas="<?php echo $mapel['kelas']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#hapusMapelModal" 
                                                data-id="<?php echo $mapel['id']; ?>"
                                                data-nama="<?php echo $mapel['nama_mapel']; ?>">
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

<!-- Modal Tambah Mata Pelajaran -->
<div class="modal fade" id="tambahMapelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Mata Pelajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Mata Pelajaran</label>
                        <input type="text" class="form-control" name="kode_mapel" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Mata Pelajaran</label>
                        <input type="text" class="form-control" name="nama_mapel" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Guru Pengajar</label>
                        <select class="form-select" name="guru_id">
                            <option value="">-- Pilih Guru --</option>
                            <?php foreach ($guru_list as $guru): ?>
                                <option value="<?php echo $guru['id']; ?>"><?php echo $guru['nama_lengkap']; ?></option>
                            <?php endforeach; ?>
                        </select>
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
                    <button type="submit" class="btn btn-primary" name="tambah_mapel">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Mata Pelajaran -->
<div class="modal fade" id="editMapelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Mata Pelajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id_mapel">
                    <div class="mb-3">
                        <label class="form-label">Kode Mata Pelajaran</label>
                        <input type="text" class="form-control" name="kode_mapel" id="edit_kode" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Mata Pelajaran</label>
                        <input type="text" class="form-control" name="nama_mapel" id="edit_nama_mapel" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Guru Pengajar</label>
                        <select class="form-select" name="guru_id" id="edit_guru">
                            <option value="">-- Pilih Guru --</option>
                            <?php foreach ($guru_list as $guru): ?>
                                <option value="<?php echo $guru['id']; ?>"><?php echo $guru['nama_lengkap']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select class="form-select" name="kelas" id="edit_kelas_mapel" required>
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
                    <button type="submit" class="btn btn-primary" name="edit_mapel">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Mata Pelajaran -->
<div class="modal fade" id="hapusMapelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Mata Pelajaran</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="hapus_id_mapel">
                    <p>Apakah Anda yakin ingin menghapus mata pelajaran <strong id="hapus_nama_mapel"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" name="hapus_mapel">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Script untuk modal edit mata pelajaran
    var editMapelModal = document.getElementById('editMapelModal');
    editMapelModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('edit_id_mapel').value = button.getAttribute('data-id');
        document.getElementById('edit_kode').value = button.getAttribute('data-kode');
        document.getElementById('edit_nama_mapel').value = button.getAttribute('data-nama');
        document.getElementById('edit_guru').value = button.getAttribute('data-guru');
        document.getElementById('edit_kelas_mapel').value = button.getAttribute('data-kelas');
    });

    // Script untuk modal hapus mata pelajaran
    var hapusMapelModal = document.getElementById('hapusMapelModal');
    hapusMapelModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('hapus_id_mapel').value = button.getAttribute('data-id');
        document.getElementById('hapus_nama_mapel').textContent = button.getAttribute('data-nama');
    });
</script>

<?php include '../includes/footer.php'; ?>
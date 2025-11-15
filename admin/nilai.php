<?php
require_once '../config/database.php';
require_once '../config/session.php';
checkAuth('admin');

// Handle CRUD operations for grades
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['tambah_nilai'])) {
        $siswa_id = $_POST['siswa_id'];
        $mata_pelajaran_id = $_POST['mata_pelajaran_id'];
        $nilai_uh1 = $_POST['nilai_uh1'];
        $nilai_uh2 = $_POST['nilai_uh2'];
        $nilai_uts = $_POST['nilai_uts'];
        $nilai_uas = $_POST['nilai_uas'];
        $semester = $_POST['semester'];
        $tahun_ajaran = $_POST['tahun_ajaran'];
        
        // Calculate final grade
        $nilai_akhir = ($nilai_uh1 + $nilai_uh2 + $nilai_uts + $nilai_uas) / 4;
        
        $stmt = $pdo->prepare("INSERT INTO nilai (siswa_id, mata_pelajaran_id, nilai_uh1, nilai_uh2, nilai_uts, nilai_uas, nilai_akhir, semester, tahun_ajaran) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$siswa_id, $mata_pelajaran_id, $nilai_uh1, $nilai_uh2, $nilai_uts, $nilai_uas, $nilai_akhir, $semester, $tahun_ajaran]);
        $success = "Data nilai berhasil ditambahkan!";
    } elseif (isset($_POST['edit_nilai'])) {
        $id = $_POST['id'];
        $nilai_uh1 = $_POST['nilai_uh1'];
        $nilai_uh2 = $_POST['nilai_uh2'];
        $nilai_uts = $_POST['nilai_uts'];
        $nilai_uas = $_POST['nilai_uas'];
        $semester = $_POST['semester'];
        $tahun_ajaran = $_POST['tahun_ajaran'];
        
        $nilai_akhir = ($nilai_uh1 + $nilai_uh2 + $nilai_uts + $nilai_uas) / 4;
        
        $stmt = $pdo->prepare("UPDATE nilai SET nilai_uh1=?, nilai_uh2=?, nilai_uts=?, nilai_uas=?, nilai_akhir=?, semester=?, tahun_ajaran=? WHERE id=?");
        $stmt->execute([$nilai_uh1, $nilai_uh2, $nilai_uts, $nilai_uas, $nilai_akhir, $semester, $tahun_ajaran, $id]);
        $success = "Data nilai berhasil diupdate!";
    } elseif (isset($_POST['hapus_nilai'])) {
        $id = $_POST['id'];
        
        $stmt = $pdo->prepare("DELETE FROM nilai WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Data nilai berhasil dihapus!";
    }
}

// Filter values
$kelas = $_GET['kelas'] ?? 'VII A';
$semester = $_GET['semester'] ?? '1';
$tahun_ajaran = $_GET['tahun_ajaran'] ?? '2024/2025';

// Get students by class
$stmt = $pdo->prepare("SELECT * FROM siswa WHERE kelas = ? ORDER BY nama_lengkap");
$stmt->execute([$kelas]);
$siswa_list = $stmt->fetchAll();

// Get subjects
$stmt = $pdo->query("SELECT * FROM mata_pelajaran ORDER BY nama_mapel");
$mapel_list = $stmt->fetchAll();

// Get grades data
$stmt = $pdo->prepare("
    SELECT n.*, s.nama_lengkap as nama_siswa, s.kelas, mp.nama_mapel 
    FROM nilai n 
    JOIN siswa s ON n.siswa_id = s.id 
    JOIN mata_pelajaran mp ON n.mata_pelajaran_id = mp.id 
    WHERE s.kelas = ? AND n.semester = ? AND n.tahun_ajaran = ?
    ORDER BY s.nama_lengkap, mp.nama_mapel
");
$stmt->execute([$kelas, $semester, $tahun_ajaran]);
$nilai_list = $stmt->fetchAll();
?>

<?php include '../includes/header.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Manajemen Nilai</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahNilaiModal">
                    <i class="fas fa-plus"></i> Tambah Nilai
                </button>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Filter Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Kelas</label>
                            <select class="form-select" name="kelas">
                                <option value="VII A" <?php echo $kelas == 'VII A' ? 'selected' : ''; ?>>VII A</option>
                                <option value="VII B" <?php echo $kelas == 'VII B' ? 'selected' : ''; ?>>VII B</option>
                                <option value="VIII A" <?php echo $kelas == 'VIII A' ? 'selected' : ''; ?>>VIII A</option>
                                <option value="VIII B" <?php echo $kelas == 'VIII B' ? 'selected' : ''; ?>>VIII B</option>
                                <option value="IX A" <?php echo $kelas == 'IX A' ? 'selected' : ''; ?>>IX A</option>
                                <option value="IX B" <?php echo $kelas == 'IX B' ? 'selected' : ''; ?>>IX B</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester">
                                <option value="1" <?php echo $semester == '1' ? 'selected' : ''; ?>>1</option>
                                <option value="2" <?php echo $semester == '2' ? 'selected' : ''; ?>>2</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tahun Ajaran</label>
                            <input type="text" class="form-control" name="tahun_ajaran" value="<?php echo $tahun_ajaran; ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Grades Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Nilai Kelas <?php echo $kelas; ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th>Mata Pelajaran</th>
                                    <th>UH 1</th>
                                    <th>UH 2</th>
                                    <th>UTS</th>
                                    <th>UAS</th>
                                    <th>Nilai Akhir</th>
                                    <th>Semester</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($nilai_list as $nilai): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($nilai['nama_siswa']); ?></td>
                                    <td><?php echo htmlspecialchars($nilai['nama_mapel']); ?></td>
                                    <td><?php echo number_format($nilai['nilai_uh1'], 1); ?></td>
                                    <td><?php echo number_format($nilai['nilai_uh2'], 1); ?></td>
                                    <td><?php echo number_format($nilai['nilai_uts'], 1); ?></td>
                                    <td><?php echo number_format($nilai['nilai_uas'], 1); ?></td>
                                    <td><strong><?php echo number_format($nilai['nilai_akhir'], 1); ?></strong></td>
                                    <td><?php echo $nilai['semester']; ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editNilaiModal" 
                                                    data-id="<?php echo $nilai['id']; ?>"
                                                    data-siswa="<?php echo $nilai['siswa_id']; ?>"
                                                    data-mapel="<?php echo $nilai['mata_pelajaran_id']; ?>"
                                                    data-uh1="<?php echo $nilai['nilai_uh1']; ?>"
                                                    data-uh2="<?php echo $nilai['nilai_uh2']; ?>"
                                                    data-uts="<?php echo $nilai['nilai_uts']; ?>"
                                                    data-uas="<?php echo $nilai['nilai_uas']; ?>"
                                                    data-semester="<?php echo $nilai['semester']; ?>"
                                                    data-tahun="<?php echo $nilai['tahun_ajaran']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#hapusNilaiModal" 
                                                    data-id="<?php echo $nilai['id']; ?>"
                                                    data-siswa="<?php echo $nilai['nama_siswa']; ?>"
                                                    data-mapel="<?php echo $nilai['nama_mapel']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (empty($nilai_list)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                            <p>Belum ada data nilai untuk filter yang dipilih.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal Tambah Nilai -->
<div class="modal fade" id="tambahNilaiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Nilai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Siswa</label>
                                <select class="form-select" name="siswa_id" required>
                                    <option value="">-- Pilih Siswa --</option>
                                    <?php foreach ($siswa_list as $siswa): ?>
                                        <option value="<?php echo $siswa['id']; ?>"><?php echo htmlspecialchars($siswa['nama_lengkap']); ?> - <?php echo htmlspecialchars($siswa['kelas']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Mata Pelajaran</label>
                                <select class="form-select" name="mata_pelajaran_id" required>
                                    <option value="">-- Pilih Mata Pelajaran --</option>
                                    <?php foreach ($mapel_list as $mapel): ?>
                                        <option value="<?php echo $mapel['id']; ?>"><?php echo htmlspecialchars($mapel['nama_mapel']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">UH 1</label>
                                <input type="number" class="form-control" name="nilai_uh1" min="0" max="100" step="0.1" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">UH 2</label>
                                <input type="number" class="form-control" name="nilai_uh2" min="0" max="100" step="0.1" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">UTS</label>
                                <input type="number" class="form-control" name="nilai_uts" min="0" max="100" step="0.1" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">UAS</label>
                                <input type="number" class="form-control" name="nilai_uas" min="0" max="100" step="0.1" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Semester</label>
                                <select class="form-select" name="semester" required>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tahun Ajaran</label>
                                <input type="text" class="form-control" name="tahun_ajaran" value="2024/2025" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="tambah_nilai">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Nilai -->
<div class="modal fade" id="editNilaiModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data Nilai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">UH 1</label>
                                <input type="number" class="form-control" name="nilai_uh1" id="edit_uh1" min="0" max="100" step="0.1" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">UH 2</label>
                                <input type="number" class="form-control" name="nilai_uh2" id="edit_uh2" min="0" max="100" step="0.1" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">UTS</label>
                                <input type="number" class="form-control" name="nilai_uts" id="edit_uts" min="0" max="100" step="0.1" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">UAS</label>
                                <input type="number" class="form-control" name="nilai_uas" id="edit_uas" min="0" max="100" step="0.1" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Semester</label>
                                <select class="form-select" name="semester" id="edit_semester" required>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tahun Ajaran</label>
                                <input type="text" class="form-control" name="tahun_ajaran" id="edit_tahun" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" name="edit_nilai">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Hapus Nilai -->
<div class="modal fade" id="hapusNilaiModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Data Nilai</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="hapus_id">
                    <p>Apakah Anda yakin ingin menghapus nilai <strong id="hapus_siswa"></strong> untuk mata pelajaran <strong id="hapus_mapel"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" name="hapus_nilai">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Script untuk modal edit nilai
    var editNilaiModal = document.getElementById('editNilaiModal');
    editNilaiModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('edit_id').value = button.getAttribute('data-id');
        document.getElementById('edit_uh1').value = button.getAttribute('data-uh1');
        document.getElementById('edit_uh2').value = button.getAttribute('data-uh2');
        document.getElementById('edit_uts').value = button.getAttribute('data-uts');
        document.getElementById('edit_uas').value = button.getAttribute('data-uas');
        document.getElementById('edit_semester').value = button.getAttribute('data-semester');
        document.getElementById('edit_tahun').value = button.getAttribute('data-tahun');
    });

    // Script untuk modal hapus nilai
    var hapusNilaiModal = document.getElementById('hapusNilaiModal');
    hapusNilaiModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        document.getElementById('hapus_id').value = button.getAttribute('data-id');
        document.getElementById('hapus_siswa').textContent = button.getAttribute('data-siswa');
        document.getElementById('hapus_mapel').textContent = button.getAttribute('data-mapel');
    });
</script>

<?php include '../includes/footer.php'; ?>
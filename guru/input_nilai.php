<?php
require_once '../config/database.php';
require_once '../config/session.php';
checkAuth('guru');

// Ambil data guru
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM guru WHERE user_id = ?");
$stmt->execute([$user_id]);
$guru = $stmt->fetch();

// Handle input nilai
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_nilai'])) {
    $siswa_id = $_POST['siswa_id'];
    $mata_pelajaran_id = $_POST['mata_pelajaran_id'];
    $nilai_uh1 = $_POST['nilai_uh1'];
    $nilai_uh2 = $_POST['nilai_uh2'];
    $nilai_uts = $_POST['nilai_uts'];
    $nilai_uas = $_POST['nilai_uas'];
    $semester = $_POST['semester'];
    $tahun_ajaran = $_POST['tahun_ajaran'];
    
    // Hitung nilai akhir
    $nilai_akhir = ($nilai_uh1 + $nilai_uh2 + $nilai_uts + $nilai_uas) / 4;
    
    // Cek apakah nilai sudah ada
    $stmt = $pdo->prepare("SELECT id FROM nilai WHERE siswa_id = ? AND mata_pelajaran_id = ? AND semester = ? AND tahun_ajaran = ?");
    $stmt->execute([$siswa_id, $mata_pelajaran_id, $semester, $tahun_ajaran]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        // Update nilai
        $stmt = $pdo->prepare("UPDATE nilai SET nilai_uh1=?, nilai_uh2=?, nilai_uts=?, nilai_uas=?, nilai_akhir=? WHERE id=?");
        $stmt->execute([$nilai_uh1, $nilai_uh2, $nilai_uts, $nilai_uas, $nilai_akhir, $existing['id']]);
    } else {
        // Insert nilai baru
        $stmt = $pdo->prepare("INSERT INTO nilai (siswa_id, mata_pelajaran_id, nilai_uh1, nilai_uh2, nilai_uts, nilai_uas, nilai_akhir, semester, tahun_ajaran) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$siswa_id, $mata_pelajaran_id, $nilai_uh1, $nilai_uh2, $nilai_uts, $nilai_uas, $nilai_akhir, $semester, $tahun_ajaran]);
    }
    
    $success = "Nilai berhasil disimpan!";
}

// Default values
$mapel_id = $_GET['mapel'] ?? '';
$kelas = $_GET['kelas'] ?? 'VII A';
$semester = $_GET['semester'] ?? '1';
$tahun_ajaran = $_GET['tahun_ajaran'] ?? '2024/2025';

// Ambil mata pelajaran yang diajar oleh guru ini
$stmt = $pdo->prepare("SELECT * FROM mata_pelajaran WHERE guru_id = ? ORDER BY kelas, nama_mapel");
$stmt->execute([$guru['id']]);
$mapel_guru = $stmt->fetchAll();

// Ambil data siswa berdasarkan kelas
if ($kelas) {
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE kelas = ? ORDER BY nama_lengkap");
    $stmt->execute([$kelas]);
    $siswa_list = $stmt->fetchAll();
}

// Ambil data nilai untuk tabel
if ($mapel_id && $kelas) {
    $stmt = $pdo->prepare("
        SELECT n.*, s.nama_lengkap as nama_siswa 
        FROM nilai n 
        JOIN siswa s ON n.siswa_id = s.id 
        WHERE n.mata_pelajaran_id = ? AND s.kelas = ? AND n.semester = ? AND n.tahun_ajaran = ?
        ORDER BY s.nama_lengkap
    ");
    $stmt->execute([$mapel_id, $kelas, $semester, $tahun_ajaran]);
    $nilai_list = $stmt->fetchAll();
}
?>

<?php include '../includes/header.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Input Nilai</h1>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Filter Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Mata Pelajaran</label>
                            <select class="form-select" name="mapel" required>
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                <?php foreach ($mapel_guru as $mapel): ?>
                                    <option value="<?php echo $mapel['id']; ?>" <?php echo $mapel_id == $mapel['id'] ? 'selected' : ''; ?>>
                                        <?php echo $mapel['nama_mapel']; ?> - <?php echo $mapel['kelas']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
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
                        <div class="col-md-2">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester">
                                <option value="1" <?php echo $semester == '1' ? 'selected' : ''; ?>>1</option>
                                <option value="2" <?php echo $semester == '2' ? 'selected' : ''; ?>>2</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Tahun Ajaran</label>
                            <input type="text" class="form-control" name="tahun_ajaran" value="<?php echo $tahun_ajaran; ?>">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if ($mapel_id && $kelas): ?>
            <!-- Form Input Nilai -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Input Nilai per Siswa</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="mata_pelajaran_id" value="<?php echo $mapel_id; ?>">
                        <input type="hidden" name="semester" value="<?php echo $semester; ?>">
                        <input type="hidden" name="tahun_ajaran" value="<?php echo $tahun_ajaran; ?>">
                        
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Siswa</label>
                                <select class="form-select" name="siswa_id" required>
                                    <option value="">-- Pilih Siswa --</option>
                                    <?php foreach ($siswa_list as $siswa): ?>
                                        <option value="<?php echo $siswa['id']; ?>"><?php echo $siswa['nama_lengkap']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">UH 1</label>
                                <input type="number" class="form-control" name="nilai_uh1" min="0" max="100" step="0.1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">UH 2</label>
                                <input type="number" class="form-control" name="nilai_uh2" min="0" max="100" step="0.1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">UTS</label>
                                <input type="number" class="form-control" name="nilai_uts" min="0" max="100" step="0.1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">UAS</label>
                                <input type="number" class="form-control" name="nilai_uas" min="0" max="100" step="0.1" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-success w-100" name="simpan_nilai">Simpan Nilai</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Nilai -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Nilai</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th>UH 1</th>
                                    <th>UH 2</th>
                                    <th>UTS</th>
                                    <th>UAS</th>
                                    <th>Nilai Akhir</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($nilai_list as $nilai): ?>
                                <tr>
                                    <td><?php echo $nilai['nama_siswa']; ?></td>
                                    <td><?php echo number_format($nilai['nilai_uh1'], 1); ?></td>
                                    <td><?php echo number_format($nilai['nilai_uh2'], 1); ?></td>
                                    <td><?php echo number_format($nilai['nilai_uts'], 1); ?></td>
                                    <td><?php echo number_format($nilai['nilai_uas'], 1); ?></td>
                                    <td><strong><?php echo number_format($nilai['nilai_akhir'], 1); ?></strong></td>
                                    <td>
                                        <button class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-info">Silakan pilih mata pelajaran dan kelas untuk menampilkan data.</div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
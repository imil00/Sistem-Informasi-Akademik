<?php
require_once '../config/database.php';
require_once '../config/session.php';
checkAuth('siswa');

// Ambil data siswa
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM siswa WHERE user_id = ?");
$stmt->execute([$user_id]);
$siswa = $stmt->fetch();

// Default values
$semester = $_GET['semester'] ?? '1';
$tahun_ajaran = $_GET['tahun_ajaran'] ?? '2024/2025';

// Ambil data nilai
$stmt = $pdo->prepare("
    SELECT n.*, mp.nama_mapel 
    FROM nilai n 
    JOIN mata_pelajaran mp ON n.mata_pelajaran_id = mp.id 
    WHERE n.siswa_id = ? AND n.semester = ? AND n.tahun_ajaran = ?
    ORDER BY mp.nama_mapel
");
$stmt->execute([$siswa['id'], $semester, $tahun_ajaran]);
$nilai_list = $stmt->fetchAll();

// Hitung rata-rata
$total_nilai = 0;
$jumlah_mapel = count($nilai_list);
foreach ($nilai_list as $nilai) {
    $total_nilai += $nilai['nilai_akhir'];
}
$rata_rata = $jumlah_mapel > 0 ? $total_nilai / $jumlah_mapel : 0;
?>

<?php include '../includes/header.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Data Nilai</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="text-muted"><?php echo $siswa['nama_lengkap']; ?> - <?php echo $siswa['kelas']; ?></span>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Semester</label>
                            <select class="form-select" name="semester">
                                <option value="1" <?php echo $semester == '1' ? 'selected' : ''; ?>>Semester 1</option>
                                <option value="2" <?php echo $semester == '2' ? 'selected' : ''; ?>>Semester 2</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tahun Ajaran</label>
                            <input type="text" class="form-control" name="tahun_ajaran" value="<?php echo $tahun_ajaran; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistik Nilai -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-primary">
                        <div class="card-body text-center">
                            <h4><?php echo number_format($rata_rata, 2); ?></h4>
                            <p>Rata-rata Nilai</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body text-center">
                            <h4><?php echo $jumlah_mapel; ?></h4>
                            <p>Jumlah Mata Pelajaran</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body text-center">
                            <h4><?php echo getJumlahPredikat($nilai_list, 'A'); ?></h4>
                            <p>Nilai A</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body text-center">
                            <h4><?php echo getJumlahPredikat($nilai_list, 'B'); ?></h4>
                            <p>Nilai B</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Nilai -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        Daftar Nilai - Semester <?php echo $semester; ?> - Tahun Ajaran <?php echo $tahun_ajaran; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Mata Pelajaran</th>
                                    <th>UH 1</th>
                                    <th>UH 2</th>
                                    <th>UTS</th>
                                    <th>UAS</th>
                                    <th>Nilai Akhir</th>
                                    <th>Predikat</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($nilai_list as $nilai): 
                                    $predikat = getPredikat($nilai['nilai_akhir']);
                                    $predikat_color = getPredikatColor($nilai['nilai_akhir']);
                                ?>
                                <tr>
                                    <td><?php echo $nilai['nama_mapel']; ?></td>
                                    <td><?php echo number_format($nilai['nilai_uh1'], 1); ?></td>
                                    <td><?php echo number_format($nilai['nilai_uh2'], 1); ?></td>
                                    <td><?php echo number_format($nilai['nilai_uts'], 1); ?></td>
                                    <td><?php echo number_format($nilai['nilai_uas'], 1); ?></td>
                                    <td><strong><?php echo number_format($nilai['nilai_akhir'], 1); ?></strong></td>
                                    <td>
                                        <span class="badge bg-<?php echo $predikat_color; ?>">
                                            <?php echo $predikat; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (empty($nilai_list)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                            <p>Belum ada data nilai untuk semester ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
// Helper functions
function getPredikat($nilai) {
    if ($nilai >= 90) return 'A';
    if ($nilai >= 80) return 'B';
    if ($nilai >= 70) return 'C';
    if ($nilai >= 60) return 'D';
    return 'E';
}

function getPredikatColor($nilai) {
    if ($nilai >= 90) return 'success';
    if ($nilai >= 80) return 'primary';
    if ($nilai >= 70) return 'warning';
    if ($nilai >= 60) return 'info';
    return 'danger';
}

function getJumlahPredikat($nilai_list, $predikat) {
    $count = 0;
    foreach ($nilai_list as $nilai) {
        if (getPredikat($nilai['nilai_akhir']) == $predikat) {
            $count++;
        }
    }
    return $count;
}
?>

<?php include '../includes/footer.php'; ?>
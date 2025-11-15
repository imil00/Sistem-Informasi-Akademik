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
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

// Ambil data absensi
$stmt = $pdo->prepare("
    SELECT * FROM absensi 
    WHERE siswa_id = ? AND MONTH(tanggal) = ? AND YEAR(tanggal) = ?
    ORDER BY tanggal DESC
");
$stmt->execute([$siswa['id'], $bulan, $tahun]);
$absensi_list = $stmt->fetchAll();

// Hitung statistik
$total_hari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);
$hadir = 0;
$izin = 0;
$sakit = 0;
$alpha = 0;

foreach ($absensi_list as $absensi) {
    switch ($absensi['status']) {
        case 'Hadir': $hadir++; break;
        case 'Izin': $izin++; break;
        case 'Sakit': $sakit++; break;
        case 'Alpha': $alpha++; break;
    }
}

$persentase_kehadiran = $total_hari > 0 ? ($hadir / $total_hari) * 100 : 0;
?>

<?php include '../includes/header.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Data Absensi</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="text-muted"><?php echo $siswa['nama_lengkap']; ?> - <?php echo $siswa['kelas']; ?></span>
                </div>
            </div>

            <!-- Filter Form -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Bulan</label>
                            <select class="form-select" name="bulan">
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?php echo sprintf('%02d', $i); ?>" <?php echo $bulan == sprintf('%02d', $i) ? 'selected' : ''; ?>>
                                        <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tahun</label>
                            <input type="number" class="form-control" name="tahun" value="<?php echo $tahun; ?>" min="2020" max="2030">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Statistik Absensi -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card text-white bg-success">
                        <div class="card-body text-center">
                            <h4><?php echo $hadir; ?></h4>
                            <p>Hadir</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-info">
                        <div class="card-body text-center">
                            <h4><?php echo $izin; ?></h4>
                            <p>Izin</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-warning">
                        <div class="card-body text-center">
                            <h4><?php echo $sakit; ?></h4>
                            <p>Sakit</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-white bg-danger">
                        <div class="card-body text-center">
                            <h4><?php echo $alpha; ?></h4>
                            <p>Alpha</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Progress Bar Kehadiran -->
            <div class="card mb-4">
                <div class="card-body">
                    <h6 class="card-title">Persentase Kehadiran Bulan <?php echo date('F', mktime(0, 0, 0, $bulan, 1)); ?></h6>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" style="width: <?php echo $persentase_kehadiran; ?>%">
                            <?php echo number_format($persentase_kehadiran, 1); ?>%
                        </div>
                    </div>
                    <small class="text-muted"><?php echo $hadir; ?> dari <?php echo $total_hari; ?> hari</small>
                </div>
            </div>

            <!-- Tabel Absensi -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        Riwayat Absensi - <?php echo date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)); ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Hari</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($absensi_list as $absensi): 
                                    $status_color = [
                                        'Hadir' => 'success',
                                        'Izin' => 'info',
                                        'Sakit' => 'warning',
                                        'Alpha' => 'danger'
                                    ][$absensi['status']] ?? 'secondary';
                                ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($absensi['tanggal'])); ?></td>
                                    <td><?php echo date('l', strtotime($absensi['tanggal'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $status_color; ?>">
                                            <?php echo $absensi['status']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $absensi['keterangan'] ?: '-'; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (empty($absensi_list)): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-times fa-3x mb-3"></i>
                            <p>Belum ada data absensi untuk bulan ini.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
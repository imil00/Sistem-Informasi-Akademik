<?php
require_once '../config/database.php';
require_once '../config/session.php';
checkAuth('guru');

// Ambil data guru
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM guru WHERE user_id = ?");
$stmt->execute([$user_id]);
$guru = $stmt->fetch();

// Handle input absensi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_absensi'])) {
    $tanggal = $_POST['tanggal'];
    $kelas = $_POST['kelas'];
    
    foreach ($_POST['absensi'] as $siswa_id => $status) {
        $keterangan = $_POST['keterangan'][$siswa_id] ?? '';
        
        // Cek apakah absensi sudah ada
        $stmt = $pdo->prepare("SELECT id FROM absensi WHERE siswa_id = ? AND tanggal = ?");
        $stmt->execute([$siswa_id, $tanggal]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Update absensi
            $stmt = $pdo->prepare("UPDATE absensi SET status = ?, keterangan = ? WHERE id = ?");
            $stmt->execute([$status, $keterangan, $existing['id']]);
        } else {
            // Insert absensi baru
            $stmt = $pdo->prepare("INSERT INTO absensi (siswa_id, tanggal, status, keterangan) VALUES (?, ?, ?, ?)");
            $stmt->execute([$siswa_id, $tanggal, $status, $keterangan]);
        }
    }
    
    $success = "Data absensi berhasil disimpan!";
}

// Default values
$tanggal = $_POST['tanggal'] ?? date('Y-m-d');
$kelas = $_POST['kelas'] ?? 'VII A';

// Ambil kelas yang diajar oleh guru ini
$stmt = $pdo->prepare("SELECT DISTINCT kelas FROM mata_pelajaran WHERE guru_id = ?");
$stmt->execute([$guru['id']]);
$kelas_list = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Ambil data siswa berdasarkan kelas
if ($kelas) {
    $stmt = $pdo->prepare("SELECT * FROM siswa WHERE kelas = ? ORDER BY nama_lengkap");
    $stmt->execute([$kelas]);
    $siswa_list = $stmt->fetchAll();
}

// Ambil data absensi untuk tanggal tertentu
$absensi_data = [];
if ($tanggal && $kelas) {
    $stmt = $pdo->prepare("
        SELECT a.*, s.nama_lengkap 
        FROM absensi a 
        JOIN siswa s ON a.siswa_id = s.id 
        WHERE a.tanggal = ? AND s.kelas = ?
    ");
    $stmt->execute([$tanggal, $kelas]);
    $absensi_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Konversi ke format yang mudah diakses
$absensi_map = [];
foreach ($absensi_data as $absensi) {
    $absensi_map[$absensi['siswa_id']] = $absensi;
}
?>

<?php include '../includes/header.php'; ?>

<?php include '../includes/navbar.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Input Absensi</h1>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <!-- Form Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="POST" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tanggal</label>
                            <input type="date" class="form-control" name="tanggal" value="<?php echo $tanggal; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kelas</label>
                            <select class="form-select" name="kelas" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php foreach ($kelas_list as $k): ?>
                                    <option value="<?php echo $k; ?>" <?php echo $kelas == $k ? 'selected' : ''; ?>><?php echo $k; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">Tampilkan Data</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Form Absensi -->
            <?php if (!empty($siswa_list)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Input Absensi - <?php echo date('d/m/Y', strtotime($tanggal)); ?> - Kelas <?php echo $kelas; ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="tanggal" value="<?php echo $tanggal; ?>">
                        <input type="hidden" name="kelas" value="<?php echo $kelas; ?>">
                        
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($siswa_list as $index => $siswa): 
                                        $absensi = $absensi_map[$siswa['id']] ?? null;
                                    ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo $siswa['nis']; ?></td>
                                        <td><?php echo $siswa['nama_lengkap']; ?></td>
                                        <td>
                                            <select class="form-select" name="absensi[<?php echo $siswa['id']; ?>]" required>
                                                <option value="Hadir" <?php echo ($absensi && $absensi['status'] == 'Hadir') ? 'selected' : ''; ?>>Hadir</option>
                                                <option value="Izin" <?php echo ($absensi && $absensi['status'] == 'Izin') ? 'selected' : ''; ?>>Izin</option>
                                                <option value="Sakit" <?php echo ($absensi && $absensi['status'] == 'Sakit') ? 'selected' : ''; ?>>Sakit</option>
                                                <option value="Alpha" <?php echo ($absensi && $absensi['status'] == 'Alpha') ? 'selected' : ''; ?>>Alpha</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" name="keterangan[<?php echo $siswa['id']; ?>]" 
                                                   value="<?php echo $absensi ? $absensi['keterangan'] : ''; ?>" 
                                                   placeholder="Keterangan (opsional)">
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-success" name="simpan_absensi">
                                <i class="fas fa-save"></i> Simpan Absensi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <?php if (empty($kelas_list)): ?>
                        Anda tidak mengajar kelas apapun.
                    <?php else: ?>
                        Silakan pilih tanggal dan kelas untuk menampilkan data siswa.
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
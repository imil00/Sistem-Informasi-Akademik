<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <img src="../assets/img/logo.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-top me-2">
            SMPN 8 BALIKPAPAN
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user me-1"></i><?php echo $_SESSION['nama_lengkap']; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Pengaturan</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <?php 
                            $logout_url = '';
                            switch ($_SESSION['role']) {
                                case 'admin': $logout_url = '../admin/logout.php'; break;
                                case 'guru': $logout_url = '../guru/logout.php'; break;
                                case 'siswa': $logout_url = '../siswa/logout.php'; break;
                            }
                            ?>
                            <a class="dropdown-item text-danger" href="<?php echo $logout_url; ?>">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
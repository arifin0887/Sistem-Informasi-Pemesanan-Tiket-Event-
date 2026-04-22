

<aside id="sidebar" class="sidebar">
    <ul class="sidebar-nav" id="sidebar-nav">

        <li class="nav-item">
            <?php 
                // CEK HALAMAN AKTIF
                $is_dashboard = (!isset($_GET['page']) || $_GET['page'] == 'petugas');
            ?>
            <a class="nav-link <?= $is_dashboard ? '' : 'collapsed'; ?>" href="index.php?page=petugas">
                <i class="bi bi-grid"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-heading">Layanan Checkin</li>

        <li class="nav-item">
            <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'riwayat') ? '' : 'collapsed'; ?>" href="index.php?page=riwayat">
                <i class="bi bi-clock-history"></i>
                <span>Riwayat Check-in</span>
            </a>
        </li>

    </ul>
</aside>
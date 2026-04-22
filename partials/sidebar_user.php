<div id="sidebarOverlay" class="sidebar-overlay" onclick="toggleSidebar()"></div>

<aside id="sidebar" class="sidebar shadow-sm">
    <ul class="sidebar-nav" id="sidebar-nav">
        
        <li class="nav-item">
            <?php $is_dashboard = (!isset($_GET['page']) || $_GET['page'] == 'user' || $_GET['page'] == 'dashboard'); ?>
            <a class="nav-link <?= $is_dashboard ? '' : 'collapsed'; ?>" href="index.php?page=user">
                <i class="bi bi-grid-fill"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-heading">Layanan Tiket</li>

        <li class="nav-item">
            <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'event') ? '' : 'collapsed'; ?>" href="index.php?page=event">
                <i class="bi bi-ticket-perforated"></i>
                <span>Cari Tiket</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link <?= (isset($_GET['page']) && ($_GET['page'] == 'riwayat' || $_GET['page'] == 'detail_transaksi')) ? '' : 'collapsed'; ?>" href="index.php?page=riwayat">
                <i class="bi bi-clock-history"></i>
                <span>Riwayat Saya</span>
            </a>
        </li>
        
    </ul>
</aside>
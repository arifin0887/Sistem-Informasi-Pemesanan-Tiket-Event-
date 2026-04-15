<style>
    .sidebar {
        background-color: #fff;
        box-shadow: 0px 0px 20px rgba(1, 41, 112, 0.1);
    }
    
    .sidebar-nav .nav-link {
        color: #1d1145;
        background: #fff;
        font-weight: 600;
        transition: 0.3s;
        border-radius: 8px;
        margin-bottom: 5px;
    }

    /* State saat menu aktif */
    .sidebar-nav .nav-link:not(.collapsed) {
        color: #e66c8a;
        background: #fef1f4;
    }

    .sidebar-nav .nav-link:not(.collapsed) i {
        color: #e66c8a;
    }

    .sidebar-nav .nav-link:hover {
        color: #e66c8a;
        background: #fef1f4;
    }

    .sidebar-nav .nav-link i {
        font-size: 1.1rem;
        margin-right: 10px;
        color: #899bbd;
    }

    .nav-heading {
        font-size: 11px;
        text-transform: uppercase;
        color: #899bbd;
        font-weight: 700;
        margin: 10px 0 5px 15px;
    }
</style>

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
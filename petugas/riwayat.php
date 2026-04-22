<?php

// AMBIL DAFTAR EVENT UNTUK FILTER (Hanya event yang punya data attendee)
$query_events = "SELECT DISTINCT e.id_event, e.nama_event 
                 FROM event e 
                 JOIN tiket t ON e.id_event = t.id_event 
                 JOIN order_detail od ON t.id_tiket = od.id_tiket
                 JOIN attendee a ON od.id_detail = a.id_detail";
$res_events = mysqli_query($conn, $query_events);

// TANGKAP FILTER ID_EVENT
$filter_event = isset($_GET['id_event']) ? $_GET['id_event'] : '';

// QUERY RIWAYAT DENGAN FILTER
$query = "
    SELECT 
        a.kode_tiket, 
        a.waktu_checkin, 
        a.status_checkin,
        t.nama_tiket,
        u.nama AS nama_pembeli,
        e.nama_event
    FROM attendee a
    JOIN order_detail od ON a.id_detail = od.id_detail
    JOIN tiket t ON od.id_tiket = t.id_tiket
    JOIN event e ON t.id_event = e.id_event
    JOIN orders o ON od.id_order = o.id_order
    JOIN users u ON o.id_user = u.id_user
    WHERE a.status_checkin = 'sudah'
";

if ($filter_event != '') {
    $query .= " AND e.id_event = '" . mysqli_real_escape_string($conn, $filter_event) . "'";
}

$query .= " ORDER BY a.waktu_checkin DESC";
$result = mysqli_query($conn, $query);
?>

<style>
    .history-card { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
    .user-avatar {
        width: 38px; height: 38px; background: #f0f2f5; color: #1D1145;
        display: flex; align-items: center; justify-content: center;
        border-radius: 10px; font-weight: bold; margin-right: 12px;
    }
    .ticket-code { background: #fff8e1; color: #f57f17; padding: 4px 8px; border-radius: 5px; font-weight: 600; }
    .badge-status { background: #e8f5e9; color: #2e7d32; padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .checkin-time { font-weight: 600; color: #1D1145; }
    .filter-section { background: #f8f9fa; border-radius: 12px; padding: 15px; margin-bottom: 20px; }
</style>

<div class="pagetitle mb-4">
    <h1>Riwayat Check-in</h1>
    <nav>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
            <li class="breadcrumb-item active">Riwayat Check-in</li>
        </ol>
    </nav>
</div>

<div class="card history-card mb-4">
    <div class="card-body p-3">
        <div class="row align-items-center g-3">
            <div class="col-md-8">
                <div class="d-flex align-items-center">
                    <i class="bi bi-filter-left fs-4 me-2 text-muted"></i>
                    <select id="filterEvent" class="form-select border-0 bg-light" style="border-radius: 10px;">
                        <option value="">Semua Event</option>
                        <?php 
                        $res_events = mysqli_query($conn, "SELECT DISTINCT e.id_event, e.nama_event FROM event e JOIN tiket t ON e.id_event = t.id_event JOIN order_detail od ON t.id_tiket = od.id_tiket JOIN attendee a ON od.id_detail = a.id_detail");
                        while($ev = mysqli_fetch_assoc($res_events)): 
                        ?>
                            <option value="<?= $ev['id_event'] ?>"><?= htmlspecialchars($ev['nama_event']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="table-checkin-container">
    <?php include 'fetch_riwayat.php'; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#filterEvent').on('change', function() {
        var idEvent = $(this).val();
        
        // EFEK LOADING
        $('#table-checkin-container').css('opacity', '0.5');

        $.ajax({
            url: 'fetch_riwayat.php', 
            type: 'GET',
            data: { id_event: idEvent },
            success: function(data) {
                $('#table-checkin-container').html(data);
                $('#table-checkin-container').css('opacity', '1');
            },
            error: function() {
                alert('Gagal mengambil data.');
                $('#table-checkin-container').css('opacity', '1');
            }
        });
    });
});
</script>


<style>
    .history-card {
        border: none;
        border-radius: 24px;
        background: #ffffff;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03) !important;
    }

    .table thead th {
        background-color: #f8f9fa;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 700;
        color: #94a3b8;
        border: none;
        padding: 15px 20px;
    }

    .table tbody td {
        padding: 18px 20px;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .table-hover tbody tr:hover {
        background-color: #f8fbff;
        transition: all 0.2s ease;
    }

    .user-avatar {
        width: 38px;
        height: 38px;
        background: #eef2ff;
        color: #4f46e5;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-right: 12px;
    }

    .badge-status {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
        font-weight: 600;
        padding: 6px 12px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge-status::before {
        content: '';
        width: 6px;
        height: 6px;
        background: #22c55e;
        border-radius: 50%;
    }

    .ticket-code {
        font-family: 'Monaco', monospace;
        background: #f1f5f9;
        color: #475569;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.85rem;
    }

    .checkin-time {
        font-size: 0.9rem;
        font-weight: 600;
    }
</style>
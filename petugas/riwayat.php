<?php

// AMBIL DAFTAR EVENT UNTUK FILTER
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

<div class="card history-card mb-4 border-0 shadow-sm">
    <div class="card-body p-3">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="input-group bg-light rounded-3 px-2">
                    <span class="input-group-text border-0 bg-transparent text-muted">
                        <i class="bi bi-filter-left fs-4"></i>
                    </span>
                    <select id="filterEvent" class="form-select border-0 bg-transparent py-2 shadow-none">
                        <option value="">Semua Event</option>
                        <?php while($ev = mysqli_fetch_assoc($res_events)): ?>
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
            $('#table-checkin-container').css('opacity', '0.5');

            $.ajax({
                url: 'fetch_riwayat.php', 
                type: 'GET',
                data: { id_event: idEvent },
                success: function(data) {
                    $('#table-checkin-container').html(data).css('opacity', '1');
                },
                error: function() {
                    alert('Gagal mengambil data.');
                    $('#table-checkin-container').css('opacity', '1');
                }
            });
        });
    });
</script>
<style>
    .scanner-wrapper {
        position: relative;
        overflow: hidden;
        border-radius: 25px;
        background: #000;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    #reader {
        border: none !important;
    }

    /* Menghilangkan tulisan link dan info default dari library */
    #reader__dashboard_section_csr button {
        background-color: #0d6efd !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 10px 20px !important;
        color: white !important;
        text-transform: uppercase;
        font-weight: bold;
        font-size: 0.8rem;
    }

    /* Efek Viewfinder (Sudut Siku) */
    .scanner-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 250px;
        height: 250px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        pointer-events: none;
        z-index: 2;
    }

    /* Animasi Garis Scan */
    .scan-line {
        position: absolute;
        width: 100%;
        height: 3px;
        background: linear-gradient(to bottom, transparent, #0d6efd, transparent);
        top: 0;
        animation: scanAnim 2s infinite linear;
        box-shadow: 0 0 15px rgba(13, 110, 253, 0.8);
    }

    @keyframes scanAnim {
        0% { top: 0%; }
        100% { top: 100%; }
    }

    .status-badge {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        background: #f8f9fa;
        color: #6c757d;
        border: 1px solid #dee2e6;
    }
</style>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm" style="border-radius: 20px;">
            <div class="card-header bg-white border-0 py-3 text-center">
                <h5 class="fw-bold mb-0" style="color: #1e293b;">
                    <i class="bi bi-qr-code-scan me-2 text-primary"></i>Check-In Peserta
                </h5>
            </div>
            
            <div class="card-body p-4 text-center">
                <div class="scanner-wrapper mb-4">
                    <div id="reader"></div>
                    
                    <div class="scanner-overlay">
                        <div class="scan-line"></div>
                    </div>
                </div>

                <div id="result-container">
                    <span class="status-badge pulse">
                        <i class="bi bi-camera me-2"></i>Arahkan ke QR Code Tiket
                    </span>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3 bg-light">
            <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between">
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Pastikan cahaya cukup</small>
                <button class="btn btn-link btn-sm text-decoration-none" onclick="location.reload()">Reset Kamera</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/html5-qrcode"></script>

<script>
    function onScanSuccess(decodedText) {
        // Matikan scanner sementara agar tidak scan berkali-kali (double check-in)
        html5QrcodeScanner.clear();

        // Beri feedback visual bahwa data sedang dikirim
        document.getElementById('result-container').innerHTML = `
            <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
            <span class="fw-bold text-primary">Memproses data...</span>
        `;

        fetch('proses_checkin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'kode_tiket=' + encodeURIComponent(decodedText)
        })
        .then(response => response.json()) 
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Check-In Berhasil!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload(); 
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message,
                    confirmButtonColor: '#0d6efd'
                }).then(() => {
                    location.reload();
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Koneksi Terputus',
                text: 'Gagal menghubungi server.',
                confirmButtonColor: '#0d6efd'
            }).then(() => {
                location.reload();
            });
        });
    }

    // Konfigurasi scanner yang lebih smooth
    let config = { 
        fps: 15, // FPS lebih tinggi agar lebih responsif
        qrbox: { width: 250, height: 250 },
        aspectRatio: 1.0 
    };

    let html5QrcodeScanner = new Html5QrcodeScanner("reader", config);
    html5QrcodeScanner.render(onScanSuccess);
</script>
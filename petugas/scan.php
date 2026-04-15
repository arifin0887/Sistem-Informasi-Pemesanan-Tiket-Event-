<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0 text-white"><i class="bi bi-camera"></i> Scanner Tiket</h5>
    </div>
    <div class="card-body text-center">
        <div id="reader" style="width: 100%; border-radius: 10px;"></div>
        <div id="result" class="mt-3">Arahkan kamera ke QR Code</div>
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    function onScanSuccess(decodedText) {
        // HENTIKAN SCANNER SEBAGAI TIKET SUDAH TERBACA
        html5QrcodeScanner.clear();

        // KIRIM DATA KODE TIKET KE SERVER UNTUK PROSES CHECK-IN
        fetch('proses_checkin.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'kode_tiket=' + encodeURIComponent(decodedText)
        })
        // TANGANI RESPONSE DARI SERVER
        .then(response => response.text()) 
        .then(text => {
            try {
                const data = JSON.parse(text);
                alert(data.message); 
                location.reload();   
            } catch (e) {
                console.error("Format JSON Error:", text);
                alert("Terjadi kesalahan format data pada server.");
                location.reload();
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert("Gagal terhubung ke server.");
        });
    }

    // INISIALISASI SCANNER QR CODE
    let html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
    html5QrcodeScanner.render(onScanSuccess);
</script>
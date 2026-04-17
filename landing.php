<?php
require_once 'koneksi.php';

// Ambil semua event (untuk jelajah & JS)
$query_event = mysqli_query($conn, "
    SELECT e.*, MIN(t.harga) as harga_mulai, v.nama_venue 
    FROM event e 
    LEFT JOIN tiket t ON e.id_event = t.id_event 
    LEFT JOIN venue v ON e.id_venue = v.id_venue
    GROUP BY e.id_event 
    ORDER BY e.tanggal ASC
");

// Simpan data event ke array untuk digunakan di JS
$data_event = [];
while ($row = mysqli_fetch_assoc($query_event)) {
    $data_event[] = $row;
}

// Event pilihan (beranda)
$query_pilihan = mysqli_query($conn, "
    SELECT e.*, MIN(t.harga) as harga_mulai, v.nama_venue 
    FROM event e 
    LEFT JOIN tiket t ON e.id_event = t.id_event 
    LEFT JOIN venue v ON e.id_venue = v.id_venue
    GROUP BY e.id_event 
    ORDER BY e.tanggal ASC 
    LIMIT 4
");

// Ambil semua kategori event unik untuk filter
$query_event = mysqli_query($conn, "
    SELECT 
        e.*, 
        MIN(t.harga) as harga_mulai, 
        v.nama_venue,
        GROUP_CONCAT(DISTINCT t.nama_tiket) as kategori_event
    FROM event e 
    LEFT JOIN tiket t ON e.id_event = t.id_event 
    LEFT JOIN venue v ON e.id_venue = v.id_venue
    GROUP BY e.id_event 
    ORDER BY e.tanggal ASC
");
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventKu – Platform Tiket Event Profesional</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap');
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc; /* Warna lebih soft */
        }

        /* Glass Header */
        .glass-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        /* Card Pilihan */
        .event-card {
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .event-card:hover {
            transform: translateY(-10px);
        }

        /* Gradient Text */
        .text-gradient {
            background: linear-gradient(135deg, #1D1145 0%, #0DB5BB 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="text-gray-800 flex flex-col min-h-screen">

    <!-- HEADER -->
    <header class="glass-nav fixed w-full z-30">
        <div class="container mx-auto px-4 lg:px-16 py-4 flex justify-between items-center">

            <!-- LOGO -->
            <button onclick="pindahMenu('beranda')" class="text-2xl lg:text-3xl font-extrabold text-[#1D1145]">
                Event<span class="text-[#E66C8A]">Ku</span>
            </button>

            <!-- MENU DESKTOP -->
            <nav class="hidden md:flex space-x-8 text-base font-semibold">
                <button onclick="pindahMenu('beranda')" id="nav-beranda">Beranda</button>
                <button onclick="pindahMenu('jelajah')" id="nav-jelajah">Jelajah</button>
                <button onclick="pindahMenu('tentang')" id="nav-tentang">Tentang</button>
                <button onclick="pindahMenu('kontak')" id="nav-kontak">Kontak</button>
            </nav>

            <!-- BUTTON -->
            <div class="hidden md:flex items-center space-x-3">
                <a href="login.php">
                    <button class="text-gray-800 font-semibold hover:text-[#E66C8A] transition">
                        Masuk
                    </button>
                </a>
                <a href="regis.php">
                    <button class="cta-gradient bg-[#E66C8A] text-white px-6 py-2.5 rounded-full font-bold shadow-lg transform hover:scale-105 transition duration-300">
                        Daftar
                    </button>
                </a>
            </div>

            <!-- HAMBURGER -->
            <button class="md:hidden text-2xl" onclick="toggleMenu()">
                <i class="bi bi-list"></i>
            </button>
        </div>

        <!-- MOBILE MENU -->
        <div id="mobileMenu" class="hidden md:hidden bg-white px-6 pb-6 pt-2 space-y-3 shadow-lg">

            <!-- NAV MENU -->
            <button onclick="pindahMenu('beranda')" class="block w-full text-left py-2 border-b">Beranda</button>
            <button onclick="pindahMenu('jelajah')" class="block w-full text-left py-2 border-b">Jelajah</button>
            <button onclick="pindahMenu('tentang')" class="block w-full text-left py-2 border-b">Tentang</button>
            <button onclick="pindahMenu('kontak')" class="block w-full text-left py-2">Kontak</button>

            <!-- DIVIDER -->
            <div class="border-t my-3"></div>

            <!-- LOGIN & DAFTAR -->
            <div class="flex flex-col gap-3">
                
                <!-- LOGIN -->
                <a href="login.php">
                    <button class="w-full border border-[#1D1145] text-[#1D1145] py-2 rounded-xl font-semibold hover:bg-[#1D1145] hover:text-white transition">
                        Masuk
                    </button>
                </a>

                <!-- DAFTAR -->
                <a href="regis.php">
                    <button class="w-full bg-gradient-to-r from-[#E66C8A] to-[#CF2E2E] text-white py-2 rounded-xl font-bold shadow-md hover:opacity-90 transition">
                        Daftar
                    </button>
                </a>

            </div>

        </div>

    </header>
    <!-- END HEADER -->

    <!-- MENU HOME -->
    <main id="menu-beranda" class="pt-16 flex-grow">

        <!-- HERO SECTION -->
        <section class="relative pt-24 md:pt-32 pb-16 md:pb-24 overflow-hidden">
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1492684223066-81342ee5ff30" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-[#1D1145]/90"></div>
            </div>

            <div class="container mx-auto px-6 lg:px-16 text-center relative z-10">
                <span class="inline-block py-1 px-3 rounded-full bg-[#E66C8A]/20 text-[#E66C8A] font-semibold text-sm mb-4">
                    #1 Tiket Event Terpercaya
                </span>
                <h1 class="text-3xl md:text-5xl lg:text-7xl font-extrabold text-white mb-4 leading-tight">
                    Amankan Tiketmu, <br/> <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#0DB5BB] to-white">Rasakan Pengalaman Nyata.</span>
                </h1>
                <p class="text-sm md:text-lg text-gray-300 mb-6 max-w-xl mx-auto">
                    Platform pemesanan tiket resmi untuk event terbaik. Cepat, aman, dan tanpa calo.
                </p>
                <div class="flex justify-center gap-4">
                    <button onclick="pindahMenu('jelajah')" class="cta-gradient px-8 py-4 rounded-2xl font-bold text-white text-lg shadow-xl hover:shadow-2xl">
                        Cari Event Sekarang
                    </button>
                </div>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="py-16 container mx-auto px-6 lg:px-16">
            <h2 class="text-3xl md:text-4xl font-extrabold text-center mb-12 text-[#1D1145]">
                Event Pilihan <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#1D1145] to-[#0DB5BB]">Minggu Ini</span>
            </h2>

            <div id="gridBeranda" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                <?php if (mysqli_num_rows($query_pilihan) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($query_pilihan)) : ?>
                        <div class="bg-white rounded-2xl shadow-md hover:shadow-2xl transition duration-300 overflow-hidden border border-gray-100 group">
                            <div class="relative bg-gradient-to-br from-[#1d1145] to-[#2d1b6b] text-white h-48 flex flex-col justify-between p-4 rounded-t-2xl overflow-hidden">
    
                                <!-- Badge -->
                                <div class="absolute top-4 left-4">
                                    <span class="bg-[#0DB5BB] text-white text-xs font-bold px-3 py-1 rounded-full uppercase shadow-lg">
                                        Terbatas
                                    </span>
                                </div>

                                <!-- Decorative Icon -->
                                <div class="absolute right-4 bottom-2 opacity-10">
                                    <i class="bi bi-calendar-event" style="font-size: 5rem;"></i>
                                </div>

                                <!-- Content -->
                                <div class="mt-auto">
                                    <h5 class="fw-bold text-white mb-1 text-truncate">
                                        <?= htmlspecialchars($row['nama_event']); ?>
                                    </h5>
                                    <small class="text-white-50">
                                        <?= date('d M Y', strtotime($row['tanggal'])); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                <h3 class="text-lg font-bold text-[#1D1145] mb-2 line-clamp-1">
                                    <?= htmlspecialchars($row['nama_event']); ?>
                                </h3>
                                
                                <div class="flex items-center text-gray-500 text-sm mb-2">
                                    <i class="bi bi-calendar-event me-2 text-[#0DB5BB]"></i>
                                    <?= date('d M Y', strtotime($row['tanggal'])); ?>
                                </div>
                                
                                <div class="flex items-center text-gray-500 text-sm mb-4">
                                    <i class="bi bi-geo-alt me-2 text-[#0DB5BB]"></i>
                                    <?= htmlspecialchars($row['nama_venue'] ?? 'TBA'); ?>
                                </div>

                                <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-50">
                                    <div>
                                        <p class="text-xs text-gray-400 uppercase font-semibold">Mulai dari</p>
                                        <p class="text-lg font-extrabold text-[#1D1145]">
                                            Rp <?= number_format($row['harga_mulai'] ?? 0, 0, ',', '.'); ?>
                                        </p>
                                    </div>
                                    <a href="#" onclick="beliEvent(<?= $row['id_event']; ?>)">
                                        <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="col-span-full text-center py-10 text-gray-400">
                        <i class="bi bi-calendar-x text-5xl mb-4 d-block"></i>
                        <p>Belum ada event pilihan tersedia.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>
    <!-- MENU END -->

    <!-- MENU JELAJAH -->
    <main id="menu-jelajah" class="pt-24 pb-16 flex-grow hidden">
        <div class="container mx-auto px-6 lg:px-16">
            <h2 class="text-3xl font-extrabold text-[#1D1145] mb-2">Pusat Jelajah Event</h2>
            <p class="text-gray-500 mb-6 text-sm">Temukan event impianmu berdasarkan kata kunci atau kategori.</p>

            <div class="bg-white p-2 rounded-xl shadow-lg max-w-4xl mb-8 border border-gray-100">
                <div class="flex items-center"> 
                    <svg class="w-6 h-6 ml-5 text-[#0DB5BB]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input type="text" id="searchInput" oninput="filterEvent()" placeholder="Cari event berdasarkan nama atau lokasi..." class="flex-grow p-4 text-base border-none focus:ring-0 focus:outline-none placeholder-gray-500 bg-transparent">
                </div>
            </div>

            <div id="eventGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6">
                <?php if (mysqli_num_rows($query_event) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($query_event)) : ?>
                        
                        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition duration-300 overflow-hidden border border-gray-100 group">
                            
                            <!-- HEADER -->
                            <div class="relative bg-gradient-to-br from-[#1d1145] to-[#2d1b6b] text-white h-40 flex flex-col justify-between p-4">
                                
                                <span class="bg-[#0DB5BB] text-white text-xs font-bold px-3 py-1 rounded-full uppercase w-fit">
                                    Event
                                </span>

                                <div class="absolute right-3 bottom-2 opacity-10">
                                    <i class="bi bi-calendar-event" style="font-size: 4rem;"></i>
                                </div>

                                <div>
                                    <h6 class="font-bold text-white line-clamp-2">
                                        <?= htmlspecialchars($row['nama_event']); ?>
                                    </h6>
                                    <small class="text-white/70">
                                        <?= date('d M Y', strtotime($row['tanggal'])); ?>
                                    </small>
                                </div>
                            </div>

                            <!-- BODY -->
                            <div class="p-4">
                                <p class="text-sm text-gray-500 mb-2">
                                    <i class="bi bi-geo-alt"></i> 
                                    <?= htmlspecialchars($row['nama_venue'] ?? 'TBA'); ?>
                                </p>

                                <div class="flex justify-between items-center">
                                    <span class="text-[#e66c8a] font-bold">
                                        Rp <?= number_format($row['harga_mulai'] ?? 0, 0, ',', '.'); ?>
                                    </span>

                                    <a href="detail_event.php?id=<?= $row['id_event']; ?>" 
                                    class="bg-[#1d1145] text-white px-3 py-1 rounded-lg text-sm hover:bg-[#e66c8a] transition">
                                        Detail
                                    </a>
                                </div>
                            </div>

                        </div>

                    <?php endwhile; ?>
                <?php else : ?>
                    <p class="col-span-full text-center text-gray-400">Belum ada event</p>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <!-- MENU END -->

    <!-- MENU TENTANG -->
    <main id="menu-tentang" class="pt-24 pb-16 flex-grow hidden">
        <div class="container mx-auto px-6 lg:px-16">
            <div class="max-w-4xl mx-auto text-center mb-16">
                <h2 class="text-4xl font-extrabold text-[#1D1145] mb-4">Tentang <span class="text-[#E66C8A]">EventKu</span></h2>
                <p class="text-lg text-gray-600 font-light">Kami adalah jembatan utama yang menghubungkan para pecinta hiburan dengan momen-momen terbaik dalam hidup mereka secara aman dan transparan.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div>
                    <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=600&q=80" alt="Tim EventKu" class="rounded-2xl shadow-xl w-full object-cover h-80">
                </div>
                <div class="space-y-6">
                    <div class="border-l-4 border-[#0DB5BB] pl-4">
                        <h3 class="text-xl font-bold text-[#1D1145]">Visi Kami</h3>
                        <p class="text-gray-600 mt-1">Menciptakan ekosistem pertiketan yang bersih dan bebas dari calo fiktif di Indonesia.</p>
                    </div>
                    <div class="border-l-4 border-[#E66C8A] pl-4">
                        <h3 class="text-xl font-bold text-[#1D1145]">Misi Kami</h3>
                        <p class="text-gray-600 mt-1">Menghadirkan teknologi pemindaian QR-Code berlapis yang menjamin keaslian tiket hingga ke tangan pembeli.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- MENU END -->

    <!-- MENU KONTAK -->
    <main id="menu-kontak" class="pt-24 pb-16 flex-grow hidden">
        <div class="container mx-auto px-6 lg:px-16">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-extrabold text-[#1D1145] mb-2">Hubungi Kami</h2>
                <p class="text-gray-500">Ada kendala atau pertanyaan? Kami siap membantu Anda 24/7.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <div class="bg-white p-6 rounded-xl shadow-md text-center border-t-4 border-[#0DB5BB]">
                    <div class="text-3xl mb-3">📍</div>
                    <h3 class="font-bold text-gray-900 mb-1">Kantor Pusat</h3>
                    <p class="text-sm text-gray-500">Jl. A Yani No 135A Magelang Utara, Kota Magelang</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md text-center border-t-4 border-[#E66C8A]">
                    <div class="text-3xl mb-3">✉️</div>
                    <h3 class="font-bold text-gray-900 mb-1">E-mail</h3>
                    <p class="text-sm text-gray-500">support@eventku.id</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-md text-center border-t-4 border-[#1D1145]">
                    <div class="text-3xl mb-3">📞</div>
                    <h3 class="font-bold text-gray-900 mb-1">WhatsApp</h3>
                    <p class="text-sm text-gray-500">+62 812 3456 7890</p>
                </div>
            </div>
        </div>
    </main>
    <!-- MENU END -->

    <!-- FOOTER -->
    <footer class="bg-[#1D1145] text-white py-8 md:py-10 mt-auto text-center">
        <div class="container mx-auto px-6 lg:px-16 text-center">
            <a href="#" class="text-3xl font-extrabold text-white tracking-tight">
                Event<span class="text-[#E66C8A]">Ku</span>
            </a>
            <p class="text-sm text-gray-400 mt-2">Tiket resmi, pengalaman tanpa batas. Partner event tepercaya Anda.</p>
            <div class="text-center border-t border-gray-700 pt-6 mt-6">
                <p class="text-xs text-gray-500">© 2026 EventKu. Hak Cipta Dilindungi Undang-Undang.</p>
            </div>
        </div>
    </footer>
    <!-- FOOTER END -->

    <script>
        // DATA DARI PHP KE JS
        let isLogin = <?= isset($_SESSION['user']) ? 'true' : 'false'; ?>;
        // Simpan data event ke array untuk digunakan di JS
        let databaseEvent = <?= json_encode($data_event); ?>;
        let kategoriAktif = "Semua";

        // FORMAT DATA
        databaseEvent = databaseEvent.map(e => ({
            id: e.id_event,
            nama: e.nama_event,
            lokasi: e.nama_venue ?? "TBA",
            tanggal: `${new Date(e.tanggal).getDate()} ${['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][new Date(e.tanggal).getMonth()]} ${new Date(e.tanggal).getFullYear()}`,
            jam: "19:00",
            harga: e.harga_mulai ?? 0,
            kategori: e.kategori_event ? e.kategori_event.split(",") : ["Umum"],
            img: "https://source.unsplash.com/400x300/?concert,festival,party"        
        }));
        console.log(databaseEvent);

        // TEMPLATE CARD
        function buatTemplateCard(event) {
            const harga = new Intl.NumberFormat('id-ID').format(event.harga);
            return `
            <div class="bg-white rounded-2xl shadow-md hover:shadow-xl transition duration-300 overflow-hidden border border-gray-100 group">
                <div class="relative bg-gradient-to-br from-[#1d1145] to-[#2d1b6b] text-white h-40 flex flex-col justify-between p-4">
                    <span class="bg-[#0DB5BB] text-white text-xs font-bold px-3 py-1 rounded-full uppercase w-fit">
                        Event
                    </span>
                    <div class="absolute right-3 bottom-2 opacity-10">
                        <i class="bi bi-calendar-event" style="font-size: 4rem;"></i>
                    </div>
                    <div>
                        <h6 class="font-bold text-white line-clamp-2">${event.nama}</h6>
                        <small class="text-white/70">${event.tanggal}</small>
                    </div>
                </div>

                <div class="p-4">
                    <p class="text-sm text-gray-500 mb-1">
                        <i class="bi bi-calendar-event me-2 text-[#0DB5BB]"></i> ${event.tanggal}
                    </p>
                    <p class="text-sm text-gray-500 mb-4">
                        <i class="bi bi-geo-alt me-2 text-[#0DB5BB]"></i> ${event.lokasi}
                    </p>

                    <div class="flex justify-between items-center pt-4 border-t border-gray-50">
                        <span class="text-[#e66c8a] font-bold">
                            Rp ${harga}
                        </span>
                        <button onclick="beliEvent(${event.id})" 
                            class="bg-[#1d1145] text-white px-4 py-1.5 rounded-lg text-sm hover:bg-[#e66c8a] transition shadow-md">
                            Beli Tiket
                        </button>
                    </div>
                </div>
            </div>`;
        }

        // FILTER EVENT (FIX ERROR NULL)
        function filterEvent() {
            const grid = document.getElementById('eventGrid');
            if (!grid) return;

            const input = document.getElementById('searchInput');
            const keyword = input ? input.value.toLowerCase() : "";

            let hasil = databaseEvent.filter(e =>
                (e.nama.toLowerCase().includes(keyword) || e.lokasi.toLowerCase().includes(keyword)) &&
                (kategoriAktif === "Semua" || e.kategori.includes(kategoriAktif))
            );

            grid.innerHTML = hasil.length
                ? hasil.map(e => buatTemplateCard(e)).join('')
                : `<p class="text-center col-span-full text-gray-400">Tidak ada event</p>`;
        }

        // BELI EVENT
        function beliEvent(id){
            if(!isLogin){
                alert("Silahkan masuk atau daftar terlebih dahulu untuk membeli tiket!");
                window.location.href = "login.php";
                return;
            }

            window.location.href = "detail_event.php?id=" + id;
        }

        // NAVIGATION + ACTIVE TAB
        function pindahMenu(menu) {
            const menus = ['beranda','jelajah','tentang','kontak'];

            menus.forEach(m => {
                const el = document.getElementById('menu-' + m);
                const nav = document.getElementById('nav-' + m);

                if(el) el.classList.add('hidden');

                if(nav){
                    nav.classList.remove('text-[#0DB5BB]', 'border-[#0DB5BB]');
                    nav.classList.add('text-gray-700', 'border-transparent');
                }
            });

            // tampilkan menu aktif
            document.getElementById('menu-' + menu).classList.remove('hidden');

            // aktifkan navbar
            const navAktif = document.getElementById('nav-' + menu);
            if(navAktif){
                navAktif.classList.remove('text-gray-700','border-transparent');
                navAktif.classList.add('text-[#0DB5BB]','border-[#0DB5BB]');
            }

            window.scrollTo(0,0);

            if(menu === 'jelajah') filterEvent();
        }

        // INIT (AMAN)
        document.addEventListener("DOMContentLoaded", () => {
            // default render jika langsung ke jelajah
            filterEvent();
        });

        // TOGGLE MOBILE MENU
        function toggleMenu(){
            document.getElementById('mobileMenu').classList.toggle('hidden');
        }
    </script>

</body>
</html>
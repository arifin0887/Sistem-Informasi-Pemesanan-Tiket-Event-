<?php
require_once 'koneksi.php';

// AMBIL SEMUA EVENT (UNTUK JS)
$query_event = mysqli_query($conn, "
    SELECT 
        e.*, 
        MIN(t.harga) as harga_mulai, 
        v.nama_venue,
        GROUP_CONCAT(DISTINCT t.nama_tiket) as kategori_event
    FROM event e 
    LEFT JOIN tiket t ON e.id_event = t.id_event 
    LEFT JOIN venue v ON e.id_venue = v.id_venue
    WHERE e.tanggal >= NOW()
    GROUP BY e.id_event 
    ORDER BY e.tanggal ASC
");

// SIMPAN KE ARRAY UNTUK JS
$data_event = [];
while ($row = mysqli_fetch_assoc($query_event)) {
    $data_event[] = $row;
}

// EVENT PILIHAN (BERANDA)
$query_pilihan = mysqli_query($conn, "
    SELECT 
        e.*, 
        MIN(t.harga) as harga_mulai, 
        v.nama_venue 
    FROM event e 
    LEFT JOIN tiket t ON e.id_event = t.id_event 
    LEFT JOIN venue v ON e.id_venue = v.id_venue
    WHERE e.tanggal >= NOW() 
    GROUP BY e.id_event 
    ORDER BY e.tanggal ASC 
    LIMIT 4
");

// AMBIL EVENT UNTUK GRID JELAJAH
$query_jelajah = mysqli_query($conn, "
    SELECT 
        e.*, 
        MIN(t.harga) as harga_mulai, 
        v.nama_venue,
        GROUP_CONCAT(DISTINCT t.nama_tiket) as kategori_event
    FROM event e 
    LEFT JOIN tiket t ON e.id_event = t.id_event 
    LEFT JOIN venue v ON e.id_venue = v.id_venue
    WHERE e.tanggal >= NOW() 
    GROUP BY e.id_event 
    ORDER BY e.tanggal ASC
");
?>

<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventKu - Platform Tiket Event Profesional</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap');
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc; 
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        .event-card {
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .event-card:hover {
            transform: translateY(-10px);
        }

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

            <!-- HEADER -->
            <div class="mb-10">
                <h2 class="text-4xl font-extrabold text-[#1D1145] mb-2">
                    Temukan Event Terbaik
                </h2>
                <p class="text-gray-500 text-sm max-w-2xl">
                    Jelajahi berbagai event menarik mulai dari konser, seminar, workshop hingga festival seru di sekitarmu.
                </p>
            </div>

            <!-- SEARCH + FILTER -->
            <div class="bg-white p-3 rounded-2xl shadow-lg max-w-5xl mb-6 border flex flex-col md:flex-row gap-3">
                
                <!-- SEARCH -->
                <div class="flex items-center flex-grow">
                    <i class="bi bi-search text-[#0DB5BB] text-xl ml-3"></i>
                    <input type="text" id="searchInput" oninput="filterEvent()" 
                        placeholder="Cari event, kota, atau venue..." 
                        class="flex-grow p-3 text-sm border-none focus:ring-0 focus:outline-none bg-transparent">
                </div>

            </div>

            <!-- GRID EVENT -->
            <div id="eventGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php if (mysqli_num_rows($query_event) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($query_event)) : ?>
                        
                        <div class="bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-2 transition duration-300 overflow-hidden border group relative">

                            <!-- FAVORITE -->
                            <button class="absolute top-3 right-3 bg-white p-2 rounded-full shadow hover:bg-pink-100">
                                <i class="bi bi-heart text-gray-400 hover:text-pink-500"></i>
                            </button>

                            <!-- HEADER -->
                            <div class="relative bg-gradient-to-br from-[#1d1145] to-[#2d1b6b] text-white h-44 flex flex-col justify-between p-4">
                                
                                <!-- BADGE -->
                                <div class="flex justify-between items-start">
                                    <span class="bg-[#0DB5BB] text-white text-xs font-bold px-3 py-1 rounded-full">
                                        Event
                                    </span>

                                    <span class="bg-white/20 text-white text-xs px-2 py-1 rounded">
                                        <?= date('d M', strtotime($row['tanggal'])); ?>
                                    </span>
                                </div>

                                <!-- TITLE -->
                                <div>
                                    <h6 class="font-bold text-sm line-clamp-2">
                                        <?= htmlspecialchars($row['nama_event']); ?>
                                    </h6>
                                    <small class="text-white/70 text-xs">
                                        <?= date('Y', strtotime($row['tanggal'])); ?>
                                    </small>
                                </div>
                            </div>

                            <!-- BODY -->
                            <div class="p-4">

                                <!-- LOCATION -->
                                <p class="text-xs text-gray-500 mb-2 flex items-center gap-1">
                                    <i class="bi bi-geo-alt"></i> 
                                    <?= htmlspecialchars($row['nama_venue'] ?? 'TBA'); ?>
                                </p>

                                <!-- INFO -->
                                <div class="flex justify-between items-center mt-3">

                                    <div>
                                        <span class="text-xs text-gray-400 block">Mulai dari</span>
                                        <span class="text-[#e66c8a] font-bold text-sm">
                                            Rp <?= number_format($row['harga_mulai'] ?? 0, 0, ',', '.'); ?>
                                        </span>
                                    </div>

                                    <a href="detail_event.php?id=<?= $row['id_event']; ?>" 
                                    class="bg-[#1d1145] text-white px-3 py-2 rounded-lg text-xs hover:bg-[#e66c8a] transition">
                                        Detail
                                    </a>
                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>
                <?php else : ?>
                    <div class="col-span-full text-center py-16">
                        <i class="bi bi-calendar-x text-5xl text-gray-300"></i>
                        <h4 class="text-gray-400 mt-3">Belum ada event tersedia</h4>
                        <p class="text-sm text-gray-400">Coba cek kembali nanti ya 😉</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </main>
    <!-- MENU END -->

    <!-- MENU TENTANG -->
    <main id="menu-tentang" class="pt-24 pb-16 flex-grow hidden">
        <div class="container mx-auto px-6 lg:px-16">

            <!-- HEADER -->
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-4xl font-extrabold text-[#1D1145] mb-4">
                    Tentang <span class="text-[#E66C8A]">EventKu</span>
                </h2>
                <p class="text-gray-600 text-lg leading-relaxed">
                    EventKu adalah platform digital yang dirancang untuk memudahkan masyarakat dalam menemukan, 
                    memesan, dan mengelola tiket berbagai acara secara cepat, aman, dan terpercaya.
                </p>
            </div>

            <!-- TENTANG EVENTKU -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center mb-20">

                <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=600&q=80" 
                    class="rounded-2xl shadow-lg w-full h-80 object-cover">

                <div class="space-y-5 text-gray-600 leading-relaxed">
                    <p>
                        Di era digital saat ini, kebutuhan akan sistem pemesanan tiket yang transparan dan efisien semakin meningkat. 
                        EventKu hadir sebagai solusi modern yang menghubungkan penyelenggara event dengan para pengunjung secara langsung, 
                        tanpa perantara yang merugikan.
                    </p>

                    <p>
                        Kami menyediakan berbagai pilihan event mulai dari konser musik, seminar, workshop, hingga acara komunitas, 
                        dengan sistem pembelian yang mudah dan terintegrasi.
                    </p>

                    <p>
                        Dengan teknologi berbasis QR Code dan sistem validasi yang aman, setiap tiket yang dibeli melalui EventKu 
                        dijamin keasliannya, sehingga pengguna dapat menikmati acara tanpa rasa khawatir.
                    </p>
                </div>

            </div>

            <!-- KEUNGGULAN -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-20">

                <div class="bg-white p-6 rounded-xl shadow text-center">
                    <i class="bi bi-shield-check text-3xl text-[#0DB5BB]"></i>
                    <h4 class="font-bold mt-3 text-[#1D1145]">Aman & Terpercaya</h4>
                    <p class="text-sm text-gray-500 mt-2">
                        Sistem validasi tiket digital memastikan setiap transaksi aman dan bebas dari penipuan.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow text-center">
                    <i class="bi bi-lightning-charge text-3xl text-[#E66C8A]"></i>
                    <h4 class="font-bold mt-3 text-[#1D1145]">Cepat & Mudah</h4>
                    <p class="text-sm text-gray-500 mt-2">
                        Proses pembelian tiket hanya dalam beberapa langkah tanpa ribet.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow text-center">
                    <i class="bi bi-people text-3xl text-[#1D1145]"></i>
                    <h4 class="font-bold mt-3 text-[#1D1145]">Terhubung Langsung</h4>
                    <p class="text-sm text-gray-500 mt-2">
                        Menghubungkan penyelenggara dan pengunjung tanpa perantara.
                    </p>
                </div>

            </div>

            <!-- TENTANG PERUSAHAAN -->
            <div class="text-center max-w-4xl mx-auto">
                <h3 class="text-2xl font-bold text-[#1D1145] mb-4">
                    Tentang PT Edu Tech Development
                </h3>

                <p class="text-gray-600 leading-relaxed mb-4">
                    PT Edu Tech Development adalah perusahaan teknologi yang berfokus pada pengembangan solusi digital inovatif 
                    di berbagai sektor, termasuk pendidikan, event, dan sistem manajemen berbasis web.
                </p>

                <p class="text-gray-600 leading-relaxed mb-4">
                    Dengan tim yang berpengalaman di bidang pengembangan perangkat lunak dan desain sistem, 
                    kami berkomitmen untuk menciptakan produk digital yang tidak hanya fungsional, tetapi juga memberikan 
                    pengalaman pengguna yang optimal.
                </p>

                <p class="text-gray-600 leading-relaxed">
                    EventKu merupakan salah satu produk unggulan kami yang dikembangkan untuk mendukung ekosistem event di Indonesia, 
                    dengan tujuan menghadirkan solusi ticketing yang modern, efisien, dan terpercaya bagi semua kalangan.
                </p>
            </div>

            <br>
            <!-- VISI MISI -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-20">
                
                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-[#0DB5BB]">
                    <h3 class="font-bold text-[#1D1145] mb-2">Visi</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Menjadi platform ticketing digital terdepan di Indonesia yang menghadirkan pengalaman terbaik 
                        dalam menemukan dan menikmati berbagai event.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl shadow border-l-4 border-[#E66C8A]">
                    <h3 class="font-bold text-[#1D1145] mb-2">Misi</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">
                        Memberikan layanan ticketing yang aman, transparan, dan inovatif dengan memanfaatkan teknologi 
                        untuk mendukung pertumbuhan industri event di Indonesia.
                    </p>
                </div>

            </div>

        </div>
    </main>
    <!-- MENU END -->

    <!-- MENU KONTAK -->
    <main id="menu-kontak" class="pt-24 pb-16 flex-grow hidden">
        <div class="container mx-auto px-6 lg:px-16">

            <!-- HEADER -->
            <div class="text-center mb-12">
                <h2 class="text-4xl font-extrabold text-[#1D1145] mb-2">
                    Hubungi Kami 📩
                </h2>
                <p class="text-gray-500 text-sm max-w-2xl mx-auto">
                    Kami siap membantu Anda terkait pemesanan tiket, informasi event, maupun kerja sama. 
                    Jangan ragu untuk menghubungi kami melalui berbagai kanal berikut.
                </p>
            </div>

            <!-- CONTACT INFO -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl mx-auto mb-12">

                <!-- Alamat -->
                <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center border-t-4 border-[#0DB5BB]">
                    <i class="bi bi-geo-alt text-3xl text-[#0DB5BB]"></i>
                    <h4 class="font-bold mt-3">Alamat</h4>
                    <p class="text-sm text-gray-500">
                        Jl. A Yani No 135A <br>
                        Magelang Utara, Indonesia
                    </p>
                </div>

                <!-- Email -->
                <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center border-t-4 border-[#E66C8A]">
                    <i class="bi bi-envelope text-3xl text-[#E66C8A]"></i>
                    <h4 class="font-bold mt-3">Email</h4>
                    <p class="text-sm text-gray-500">support@eventku.id</p>
                </div>

                <!-- WhatsApp -->
                <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition text-center border-t-4 border-[#1D1145]">
                    <i class="bi bi-whatsapp text-3xl text-[#1D1145]"></i>
                    <h4 class="font-bold mt-3">WhatsApp</h4>
                    <p class="text-sm text-gray-500">+62 812 3456 7890</p>
                </div>

            </div>

            <!-- GOOGLE MAPS -->
            <div class="max-w-6xl mx-auto">
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden border">

                    <!-- TITLE -->
                    <div class="p-4 border-b">
                        <h4 class="font-bold text-[#1D1145]">
                            📍 Lokasi Kantor Kami
                        </h4>
                        <p class="text-sm text-gray-500">
                            Kunjungi kantor kami secara langsung melalui peta di bawah ini.
                        </p>
                    </div>

                    <!-- MAP -->
                    <div class="w-full h-[400px]">
                        <iframe 
                            src="https://www.google.com/maps?q=SMK+Negeri+2+Magelang&output=embed"
                            width="100%" 
                            height="100%" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    </div>

                </div>
            </div>

        </div>
    </main>
    <!-- MENU END -->

    <!-- FOOTER -->
    <footer class="bg-gradient-to-b from-[#1D1145] to-[#0A0A1E] text-white relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_20%_80%,#0DB5BB20_0%,transparent_50%)]"></div>
            <div class="absolute bottom-0 right-0 w-full h-full bg-[radial-gradient(circle_at_80%_20%,#E66C8A20_0%,transparent_50%)]"></div>
        </div>

        <div class="container mx-auto px-6 lg:px-16 py-16 relative z-10">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12 mb-12">
                
                <!-- Logo & Deskripsi -->
                <div class="lg:col-span-1">
                    <a href="#" class="text-3xl md:text-4xl font-black bg-gradient-to-r from-white via-[#0DB5BB] to-[#E66C8A] bg-clip-text inline-block mb-6">
                        Event<span class="text-[#E66C8A]">Ku</span>
                    </a>
                    <p class="text-gray-300 leading-relaxed mb-6">Platform tiket event #1 Indonesia. Aman, resmi, tanpa calo. Rasakan pengalaman event terbaik dengan kami.</p>
                    <div class="flex space-x-3">
                        <a href="#" class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center hover:bg-white/20 transition-all duration-300 group">
                            <i class="bi bi-instagram text-xl group-hover:scale-110"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center hover:bg-white/20 transition-all duration-300 group">
                            <i class="bi bi-twitter-x text-xl group-hover:scale-110"></i>
                        </a>
                        <a href="#" class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center hover:bg-white/20 transition-all duration-300 group">
                            <i class="bi bi-youtube text-xl group-hover:scale-110"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-bold mb-6 text-white">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-300 hover:text-[#0DB5BB] transition flex items-center group"><i class="bi bi-chevron-right me-2 opacity-0 group-hover:opacity-100 transition"></i>Tentang Kami</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-[#0DB5BB] transition flex items-center group"><i class="bi bi-chevron-right me-2 opacity-0 group-hover:opacity-100 transition"></i>Cara Beli Tiket</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-[#0DB5BB] transition flex items-center group"><i class="bi bi-chevron-right me-2 opacity-0 group-hover:opacity-100 transition"></i>FAQ</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-[#0DB5BB] transition flex items-center group"><i class="bi bi-chevron-right me-2 opacity-0 group-hover:opacity-100 transition"></i>Hubungi Kami</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div>
                    <h4 class="text-lg font-bold mb-6 text-white">Perusahaan</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-300 hover:text-[#E66C8A] transition flex items-center group"><i class="bi bi-chevron-right me-2 opacity-0 group-hover:opacity-100 transition"></i>Syarat & Ketentuan</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-[#E66C8A] transition flex items-center group"><i class="bi bi-chevron-right me-2 opacity-0 group-hover:opacity-100 transition"></i>Kebijakan Privasi</a></li>
                        <li><a href="#" class="text-gray-300 hover:text-[#E66C8A] transition flex items-center group"><i class="bi bi-chevron-right me-2 opacity-0 group-hover:opacity-100 transition"></i>Karir</a></li>
                        <li><p class="text-gray-400 text-xs">PT Edu Tech Development</p></li>
                    </ul>
                </div>

                <!-- Contact -->
                <div>
                    <h4 class="text-lg font-bold mb-6 text-white">Kontak</h4>
                    <div class="space-y-4">
                        <div class="flex items-start space-x-3">
                            <div class="w-10 h-10 bg-[#0DB5BB]/20 rounded-xl flex items-center justify-center mt-1 flex-shrink-0">
                                <i class="bi bi-geo-alt text-[#0DB5BB]"></i>
                            </div>
                            <div>
                                <p class="text-gray-300 font-medium">Jl. A Yani No 135A</p>
                                <p class="text-sm text-gray-400">Magelang Utara, Kota Magelang</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-[#E66C8A]/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="bi bi-envelope text-[#E66C8A]"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-300">support@eventku.id</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                <i class="bi bi-whatsapp text-[#25D366]"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-300">+62 812 3456 7890</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-white/10 pt-8 mt-12">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <p class="text-xs text-gray-400 text-center md:text-left">
                        © 2026 EventKu. Dikembangkan oleh <span class="font-semibold text-[#0DB5BB]">PT Edu Tech Development</span>. Hak Cipta Dilindungi.
                    </p>
                    <div class="flex justify-center md:justify-end space-x-4">
                        <a href="#" class="text-gray-400 hover:text-[#E66C8A] transition"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-gray-400 hover:text-[#1DA1F2] transition"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="text-gray-400 hover:text-[#0DB5BB] transition"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
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

        // FILTER EVENT
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

            document.getElementById('menu-' + menu).classList.remove('hidden');

            const navAktif = document.getElementById('nav-' + menu);
            if(navAktif){
                navAktif.classList.remove('text-gray-700','border-transparent');
                navAktif.classList.add('text-[#0DB5BB]','border-[#0DB5BB]');
            }

            window.scrollTo(0,0);

            if(menu === 'jelajah') filterEvent();
        }

        document.addEventListener("DOMContentLoaded", () => {
            filterEvent();
        });

        // TOGGLE MOBILE MENU
        function toggleMenu(){
            document.getElementById('mobileMenu').classList.toggle('hidden');
        }
    </script>

</body>
</html>
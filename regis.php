<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru | EventKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Konsistensi Font Poppins */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f4f8; /* Background sama dengan landing page */
        }
        /* Custom Class untuk Gradien pada Button */
        .cta-gradient {
            background-image: linear-gradient(to right, #E66C8A 0%, #CF2E2E 100%);
            transition: all 0.3s ease;
        }
        .cta-gradient:hover {
            background-image: linear-gradient(to right, #CF2E2E 0%, #E66C8A 100%);
            box-shadow: 0 10px 15px -3px rgba(230, 108, 138, 0.5);
        }
        /* Gradien untuk Judul Teks */
        .text-gradient {
            background-image: linear-gradient(to right, #1D1145, #0DB5BB);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100 px-4 py-8">

    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-xl overflow-hidden flex flex-col md:flex-row border border-gray-100">
        
        <!-- SIDE IMAGE -->
        <div class="hidden md:flex md:w-1/2 bg-cover bg-center relative items-center"
             style="background-image: url('https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=800&q=80');">

            <div class="absolute inset-0 bg-gradient-to-t from-[#1D1145]/90 via-[#1D1145]/70 to-[#1D1145]/40"></div>

            <div class="relative z-10 p-10 text-white">
                <span class="text-[#E66C8A] text-xs font-bold uppercase tracking-widest">
                    Bergabunglah Sekarang
                </span>

                <h1 class="text-3xl font-extrabold mt-3 mb-4 leading-tight">
                    Mulai Pengalaman Event Serumu!
                </h1>

                <p class="text-gray-300 text-sm leading-relaxed max-w-sm">
                    Dapatkan akses instan ke ribuan tiket konser, seminar, dan festival pilihan tanpa antre.
                </p>

                <div class="mt-6 flex flex-wrap gap-2 text-xs text-gray-300">
                    <span class="bg-white/10 px-3 py-1 rounded-full">🛡️ Tiket Resmi</span>
                    <span class="bg-white/10 px-3 py-1 rounded-full">⚡ Transaksi Cepat</span>
                </div>
            </div>
        </div>

        <!-- FORM -->
        <div class="w-full md:w-1/2 flex items-center justify-center px-6 py-10">
            <div class="w-full max-w-md">

                <!-- HEADER -->
                <div class="text-center md:text-left mb-6">
                    <a href="/" class="text-2xl font-extrabold text-[#1D1145]">
                        Event<span class="text-[#E66C8A]">Ku</span>
                    </a>

                    <h2 class="text-xl font-bold mt-3 text-gray-900">
                        Buat Akun Baru
                    </h2>

                    <p class="text-gray-500 text-sm">
                        Daftar untuk mulai membeli tiket event favoritmu
                    </p>
                </div>

                <!-- FORM -->
                <form action="proses_regis.php" method="POST" class="space-y-4">

                    <!-- NAMA -->
                    <div>
                        <label class="text-xs font-semibold text-gray-600 uppercase">Nama Lengkap</label>
                        <div class="relative mt-1">
                            <span class="absolute left-3 top-3 text-gray-400">
                                👤
                            </span>
                            <input type="text" name="full_name" required
                                class="w-full pl-10 pr-3 py-2.5 text-sm border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#E66C8A]/20 focus:border-[#E66C8A] outline-none"
                                placeholder="Masukkan nama lengkap">
                        </div>
                    </div>

                    <!-- EMAIL -->
                    <div>
                        <label class="text-xs font-semibold text-gray-600 uppercase">Email</label>
                        <div class="relative mt-1">
                            <span class="absolute left-3 top-3 text-gray-400">
                                ✉️
                            </span>
                            <input type="email" name="email" required
                                class="w-full pl-10 pr-3 py-2.5 text-sm border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#E66C8A]/20 focus:border-[#E66C8A] outline-none"
                                placeholder="contoh@mail.com">
                        </div>
                    </div>

                    <!-- PASSWORD -->
                    <div>
                        <label class="text-xs font-semibold text-gray-600 uppercase">Password</label>
                        <div class="relative mt-1">
                            <span class="absolute left-3 top-3 text-gray-400">
                                🔒
                            </span>
                            <input type="password" name="password" required
                                class="w-full pl-10 pr-3 py-2.5 text-sm border rounded-lg bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#E66C8A]/20 focus:border-[#E66C8A] outline-none"
                                placeholder="Minimal 8 karakter">
                        </div>
                    </div>

                    <!-- TERMS -->
                    <div class="flex items-start text-xs text-gray-600">
                        <input type="checkbox" required class="mt-1 mr-2">
                        <span>
                            Saya setuju dengan 
                            <a href="#" class="text-[#1D1145] font-semibold hover:text-[#E66C8A]">Syarat & Ketentuan</a> 
                            dan 
                            <a href="#" class="text-[#1D1145] font-semibold hover:text-[#E66C8A]">Privasi</a>
                        </span>
                    </div>

                    <!-- BUTTON -->
                    <button type="submit"
                        class="w-full bg-gradient-to-r from-[#E66C8A] to-[#CF2E2E] text-white py-2.5 rounded-full text-sm font-bold shadow-md hover:scale-[1.02] transition">
                        Daftar Sekarang
                    </button>
                </form>

                <!-- FOOTER -->
                <p class="text-center text-xs text-gray-500 mt-6">
                    Sudah punya akun?
                    <a href="login.php" class="text-[#E66C8A] font-semibold hover:text-[#CF2E2E]">
                        Masuk
                    </a>
                </p>

            </div>
        </div>

    </div>

</body>
</html>
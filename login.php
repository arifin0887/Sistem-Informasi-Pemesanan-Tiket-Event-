<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Akun Anda | EventKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #1D1145;
            --secondary: #0DB5BB;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, rgba(13, 181, 187, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(29, 17, 69, 0.05) 0px, transparent 50%);
        }
        .cta-gradient {
            background-image: linear-gradient(135deg, var(--primary) 0%, #2a1a5e 100%);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .cta-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -5px rgba(29, 17, 69, 0.3);
            filter: brightness(1.2);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">

    <div class="w-full max-w-4xl glass-card rounded-[32px] shadow-2xl overflow-hidden flex flex-col lg:flex-row">
        
        <div class="w-full lg:w-1/2 p-6 lg:p-12 flex flex-col justify-center">
            
            <div class="mb-10">
                <a href="/" class="text-3xl font-extrabold text-[#1D1145] tracking-tight">
                    Event<span class="text-[#0DB5BB]">Ku</span>
                </a>
                <h2 class="text-2xl font-bold mt-6 text-gray-900">Selamat Datang Kembali</h2>
                <p class="text-gray-500 text-sm mt-2">Masuk untuk mengelola event dan tiket Anda.</p>
            </div>

            <form action="proses_login.php" method="POST" class="space-y-6">

                <div>
                    <label for="email" class="block text-xs font-bold text-[#1D1145] mb-2 uppercase tracking-widest">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"></path></svg>
                        </span>
                        <input type="email" id="email" name="email" required 
                               class="w-full pl-12 pr-4 py-3.5 bg-white/50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-[#0DB5BB]/10 focus:border-[#0DB5BB] transition-all duration-300 text-sm outline-none"
                               placeholder="nama@email.com">
                    </div>
                </div>

                <div>
                    <div class="flex justify-between mb-2">
                        <label for="password" class="block text-xs font-bold text-[#1D1145] uppercase tracking-widest">Password</label>
                    </div>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </span>
                        <input type="password" id="password" name="password" required 
                               class="w-full pl-12 pr-4 py-3.5 bg-white/50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-[#0DB5BB]/10 focus:border-[#0DB5BB] transition-all duration-300 text-sm outline-none"
                               placeholder="••••••••">
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-[#0DB5BB] focus:ring-[#0DB5BB]">
                        <span class="ml-2 text-xs font-medium text-gray-600">Ingat saya</span>
                    </label>
                    <a href="#" class="text-xs font-bold text-[#0DB5BB] hover:underline">Lupa Password?</a>
                </div>

                <button type="submit" name="login" class="w-full cta-gradient text-white py-4 rounded-2xl font-bold text-sm shadow-xl transform transition duration-300 uppercase tracking-widest">
                    Masuk Sekarang
                </button>
            </form>

            <p class="mt-10 text-center text-gray-500 text-xs">
                Belum punya akun? 
                <a href="regis.php" class="text-[#0DB5BB] font-bold hover:text-[#1D1145] transition decoration-2 underline-offset-4">Buat akun baru</a>
            </p>
        </div>

        <div class="hidden lg:flex lg:w-1/2 bg-cover bg-center relative items-center" 
             style="background-image: url('https://images.unsplash.com/photo-1492684223066-81342ee5ff30?auto=format&fit=crop&w=1200&q=80');">
            <div class="absolute inset-0 bg-gradient-to-br from-[#1D1145]/95 to-[#1D1145]/60"></div>
            
            <div class="relative z-10 p-12 lg:p-16 text-white">
                <div class="w-16 h-1 bg-[#0DB5BB] mb-8 rounded-full"></div>
                <h1 class="text-4xl font-extrabold mb-6 leading-tight">Kelola Event Lebih Profesional.</h1>
                <p class="text-gray-300 text-lg font-light leading-relaxed mb-10">
                    Satu platform untuk semua kebutuhan ticketing, monitoring, dan validasi peserta secara real-time.
                </p>
                
                <div class="space-y-4">
                    <div class="flex items-center space-x-3 bg-white/5 p-4 rounded-2xl border border-white/10 backdrop-blur-md">
                        <span class="text-xl">🚀</span>
                        <span class="text-sm font-medium">Dashboard Monitor Real-time</span>
                    </div>
                    <div class="flex items-center space-x-3 bg-white/5 p-4 rounded-2xl border border-white/10 backdrop-blur-md">
                        <span class="text-xl">🛡️</span>
                        <span class="text-sm font-medium">Sistem Keamanan Data Terenkripsi</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
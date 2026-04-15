<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk ke Akun Anda | EventKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Konsistensi Font Poppins */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f4f8; 
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
    </style>
</head>
<body class="flex items-center justify-center min-h-screen text-gray-800 p-4">

    <div class="w-full max-w-5xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row border border-gray-100">
        
        <div class="w-full md:w-1/2 p-8 md:p-12 lg:p-14 flex flex-col justify-center">
            
            <div class="text-center md:text-left mb-8">
                <a href="/" class="text-3xl font-extrabold text-[#1D1145] tracking-tight">
                    Event<span class="text-[#E66C8A]">Ku</span>
                </a>
                <h2 class="text-2xl font-bold mt-4 text-gray-900">Selamat Datang Kembali</h2>
                <p class="text-gray-500 text-sm mt-1">Masuk untuk melanjutkan pembelian tiket Anda.</p>
            </div>

            <form action="proses_login.php" method="POST" class="space-y-5">

                <div>
                    <label for="email" class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v12a2 2 0 002 2z"></path></svg>
                        </span>
                        <input type="email" id="email" name="email" required 
                               class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#E66C8A]/20 focus:border-[#E66C8A] focus:bg-white transition duration-200 text-sm outline-none"
                               placeholder="Masukkan email Anda">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-gray-600 mb-1.5 uppercase tracking-wide">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </span>
                        <input type="password" id="password" name="password" required 
                               class="w-full pl-11 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#E66C8A]/20 focus:border-[#E66C8A] focus:bg-white transition duration-200 text-sm outline-none"
                               placeholder="Masukkan kata sandi Anda">
                    </div>
                </div>
                
                <div class="flex items-center justify-between pt-1">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox" 
                               class="h-4 w-4 text-[#E66C8A] border-gray-300 rounded focus:ring-[#E66C8A]">
                        <label for="remember_me" class="ml-2 block text-xs text-gray-600 font-medium">
                            Ingat Saya
                        </label>
                    </div>
                    
                    <a href="/forgot-password" class="text-xs font-bold text-[#0DB5BB] hover:text-[#1D1145] transition">
                        Lupa Password?
                    </a>
                </div>

                <button type="submit" name="login" class="w-full cta-gradient text-white px-6 py-3.5 rounded-full font-bold text-sm shadow-lg transform hover:scale-[1.01] transition duration-300 uppercase tracking-wider mt-2">
                    Masuk ke Akun
                </button>
            </form>

            <p class="mt-8 text-center text-gray-500 text-xs">
                Belum punya akun? 
                <a href="regis.php" class="text-[#E66C8A] font-bold hover:text-[#CF2E2E] transition">Daftar sekarang</a>
            </p>
        </div>

        <div class="hidden md:flex md:w-1/2 bg-cover bg-center relative items-center" 
             style="background-image: url('https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?auto=format&fit=crop&w=800&q=80');">
            <div class="absolute inset-0 bg-gradient-to-t from-[#1D1145]/90 via-[#1D1145]/70 to-[#1D1145]/40"></div>
            
            <div class="relative z-10 p-12 text-white">
                <span class="text-[#0DB5BB] text-sm font-bold uppercase tracking-widest">Selamat Datang Kembali</span>
                <h1 class="text-4xl font-extrabold mt-2 mb-4 leading-tight">Amankan Tiket Konsermu!</h1>
                <p class="text-gray-300 text-sm font-light leading-relaxed max-w-sm">
                    Jangan sampai kehabisan. Masuk kembali untuk melanjutkan perburuan tiket event terpopuler Anda.
                </p>
                <div class="mt-8 flex items-center space-x-2 text-xs text-gray-400">
                    <span class="bg-white/10 px-3 py-1.5 rounded-full">🛡️ Sistem Keamanan Berlapis</span>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
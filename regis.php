<?php
session_start();
if (isset($_SESSION['error'])) {
    echo "<script>alert('{$_SESSION['error']}')</script>";
    unset($_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru | EventKu</title>
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
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--secondary); }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4 md:p-8">

    <div class="w-full max-w-4xl glass-card rounded-[32px] shadow-2xl overflow-hidden flex flex-col lg:flex-row">
        
        <div class="w-full lg:w-1/2 p-6 lg:p-10 flex flex-col justify-center bg-white/40">
            
            <div class="mb-6">
                <a href="/" class="text-2xl font-extrabold text-[#1D1145] tracking-tight">
                    Event<span class="text-[#0DB5BB]">Ku</span>
                </a>
                <h2 class="text-xl font-bold mt-4 text-gray-900">Gabung Sekarang</h2>
                <p class="text-gray-500 text-xs mt-1">Lengkapi data diri untuk mulai pengalaman baru.</p>
            </div>

            <form action="proses_regis.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-[#1D1145] mb-1.5 uppercase tracking-widest">Nama Lengkap</label>
                    <input type="text" name="nama" required 
                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#0DB5BB]/10 focus:border-[#0DB5BB] transition-all text-sm outline-none"
                           placeholder="John Doe">
                </div>

                <div>
                    <label class="block text-[10px] font-bold text-[#1D1145] mb-1.5 uppercase tracking-widest">Email Address</label>
                    <input type="email" name="email" required 
                           class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#0DB5BB]/10 focus:border-[#0DB5BB] transition-all text-sm outline-none"
                           placeholder="nama@email.com">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[10px] font-bold text-[#1D1145] mb-1.5 uppercase tracking-widest">Password</label>
                        <input type="password" name="password" required 
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#0DB5BB]/10 focus:border-[#0DB5BB] transition-all text-sm outline-none"
                               placeholder="••••••••">
                    </div>
                    <div>
                        <label class="block text-[10px] font-bold text-[#1D1145] mb-1.5 uppercase tracking-widest">Konfirmasi</label>
                        <input type="password" name="confirm_password" required 
                               class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl focus:ring-4 focus:ring-[#0DB5BB]/10 focus:border-[#0DB5BB] transition-all text-sm outline-none"
                               placeholder="••••••••">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" name="register" class="w-full cta-gradient text-white py-3.5 rounded-xl font-bold text-xs shadow-xl transform transition duration-300 uppercase tracking-widest">
                        Daftar Akun
                    </button>
                </div>
            </form>

            <p class="mt-8 text-center text-gray-500 text-[11px]">
                Sudah punya akun? 
                <a href="login.php" class="text-[#0DB5BB] font-bold hover:text-[#1D1145] transition underline underline-offset-4">Masuk di sini</a>
            </p>
        </div>

        <div class="hidden lg:flex lg:w-1/2 bg-cover bg-center relative items-center" 
             style="background-image: url('https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?auto=format&fit=crop&w=1200&q=80');">
            <div class="absolute inset-0 bg-gradient-to-br from-[#1D1145]/95 to-[#1D1145]/70"></div>
            
            <div class="relative z-10 p-10 lg:p-12 text-white">
                <div class="w-12 h-1 bg-[#0DB5BB] mb-6 rounded-full"></div>
                <h1 class="text-3xl font-extrabold mb-4 leading-tight">Mulai Petualangan Eventmu!</h1>
                <p class="text-gray-300 text-sm font-light leading-relaxed mb-8">
                    Bergabunglah dengan ribuan penikmat event lainnya. Dapatkan akses eksklusif ke tiket presale dan promo menarik setiap minggunya.
                </p>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-white/5 p-4 rounded-xl border border-white/10 backdrop-blur-sm text-center">
                        <span class="block text-xl mb-1">🎟️</span>
                        <span class="text-[10px] font-bold uppercase tracking-tighter">Tiket Digital</span>
                    </div>
                    <div class="bg-white/5 p-4 rounded-xl border border-white/10 backdrop-blur-sm text-center">
                        <span class="block text-xl mb-1">⚡</span>
                        <span class="text-[10px] font-bold uppercase tracking-tighter">Fast Checkout</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</body>
</html>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sehati</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#413074',
                        primaryDark: '#2c1e54',
                        secondary: '#f6f9f9',
                        accent: '#0fb7b8',
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'], }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f6f9f9; }
        .glass-card {
            background: white;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="glass-card w-full max-w-md rounded-3xl p-8 md:p-10">
        <div class="flex flex-col items-center mb-8">
            <img src="/sehati/frontend/logo.png" alt="SEHATI Logo" class="h-16 mb-4">
            <h1 class="text-2xl font-bold text-slate-800">Selamat Datang Kembali</h1>
            <p class="text-slate-500 text-sm mt-1 text-center">Masuk ke portal SEHATI untuk mengelola jadwal dan layanan kesehatan Anda.</p>
        </div>

        <form action="login_process.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">NIK atau Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <input type="text" name="identifier" id="login-identifier" placeholder="Masukkan NIK atau Email Anda" class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required>
                </div>
            </div>

            <div>
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-semibold text-slate-700">Password</label>
                    <a href="forgot_password.php" class="text-xs font-semibold text-primary hover:underline">Lupa Password?</a>
                </div>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input type="password" name="password" placeholder="Masukkan Password" class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required>
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-primary text-white font-bold rounded-xl hover:bg-primaryDark transition-all shadow-md mt-6">
                Masuk
            </button>
        </form>

        <p class="text-center text-sm text-slate-600 mt-8">
            Belum punya akun? <a href="register.php" class="font-bold text-primary hover:underline">Daftar sekarang</a>
        </p>
    </div>

</body>
</html>

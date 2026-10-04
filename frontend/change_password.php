<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Password - Sehati</title>
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
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .glass-card {
            background: white;
            border: 1px solid rgba(0,0,0,0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="text-slate-800 pb-8">

    <!-- Header -->
    <header class="bg-white text-slate-800 p-4 sticky top-0 z-50 border-b border-slate-200 shadow-sm flex items-center gap-4">
        <a href="profile.php" class="text-slate-500 hover:text-primary w-8 h-8 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors">
            <i class="fa-solid fa-arrow-left text-xl"></i>
        </a>
        <h1 class="text-lg font-bold">Ubah Password</h1>
    </header>

    <main class="w-full max-w-xl mx-auto p-4 md:p-8 mt-2">
        <div class="glass-card rounded-2xl p-6 md:p-8">
            <div class="mb-6">
                <p class="text-sm text-slate-500">Pastikan password baru Anda kuat (mengandung huruf, angka, dan minimal 8 karakter) demi menjaga keamanan akun SEHATI Anda.</p>
            </div>

            <form id="changePasswordForm" class="space-y-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Password Saat Ini <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" placeholder="Masukkan password lama" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required>
                    </div>
                </div>
                
                <div class="pt-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Password Baru <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" id="new-password" placeholder="Minimal 8 karakter" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required minlength="8">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Konfirmasi Password Baru <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="password" id="confirm-password" placeholder="Ketik ulang password baru" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required minlength="8">
                    </div>
                    <p id="error-msg" class="text-xs text-red-500 mt-2 hidden">Konfirmasi password tidak cocok!</p>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full py-3.5 bg-primary text-white font-bold rounded-xl hover:bg-primaryDark transition-all shadow-md">
                        Ubah Password
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const newPwd = document.getElementById('new-password').value;
            const confirmPwd = document.getElementById('confirm-password').value;
            const errorMsg = document.getElementById('error-msg');
            
            if(newPwd !== confirmPwd) {
                errorMsg.classList.remove('hidden');
                return;
            }
            
            errorMsg.classList.add('hidden');
            
            // Show success logic then redirect
            const btn = this.querySelector('button[type="submit"]');
            btn.innerHTML = '<i class="fa-solid fa-circle-check mr-2"></i> Berhasil Diubah!';
            btn.classList.add('bg-emerald-500', 'hover:bg-emerald-600');
            
            setTimeout(() => {
                window.location.href = 'profile.php';
            }, 1000);
        });
    </script>
</body>
</html>

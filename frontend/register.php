<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun Baru - Sehati</title>
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
<body class="min-h-screen flex items-center justify-center p-4 py-10">

    <div class="glass-card w-full max-w-2xl rounded-3xl p-8 md:p-10">
        <div class="flex flex-col items-center mb-8">
            <img src="/sehati/frontend/logo.png" alt="SEHATI Logo" class="h-16 mb-4">
            <h1 class="text-2xl font-bold text-slate-800">Registrasi Akun Baru</h1>
            <p class="text-slate-500 text-sm mt-1 text-center">Bergabung dengan SEHATI untuk kemudahan layanan kesehatan.</p>
        </div>

        <form action="register_process.php" method="POST" class="space-y-5">
            <!-- Data Diri -->
            <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4"><i class="fa-solid fa-address-card mr-2 text-primary"></i> Data Identitas</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap Sesuai KTP <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="reg-name" placeholder="Contoh: Budi Santoso" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Induk Kependudukan (NIK) <span class="text-red-500">*</span></label>
                    <input type="text" name="nik" placeholder="16 Digit Angka NIK" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required maxlength="16">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Kartu Keluarga (KK) <span class="text-red-500">*</span></label>
                    <input type="text" name="no_kk" placeholder="16 Digit Angka No. KK" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required maxlength="16">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor BPJS <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <input type="text" name="no_bpjs" placeholder="Kosongkan jika tidak ada" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all">
                </div>
            </div>

            <!-- Kontak & Akun -->
            <h3 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-2 mb-4 mt-6"><i class="fa-solid fa-user-lock mr-2 text-primary"></i> Kontak & Keamanan</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Email <span class="text-red-500">*</span></label>
                    <input type="email" name="email" placeholder="budi@example.com" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Telepon / WhatsApp <span class="text-red-500">*</span></label>
                    <input type="tel" name="no_hp" placeholder="Contoh: 08123456789" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Password <span class="text-red-500">*</span></label>
                    <input type="password" name="password" placeholder="Minimal 8 karakter" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required minlength="8">
                </div>
            </div>
            
            <div class="pt-4">
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" required class="mt-1 w-4 h-4 text-primary rounded focus:ring-primary">
                    <span class="text-xs text-slate-600">
                        Saya menyetujui <a href="#" class="font-bold text-primary hover:underline">Syarat dan Ketentuan</a> serta Kebijakan Privasi dari sistem SEHATI.
                    </span>
                </label>
            </div>

            <button type="submit" class="w-full py-4 bg-primary text-white font-bold rounded-xl hover:bg-primaryDark transition-all shadow-md mt-6">
                Buat Akun Sekarang
            </button>
        </form>

        <p class="text-center text-sm text-slate-600 mt-8">
            Sudah punya akun? <a href="login.php" class="font-bold text-primary hover:underline">Masuk di sini</a>
        </p>
    </div>

</body>
</html>

<?php
session_start();
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nik = $_POST['nik'] ?? '';
    $email = $_POST['email'] ?? '';
    $new_password = $_POST['new_password'] ?? '';

    if (empty($nik) || empty($email) || empty($new_password)) {
        $error = "Semua kolom harus diisi!";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE nik = ? AND email = ?");
        $stmt->bind_param("ss", $nik, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed_password, $user['id']);
            
            if ($update->execute()) {
                $success = "Password berhasil diubah! Silakan login dengan password baru Anda.";
            } else {
                $error = "Terjadi kesalahan sistem, silakan coba lagi.";
            }
        } else {
            $error = "Data NIK dan Email tidak cocok dengan pengguna mana pun di sistem kami.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Sehati</title>
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
<body class="min-h-screen flex items-center justify-center p-4 relative">

    <!-- Back Button to Login -->
    <a href="login.php" class="absolute top-6 left-6 md:top-8 md:left-8 w-11 h-11 bg-white border border-slate-200 text-slate-700 flex items-center justify-center rounded-xl shadow-sm hover:bg-primary hover:text-white transition-all group">
        <i class="fa-solid fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
    </a>

    <div class="glass-card w-full max-w-md rounded-3xl p-8 md:p-10">
        <div class="flex flex-col items-center mb-8">
            <div class="w-16 h-16 bg-rose-50 text-rose-500 flex items-center justify-center rounded-2xl mb-4">
                <i class="fa-solid fa-key text-2xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Lupa Password?</h1>
            <p class="text-slate-500 text-sm mt-2 text-center leading-relaxed">Jangan khawatir! Verifikasi identitas Anda dengan memasukkan NIK dan Email yang terdaftar untuk membuat password baru.</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-600 rounded-xl text-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-600 rounded-xl text-sm flex items-start gap-3">
                <i class="fa-solid fa-circle-check mt-0.5"></i>
                <div>
                    <p class="font-bold mb-1">Berhasil!</p>
                    <p><?php echo htmlspecialchars($success); ?></p>
                </div>
            </div>
            <a href="login.php" class="block w-full py-3.5 bg-primary text-white font-bold rounded-xl text-center hover:bg-primaryDark transition-all shadow-md mt-2">
                Kembali ke Login
            </a>
        <?php else: ?>

        <form action="" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">NIK Induk Kependudukan</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-id-card"></i>
                    </div>
                    <input type="text" name="nik" placeholder="Masukkan 16 digit NIK Anda" class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required maxlength="16" pattern="[0-9]{16}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Alamat Email</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-envelope"></i>
                    </div>
                    <input type="email" name="email" placeholder="Masukkan Email yang terdaftar" class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Password Baru</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input type="password" name="new_password" placeholder="Buat password baru Anda" class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required minlength="6">
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 bg-primary text-white font-bold rounded-xl hover:bg-primaryDark transition-all shadow-md mt-6">
                Ganti Password
            </button>
        </form>

        <?php endif; ?>

    </div>

</body>
</html>

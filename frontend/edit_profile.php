<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once '../config/database.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $no_kk = trim($_POST['no_kk'] ?? '');
    $no_bpjs = trim($_POST['no_bpjs'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');

    if (empty($nama) || empty($no_kk) || empty($email) || empty($no_hp)) {
        $error = "Semua field bertanda * harus diisi.";
    } else {
        $stmt = $conn->prepare("UPDATE users SET nama_lengkap = ?, no_kk = ?, no_bpjs = ?, email = ?, no_hp = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $nama, $no_kk, $no_bpjs, $email, $no_hp, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $_SESSION['user_nama'] = $nama;
            $success = true;
        } else {
            $error = "Gagal memperbarui profil: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch current user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil - Sehati</title>
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
        <h1 class="text-lg font-bold">Edit Profil</h1>
    </header>

    <main class="w-full max-w-2xl mx-auto p-4 md:p-8 mt-2">
        <div class="glass-card rounded-2xl p-6 md:p-8">
            
            <?php if ($success): ?>
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-start gap-3">
                <i class="fa-solid fa-circle-check mt-1"></i>
                <div>
                    <h4 class="font-bold">Berhasil!</h4>
                    <p class="text-sm">Profil Anda telah berhasil diperbarui.</p>
                </div>
            </div>
            <script>
                setTimeout(() => window.location.href = 'profile.php', 1500);
            </script>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-start gap-3">
                <i class="fa-solid fa-triangle-exclamation mt-1"></i>
                <p class="text-sm"><?php echo htmlspecialchars($error); ?></p>
            </div>
            <?php endif; ?>

            <form method="POST" action="edit_profile.php" id="editProfileForm" class="space-y-5">
                <div class="flex flex-col items-center mb-8">
                    <div class="relative">
                        <div class="w-24 h-24 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 text-4xl mb-2 overflow-hidden">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <button type="button" class="absolute bottom-2 right-0 w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center border-2 border-white shadow-sm hover:bg-primaryDark transition-colors">
                            <i class="fa-solid fa-camera text-xs"></i>
                        </button>
                    </div>
                    <p class="text-xs text-slate-500 font-medium">Ubah Foto Profil</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap Sesuai KTP <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" value="<?php echo htmlspecialchars($user['nama_lengkap'] ?? ''); ?>" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Induk Kependudukan (NIK) <span class="text-slate-400 font-normal">(Tidak bisa diubah)</span></label>
                        <input type="text" value="<?php echo htmlspecialchars($user['nik'] ?? ''); ?>" disabled class="w-full px-4 py-3 bg-slate-50 border border-slate-200 text-slate-500 rounded-xl outline-none text-sm cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Kartu Keluarga (KK) <span class="text-red-500">*</span></label>
                        <input type="text" name="no_kk" value="<?php echo htmlspecialchars($user['no_kk'] ?? ''); ?>" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor BPJS</label>
                        <input type="text" name="no_bpjs" value="<?php echo htmlspecialchars($user['no_bpjs'] ?? ''); ?>" placeholder="Kosongkan jika tidak ada" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nomor Telepon / WhatsApp <span class="text-red-500">*</span></label>
                        <input type="tel" name="no_hp" value="<?php echo htmlspecialchars($user['no_hp'] ?? ''); ?>" class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none focus:border-primary focus:ring-1 focus:ring-primary text-sm transition-all" required>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full py-3.5 bg-primary text-white font-bold rounded-xl hover:bg-primaryDark transition-all shadow-md flex items-center justify-center gap-2">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>

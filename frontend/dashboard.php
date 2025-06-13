<?php
date_default_timezone_set('Asia/Jakarta');

$api_url = 'http://127.0.0.1:5000/pembayaran';
$data = [];
$error = null;
$total_pembayaran = 0;
$jumlah_transaksi = 0;

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response !== false && $http_code === 200) {
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $error = 'Gagal mendekode data JSON: ' . json_last_error_msg();
    } else {
        $jumlah_transaksi = count($data);
        foreach ($data as $row) {
            $total_pembayaran += $row['nominal_dibayar'];
        }
    }
} else {
    $error = 'Gagal mengambil data pembayaran: ' . ($http_code ? "HTTP $http_code" : 'Koneksi gagal');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Zakat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(to bottom, #b3e5fc, #81d4fa, #4fc3f7);
            font-family: 'Segoe UI', sans-serif;
        }
        .ocean-wave {
            background: url('https://www.transparenttextures.com/patterns/waves.png');
            background-size: cover;
            background-position: center;
        }
        .card {
            background-color: #ffffff;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl ocean-wave p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-blue-800">🕌 Dashboard Zakat</h1>
            <div class="space-x-2">
                <a href="tambahpembayaran.php" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">Tambah Pembayaran</a>
                <a href="beras.php" class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600">Data Beras</a>
                <a href="datapembayaran.php" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">Data Pembayaran</a>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 border border-red-400 rounded-lg p-4 mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="card p-6">
                <h2 class="text-xl font-semibold text-blue-700">Total Pembayaran</h2>
                <p class="text-2xl font-bold text-green-600">Rp <?= number_format($total_pembayaran, 2, ',', '.') ?></p>
            </div>
            <div class="card p-6">
                <h2 class="text-xl font-semibold text-blue-700">Jumlah Transaksi</h2>
                <p class="text-2xl font-bold text-purple-600"><?= $jumlah_transaksi ?></p>
            </div>
            <div class="card p-6">
                <h2 class="text-xl font-semibold text-blue-700">Tanggal Terakhir Update</h2>
                <p class="text-2xl font-bold text-orange-600"><?= date('D, d M Y H:i') ?></p>
            </div>
        </div>
    </div>
</body>
</html>
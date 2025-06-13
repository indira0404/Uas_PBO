<?php
date_default_timezone_set('Asia/Jakarta');

$api_url = 'http://127.0.0.1:5000/pembayaran';
$data = [];
$error = null;

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
    }
} else {
    $error = 'Gagal mengambil data pembayaran: ' . ($http_code ? "HTTP $http_code" : 'Koneksi gagal');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Pembayaran Zakat</title>
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
        .table-header {
            background-color: #e0f7fa;
            color: #0288d1;
        }
        .action-btn {
            padding: 0.25rem 0.5rem;
            margin: 0 0.25rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-6xl ocean-wave p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-blue-800">🕌 Pembayaran Zakat</h1>
            <div>
                <a href="dashboard.php" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 mr-2">🏠 Kembali</a>
                <button class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">Generate Excel</button>
                <button class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 ml-2">Edit</button>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 border border-red-400 rounded-lg p-4 mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto rounded-lg border border-blue-200 bg-white">
            <table class="min-w-full text-sm text-center">
                <thead class="table-header uppercase text-xs font-bold">
                    <tr>
                        <th class="px-3 py-2">ID</th>
                        <th class="px-3 py-2">Jumlah Jiwa</th>
                        <th class="px-3 py-2">Jenis Zakat</th>
                        <th class="px-3 py-2">Nama</th>
                        <th class="px-3 py-2">Metode Pembayaran</th>
                        <th class="px-3 py-2">Total Bayar</th>
                        <th class="px-3 py-2">Nominal Dibayar</th>
                        <th class="px-3 py-2">Kembalian</th>
                        <th class="px-3 py-2">Keterangan</th>
                        <th class="px-3 py-2">Tanggal Bayar</th>
                        <th class="px-3 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php if (!empty($data) && is_array($data)): ?>
                        <?php foreach ($data as $row): ?>
                            <tr class="border-b hover:bg-blue-50">
                                <td class="py-2"><?= htmlspecialchars($row['id']) ?></td>
                                <td class="py-2"><?= htmlspecialchars($row['jumlah_jiwa']) ?></td>
                                <td class="py-2"><?= htmlspecialchars($row['jenis_zakat']) ?></td>
                                <td class="py-2"><?= htmlspecialchars($row['nama']) ?></td>
                                <td class="py-2"><?= htmlspecialchars($row['metode_pembayaran']) ?></td>
                                <td class="py-2">Rp <?= number_format($row['total_bayar'], 2, ',', '.') ?></td>
                                <td class="py-2">Rp <?= number_format($row['nominal_dibayar'], 2, ',', '.') ?></td>
                                <td class="py-2">Rp <?= number_format($row['kembalian'], 2, ',', '.') ?></td>
                                <td class="py-2"><?= htmlspecialchars($row['keterangan']) ?></td>
                                <td class="py-2"><?= date('D, d M Y H:i', strtotime($row['tanggal_bayar'])) ?></td>
                                <td class="py-2">
                                    <button class="action-btn bg-blue-600 hover:bg-blue-700 text-white">Edit</button>
                                    <button class="action-btn bg-red-500 hover:bg-red-600 text-white">Hapus</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="py-4 text-gray-500">Tidak ada data pembayaran tersedia</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
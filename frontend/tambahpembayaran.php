<?php
date_default_timezone_set('Asia/Jakarta');

$api_url = 'http://127.0.0.1:5000/pembayaran';
$beras_api_url = 'http://127.0.0.1:5000/beras';
$data = [];
$beras_data = [];
$error = null;
$beras_error = null;
$success = null;

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

$ch = curl_init($beras_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$beras_response = curl_exec($ch);
$beras_http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($beras_response !== false && $beras_http_code === 200) {
    $beras_data = json_decode($beras_response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $beras_error = 'Gagal mendekode data beras: ' . json_last_error_msg();
    }
} else {
    $beras_error = 'Gagal mengambil data beras: ' . ($beras_http_code ? "HTTP $beras_http_code" : 'Koneksi gagal');
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $jumlah_jiwa = $_POST['jumlah_jiwa'] ?? 0;
    $jenis_zakat = $_POST['jenis_zakat'] ?? '';
    $beras_pilihan = $_POST['beras_pilihan'] ?? '';
    $pendapatan_tahunan = $_POST['pendapatan_tahunan'] ?? 0;
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? '';
    $total_bayar = $_POST['total_bayar'] ?? 0;
    $nominal_dibayar = $_POST['nominal_dibayar'] ?? 0;
    $tanggal_bayar = $_POST['tanggal_bayar'] ?? date('Y-m-d\TH:i');

    $kembalian = $nominal_dibayar - $total_bayar;

    if ($jenis_zakat === 'beras' && $beras_pilihan) {
        $selected_beras = array_filter($beras_data, fn($b) => $b['id'] == $beras_pilihan);
        $selected_beras = array_values($selected_beras)[0] ?? null;
        if ($selected_beras) {
            $total_bayar = $selected_beras['harga'] * $jumlah_jiwa;
            $kembalian = $nominal_dibayar - $total_bayar;
        }
    } elseif ($jenis_zakat === 'uang') {
        $nisab = 52400000; // Nilai nisab (contoh)
        $zakat = ($pendapatan_tahunan > $nisab) ? ($pendapatan_tahunan * 0.025) : 0;
        $total_bayar = $zakat * $jumlah_jiwa;
        $kembalian = $nominal_dibayar - $total_bayar;
    }

    if ($kembalian >= 0) {
        $payload = [
            'nama' => $nama,
            'jumlah_jiwa' => $jumlah_jiwa,
            'jenis_zakat' => $jenis_zakat,
            'metode_pembayaran' => $metode_pembayaran,
            'total_bayar' => $total_bayar,
            'nominal_dibayar' => $nominal_dibayar,
            'kembalian' => $kembalian,
            'tanggal_bayar' => $tanggal_bayar
        ];

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 201) {
            $success = "Pembayaran zakat berhasil disimpan.";
        } else {
            $error = "Gagal menyimpan pembayaran: HTTP $http_code";
        }
    } else {
        $error = "Nominal dibayar tidak cukup untuk menutupi total bayar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pembayaran Zakat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f7e4bc, #a8d5ba, #87c2a7);
            font-family: 'Poppins', sans-serif;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .form-input:focus {
            border-color: #4caf50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }
        .submit-btn {
            background: linear-gradient(90deg, #4caf50, #2e7d32);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .submit-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }
        .back-btn {
            background: linear-gradient(90deg, #757575, #424242);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .back-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(117, 117, 117, 0.4);
        }
        .section-title {
            position: relative;
        }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50%;
            height: 2px;
            background: #4caf50;
            transition: width 0.3s;
        }
        .section-title:hover::after {
            width: 100%;
        }
    </style>
</head>
<body class="bg-gradient-to-r from-sky-100 to-emerald-100 font-sans min-h-screen flex items-center justify-center">
    <div class="bg-white shadow-2xl rounded-2xl p-8 w-full max-w-2xl space-y-6 container">
        <h1 class="text-3xl font-extrabold text-center text-emerald-700 mb-4 section-title">Pembayaran Zakat</h1>

        <?php if ($beras_error): ?>
            <div class="bg-red-50 border border-red-300 text-red-600 px-4 py-3 rounded text-center">
                <?= htmlspecialchars($beras_error); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-300 text-red-600 px-4 py-3 rounded text-center">
                <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-emerald-50 border border-emerald-300 text-emerald-700 px-4 py-3 rounded text-center">
                <?= htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <form id="paymentForm" method="post" action="" class="space-y-4">
            <div>
                <label class="block text-gray-700 font-semibold mb-1 section-title">Nama</label>
                <input type="text" name="nama" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400 form-input" required>
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-1 section-title">Jumlah Jiwa</label>
                <input type="number" id="jumlah_jiwa" name="jumlah_jiwa" min="1" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400 form-input" required>
            </div>
            <div>
                <label class="block text-gray-700 font-semibold mb-1 section-title">Jenis Zakat</label>
                <select id="jenis_zakat" name="jenis_zakat" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-400 form-input" required>
                    <option value="">Pilih Jenis Zakat</option>
                    <option value="beras">Beras</option>
                    <option value="uang">Uang</option>
                </select>
            </div>

            <div id="beras_section" class="hidden">
                <label class="block text-gray-700 font-semibold mb-1 section-title">Pilih Jenis Beras</label>
                <select id="beras_pilihan" name="beras_pilihan" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-400 form-input">
                    <option value="">Pilih Beras</option>
                    <?php foreach ($beras_data as $beras): ?>
                        <option value="<?= htmlspecialchars($beras['id']) ?>" data-harga="<?= htmlspecialchars($beras['harga']) ?>">
                            <?= htmlspecialchars($beras['id']) ?> - Rp <?= number_format($beras['harga'], 2) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="pendapatan_section" class="hidden">
                <label class="block text-gray-700 font-semibold mb-1 section-title">Pendapatan Tahunan (Rp)</label>
                <input type="number" id="pendapatan_tahunan" name="pendapatan_tahunan" min="0" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-400 form-input">
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1 section-title">Metode Pembayaran</label>
                <input type="text" name="metode_pembayaran" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400 form-input" required>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1 section-title">Total Bayar (Rp)</label>
                <input type="number" step="0.01" id="total_bayar" name="total_bayar" class="w-full p-3 bg-gray-100 border border-gray-200 rounded-lg form-input" readonly required>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1 section-title">Nominal Dibayar (Rp)</label>
                <input type="number" step="0.01" id="nominal_dibayar" name="nominal_dibayar" min="0" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400 form-input" required>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1 section-title">Kembalian (Rp)</label>
                <input type="number" step="0.01" id="kembalian" name="kembalian" class="w-full p-3 bg-gray-100 border border-gray-200 rounded-lg form-input" readonly>
            </div>

            <div>
                <label class="block text-gray-700 font-semibold mb-1 section-title">Tanggal Bayar</label>
                <input type="datetime-local" name="tanggal_bayar" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-400 form-input" required>
            </div>

            <div class="flex justify-between mt-6">
                <a href="dashboard.php" class="px-6 py-2 rounded-lg bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold transition back-btn">Kembali</a>
                <button type="submit" class="px-6 py-2 rounded-lg bg-gradient-to-r from-emerald-500 to-sky-500 hover:from-emerald-600 hover:to-sky-600 text-white text-sm font-semibold transition submit-btn">
                    Simpan Pembayaran
                </button>
            </div>
        </form>

        <script>
            document.getElementById('jenis_zakat').addEventListener('change', function() {
                const berasSection = document.getElementById('beras_section');
                const pendapatanSection = document.getElementById('pendapatan_section');
                const jenisZakat = this.value;

                berasSection.classList.add('hidden');
                pendapatanSection.classList.add('hidden');

                if (jenisZakat === 'beras') {
                    berasSection.classList.remove('hidden');
                } else if (jenisZakat === 'uang') {
                    pendapatanSection.classList.remove('hidden');
                }

                updateTotalBayar();
            });

            document.getElementById('beras_pilihan').addEventListener('change', updateTotalBayar);
            document.getElementById('pendapatan_tahunan').addEventListener('input', updateTotalBayar);
            document.getElementById('jumlah_jiwa').addEventListener('input', updateTotalBayar);
            document.getElementById('nominal_dibayar').addEventListener('input', updateKembalian);

            function updateTotalBayar() {
                const jenisZakat = document.getElementById('jenis_zakat').value;
                const jumlahJiwa = parseInt(document.getElementById('jumlah_jiwa').value) || 0;
                let totalBayar = 0;

                if (jenisZakat === 'beras') {
                    const berasPilihan = document.getElementById('beras_pilihan');
                    const selectedOption = berasPilihan.options[berasPilihan.selectedIndex];
                    const harga = parseFloat(selectedOption?.dataset.harga) || 0;
                    totalBayar = harga * jumlahJiwa;
                } else if (jenisZakat === 'uang') {
                    const pendapatanTahunan = parseFloat(document.getElementById('pendapatan_tahunan').value) || 0;
                    const nisab = 52400000; // Nilai nisab (contoh)
                    const zakat = (pendapatanTahunan > nisab) ? (pendapatanTahunan * 0.025) : 0;
                    totalBayar = zakat * jumlahJiwa;
                }

                document.getElementById('total_bayar').value = totalBayar.toFixed(2);
                updateKembalian();
            }

            function updateKembalian() {
                const totalBayar = parseFloat(document.getElementById('total_bayar').value) || 0;
                const nominalDibayar = parseFloat(document.getElementById('nominal_dibayar').value) || 0;
                const kembalian = nominalDibayar - totalBayar;
                document.getElementById('kembalian').value = kembalian >= 0 ? kembalian.toFixed(2) : '0.00';
            }
        </script>
    </div>
</body>
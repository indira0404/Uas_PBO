<?php
// Set zona waktu ke WIB (Asia/Jakarta)
date_default_timezone_set('Asia/Jakarta');

// URL endpoint API Flask
$api_url = 'http://localhost:5000/beras';

// Inisialisasi variabel
$data = [];
$error = null;

// Ambil data beras
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
    $error = 'Gagal mengambil data beras: ' . ($http_code ? "HTTP $http_code" : 'Koneksi gagal');
}

// Tambah data jika ada permintaan POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_harga'])) {
    $data_to_send = ['harga' => floatval($_POST['add_harga'])];
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data_to_send));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 201) {
        $success = "Data beras berhasil ditambahkan.";
    } else {
        $error = "Gagal menambahkan data beras: HTTP $http_code";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Harga Beras</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-green-100 to-blue-100 min-h-screen font-sans">
    <div class="max-w-5xl mx-auto mt-10 p-6 bg-white rounded-xl shadow-2xl">
        <div class="flex justify-between items-center border-b pb-4 mb-4">
            <div class="flex items-center space-x-3">
                <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20"><path d="M10 0C4.477 0 0 4.477 0 10s4.477 10 10 10c1.65 0 3.19-.404 4.54-1.11l5.57 1.28-1.28-5.57A9.963 9.963 0 0020 10c0-5.523-4.477-10-10-10z"/></svg>
                <h1 class="text-2xl font-bold text-gray-800">Data Harga Beras</h1>
            </div>
            <div class="space-x-2">
                <a href="dashboard.php" class="bg-gray-700 text-white px-4 py-2 rounded hover:bg-gray-800">Kembali</a>
                <button onclick="openAddModal()" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Tambah Data</button>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-700 border border-red-300 rounded p-4 mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php elseif (isset($success)): ?>
            <div class="bg-green-100 text-green-700 border border-green-300 rounded p-4 mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="w-full table-auto text-sm text-left text-gray-700">
                <thead class="bg-gray-200">
                    <tr>
                        <th class="px-4 py-2">ID</th>
                        <th class="px-4 py-2">Harga</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (!empty($data) && is_array($data)): ?>
                        <?php foreach ($data as $record): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium text-gray-900"><?= htmlspecialchars($record['id']) ?></td>
                                <td class="px-4 py-2">Rp <?= number_format($record['harga'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="px-4 py-4 text-center text-gray-500">Tidak ada data ditemukan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-md">
            <h2 class="text-xl font-bold mb-4 text-center text-gray-800">Tambah Data Beras</h2>
            <form id="addForm" onsubmit="submitAddForm(event)" class="space-y-4">
                <div>
                    <label for="add_harga" class="block text-sm font-medium text-gray-600">Harga</label>
                    <input type="number" id="add_harga" name="add_harga" class="mt-1 w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-green-500" placeholder="Masukkan harga" step="0.01" required>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
                    <button type="button" onclick="closeAddModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Batal</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAddModal() {
            document.getElementById('add_harga').value = '';
            document.getElementById('addModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        function submitAddForm(event) {
            event.preventDefault();
            const harga = document.getElementById('add_harga').value;
            if (!harga || isNaN(harga)) {
                alert("Harap masukkan harga yang valid!");
                return;
            }
            fetch('http://localhost:5000/beras', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ harga: parseFloat(harga) })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal menyimpan data');
                }
                return response.json();
            })
            .then(result => {
                if (result.message === "Beras created successfully") {
                    alert("Data beras berhasil ditambahkan!");
                    location.reload();
                } else {
                    throw new Error(result.message || 'Error tidak diketahui');
                }
            })
            .catch(error => {
                alert("Terjadi kesalahan: " + error.message);
            });
        }
    </script>
</body>
</html>

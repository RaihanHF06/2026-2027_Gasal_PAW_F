<?php
include 'koneksi.php';

$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : date('Y-m-01');
$tanggal_selesai = isset($_GET['tanggal_selesai']) ? $_GET['tanggal_selesai'] : date('Y-m-d');

// rekap transaksi per tanggal
$query = mysqli_query($koneksi, "
    SELECT 
        DATE(waktu_transaksi) AS tanggal,
        SUM(total) AS total
    FROM transaksi
    WHERE waktu_transaksi BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'
    GROUP BY DATE(waktu_transaksi)
    ORDER BY tanggal ASC
");

$data_tanggal = [];
$data_total = [];
$nomor = 1;
$total_pendapatan = 0;

while ($data = mysqli_fetch_assoc($query)) {
    $data_tanggal[] = $data['tanggal'];
    $data_total[] = $data['total'];
    $total_pendapatan += $data['total'];
}

// jumlah pelanggan
$query_pelanggan = mysqli_query($koneksi, "
    SELECT COUNT(DISTINCT pelanggan_id) AS jumlah
    FROM transaksi
    WHERE waktu_transaksi BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'
");

$data_pelanggan = mysqli_fetch_assoc($query_pelanggan);
$jumlah_pelanggan = $data_pelanggan['jumlah'];

function rupiah($angka) {
    return 'Rp. ' . number_format($angka, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Laporan Penjualan</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            color: #333;
            background: white;
        }

        h1 {
            color: #4285a8;
            font-size: 20px;
            font-weight: normal;
            margin-bottom: 25px;
        }

        .tanggal {
            color: #777;
            font-size: 15px;
        }

        .filter {
            margin-bottom: 20px;
        }

        .filter input {
            padding: 7px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        button, .tombol {
            padding: 9px 15px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
        }

        .cetak, .excel {
            background: #e87519;
            margin-right: 5px;
        }

        .filter button {
            background: #337ab7;
        }

        .tombol-aksi {
            margin-bottom: 10px;
        }

        .grafik {
            width: 620px;
            max-width: 100%;
            height: 270px;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 13px;
        }

        th {
            background: #d9edf7;
            text-align: left;
            padding: 10px;
        }

        td {
            padding: 9px;
            border-bottom: 1px solid #ddd;
        }

        .ringkasan {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            width: 500px;
            max-width: 100%;
        }

        .ringkasan table {
            margin: 0;
        }

        .ringkasan th {
            color: #4285a8;
            font-weight: normal;
        }

        .ringkasan td {
            text-align: right;
            color: #4285a8;
            font-weight: bold;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 15px;
            }

            .grafik {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="filter no-print">
        <form method="GET">
            <label>Tanggal Mulai:</label>
            <input type="date" name="tanggal_mulai"
                value="<?php echo $tanggal_mulai; ?>" required>

            <label>Tanggal Selesai:</label>
            <input type="date" name="tanggal_selesai"
                value="<?php echo $tanggal_selesai; ?>" required>

            <button type="submit">Tampilkan</button>
        </form>
    </div>

    <h1>
        Rekap Laporan Penjualan
        <span class="tanggal">
            <?php echo date('Y-m-d', strtotime($tanggal_mulai)); ?>
            sampai
            <?php echo date('Y-m-d', strtotime($tanggal_selesai)); ?>
        </span>
    </h1>

    <div class="tombol-aksi no-print">
        <button class="cetak" onclick="window.print()">Cetak</button>

        <a class="tombol excel"
            href="export_excel.php?tanggal_mulai=<?php echo $tanggal_mulai; ?>&tanggal_selesai=<?php echo $tanggal_selesai; ?>">
            Excel
        </a>
    </div>

    <div class="grafik">
        <canvas id="grafikPenjualan"></canvas>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 20%;">No</th>
                <th style="width: 45%;">Total</th>
                <th>Tanggal</th>
            </tr>
        </thead>

        <tbody>
            <?php
            for ($i = 0; $i < count($data_tanggal); $i++) {
            ?>
                <tr>
                    <td><?php echo $nomor++; ?></td>
                    <td><?php echo rupiah($data_total[$i]); ?></td>
                    <td><?php echo date('d M Y', strtotime($data_tanggal[$i])); ?></td>
                </tr>
            <?php
            }

            if (count($data_tanggal) == 0) {
            ?>
                <tr>
                    <td colspan="3" style="text-align:center;">
                        Tidak ada data transaksi
                    </td>
                </tr>
            <?php
            }
            ?>
        </tbody>
    </table>

    <div class="ringkasan">
        <table>
            <thead>
                <tr>
                    <th>Jumlah Pelanggan</th>
                    <th>Jumlah Pendapatan</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td><?php echo $jumlah_pelanggan; ?> Orang</td>
                    <td><?php echo rupiah($total_pendapatan); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        const tanggal = <?php echo json_encode($data_tanggal); ?>;
        const total = <?php echo json_encode($data_total); ?>;

        const ctx = document.getElementById('grafikPenjualan');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: tanggal,
                datasets: [{
                    label: 'Total',
                    data: total,
                    backgroundColor: 'rgba(54, 112, 190, 0.8)',
                    borderColor: 'rgba(35, 85, 155, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

</body>
</html>
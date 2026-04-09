<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kegiatan Operasi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }
        th {
            background-color: #bdd9bf;
            font-weight: bold;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h3 {
            margin: 5px 0;
        }
        .keterangan {
            font-size: 10px;
            color: red;
            margin-bottom: 10px;
        }
        .total-col {
            background-color: #F47174;
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>LAPORAN BULANAN</h3>
        <h3>JUMLAH PASIEN OPERASI</h3>
        <p>{{ $tgllap }}</p>
    </div>

    <p class="keterangan">*Data dibawah ini berdasarkan Tanggal Registrasi</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Ruangan</th>
                <th>BPJS</th>
                <th>Umum</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach ($op as $a)
            <tr>
                <td>{{ $no++ }}</td>
                <td style="text-align: left; background-color: #bdd9bf;">{{ $a->jenis_op }}</td>
                <td>
                    @php
                    $query_result = DB::select("SELECT COUNT(*) as total FROM reg_periksa AS a
                                                INNER JOIN kamar_inap AS b ON b.no_rawat = a.no_rawat
                                                INNER JOIN booking_operasi AS c ON c.no_rawat = b.no_rawat
                                                INNER JOIN paket_operasi AS d ON d.kode_paket = c.kode_paket
                                                WHERE a.tgl_registrasi BETWEEN ? AND ?
                                                AND c.tanggal BETWEEN ? AND ?
                                                AND a.kd_pj='BPJ' AND (c.status='Proses Operasi' OR c.status='Selesai')", [ $tgl1, $tgl2, $tgl1, $tgl2]);
                    $anggota = $query_result[0]->total ?? 0;
                    @endphp
                    {{ $anggota }}
                </td>
                <td>
                    @php
                    $query_result2 = DB::select("SELECT COUNT(*) as total FROM reg_periksa AS a
                                                INNER JOIN kamar_inap AS b ON b.no_rawat = a.no_rawat
                                                INNER JOIN booking_operasi AS c ON c.no_rawat = b.no_rawat
                                                INNER JOIN paket_operasi AS d ON d.kode_paket = c.kode_paket
                                                WHERE a.tgl_registrasi BETWEEN ? AND ?
                                                AND c.tanggal BETWEEN ? AND ?
                                                AND a.kd_pj='PJ2' AND (c.status='Proses Operasi' OR c.status='Selesai')", [ $tgl1, $tgl2, $tgl1, $tgl2]);
                    $pns = $query_result2[0]->total ?? 0;
                    @endphp
                    {{ $pns }}
                </td>
                <td class="total-col">{{ $a->total }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

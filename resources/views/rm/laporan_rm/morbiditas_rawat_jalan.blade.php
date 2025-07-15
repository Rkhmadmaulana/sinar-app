@extends('layout.app')

@section('content')
<style>
    .date-form-container {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }

    .date-input-group {
        display: flex;
        align-items: center;
        white-space: nowrap;
        min-width: 0;
        flex-shrink: 0;
    }

    .date-input-group label {
        margin-right: 0.5rem;
        margin-bottom: 0;
        min-width: 50px;
        flex-shrink: 0;
    }

    .date-input-group input[type="date"] {
        width: 150px;
        flex-shrink: 0;
        max-width: 100%;
        box-sizing: border-box;
    }

    /* Styling khusus untuk tabel morbiditas */
    /* Pastikan tabel memiliki tinggi yang cukup untuk scroll vertikal */
    .morbiditas-table {
        font-size: 0.85rem;
        border-collapse: separate;
        width: 100%;
        min-width: 1800px;
        border-spacing: 0;
        /*height: auto; /* Biarkan tinggi menyesuaikan konten */
    }

    .morbiditas-table th,
    .morbiditas-table td {
        /*border: 1px solid #dee2e6;*/
        border-width:0 0 1px 1px;
        padding: 8px 6px;
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }

    .morbiditas-table > thead > .header-main > th{
        border-top-width:1px;
    }

    /* Header styling */
    .header-main {
        background-color: #007bff;
        color: white;
        font-weight: bold;
    }

    .header-age-group {
        background-color: #6c757d;
        color: white;
        font-weight: 600;
    }

    .header-gender {
        background-color: #28a745;
        color: white;
        font-weight: 600;
    }

    .age-header {
        font-size: 0.75rem;
        min-width: 60px;
        padding: 6px 4px;
    }

    .gender-header {
        font-size: 0.8rem;
        min-width: 30px;
        padding: 6px 4px;
        font-weight: bold;
    }

    /* Sticky columns untuk kode dan diagnosa */
    .sticky-col-1 {
        position: sticky;
        left: 0;
        background-color: #f8f9fa;
        z-index: 10;
        min-width: 80px;
        max-width: 80px;
        font-weight: bold;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }

    .sticky-col-2 {
        position: sticky;
        left: 80px;
        background-color: #f8f9fa;
        z-index: 10;
        min-width: 250px;
        max-width: 250px;
        text-align: left !important;
        font-weight: 600;
        border-right-width: 1px !important;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
        /*resize: horizontal; /* Tambahkan resize horizontal */
        overflow: hidden; /* <<<<<<<<< overflow tidak bekerja */
    }

    /* Style untuk resize handle yang lebih terlihat */
    .sticky-col-2::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 3px;
        /* height: 100%; */
        cursor: col-resize;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .sticky-col-2:hover::after {
        opacity: 0.5;
    }


    /* Data cells styling */
    .data-cell {
        min-width: 35px;
        max-width: 35px;
        padding: 6px 4px;
        font-size: 0.8rem;
    }

    /* Total columns styling */
    .total-col {
        background-color: #fff3cd;
        font-weight: bold;
        min-width: 60px;
    }

    /* Responsive breakpoints */
    @media (max-width: 768px) {
        .date-form-container {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
        
        .date-input-group {
            width: 100%;
            margin-bottom: 0;
        }
        
        .date-input-group input[type="date"] {
            width: 100%;
            max-width: 100%;
        }

        .morbiditas-table {
            font-size: 0.75rem;
        }

        .sticky-col-1{
            position: static !important;
        }

        .sticky-col-2 {
            min-width: 200px;
            max-width: inherit;
            position: static !important;
        }

        .card-body, .table-wrapper {
            height: 60vh;
        }

        .resize-handle{
            cursor: inherit;
        }

        .resize-handle:hover {
            background: none;
            opacity: 0;
        }
        
    }

    @media (max-width: 576px) {
        .date-form-container {
            gap: 0.5rem;
        }
        
        .date-input-group { 
            flex-direction: column;
            align-items: stretch;
        }
        
        .date-input-group label {
            margin-right: 0;
            margin-bottom: 0.25rem;
            min-width: auto;
        }
        
        .date-input-group input[type="date"] {
            width: 100%;
        }

        .morbiditas-table {
            font-size: 0.7rem;
        }

        .sticky-col-1 {
            min-width: 60px;
            max-width: 60px;
            position: static !important;
        }

        .sticky-col-2 {
            left: 60px;
            min-width: 180px;
            max-width: inherit;
            position: static !important;
        }

        .data-cell {
            min-width: 30px;
            max-width: 30px;
            padding: 4px 2px;
        }

        .card-body, .table-wrapper {
            height: 50vh;
        }

        .resize-handle{
            cursor: inherit;
        }

        .resize-handle:hover {
            background: none;
            opacity: 0;
        }
    }

    /* Pastikan card-body tidak overflow */
    .card-body {
        overflow-x: auto; /* Ubah dari overflow-x: auto menjadi overflow: auto */
        /*height: 70vh; /* Tambahkan tinggi maksimum agar bisa scroll vertikal */
        /*position: relative;*/
    }/

    /* Hover effects untuk kemudahan baca */
    .morbiditas-table tbody tr:hover {
        background-color: #f5f5f5;
    }

    .morbiditas-table tbody tr:hover .sticky-col-1,
    .morbiditas-table tbody tr:hover .sticky-col-2 {
        background-color: #e9ecef;
    }

    .table-wrapper {
        position: relative;
        overflow: auto;
        height: 70vh;
        border: 1px solid #dee2e6;
    }

    /* Alternatif manual resize dengan JavaScript - CSS pendukung */
    .resize-handle {
        position: absolute;
        top: 0;
        right: 0;
        width: 5px;
        height: 100%;
        background: transparent;
        cursor: col-resize;
        z-index: 15;
    }

    .resize-handle:hover {
        background: #007bff;
        opacity: 0.5;
    }
</style>

<script>
    //document.addEventListener('DOMContentLoaded', function() {
    //    const diagnosisColumns = document.querySelectorAll('.sticky-col-2');
//
    //    // Check if the screen is desktop
    //    function isDesktop() {
    //        return window.matchMedia('(min-width: 769px)').matches;
    //    }
//
    //    diagnosisColumns.forEach(column => {
    //        if (!isDesktop()) return; // Skip if not desktop
//
    //        // Add resize handle
    //        const resizeHandle = document.createElement('div');
    //        resizeHandle.className = 'resize-handle';
    //        column.appendChild(resizeHandle);
//
    //        let isResizing = false;
    //        let startX = 0;
    //        let startWidth = 0;
//
    //        resizeHandle.addEventListener('mousedown', function(e) {
    //            if (!isDesktop()) return; // Skip if not desktop
//
    //            isResizing = true;
    //            startX = e.clientX;
    //            startWidth = parseInt(document.defaultView.getComputedStyle(column).width, 10);
    //            document.addEventListener('mousemove', doResize);
    //            document.addEventListener('mouseup', stopResize);
    //            e.preventDefault();
    //        });
//
    //        function doResize(e) {
    //            if (!isResizing || !isDesktop()) return; // Skip if not resizing or not desktop
//
    //            const width = startWidth + e.clientX - startX;
    //            if (width >= 180) { // Minimum width
    //                column.style.width = width + 'px';
    //                column.style.minWidth = width + 'px';
    //            }
    //        }
//
    //        function stopResize() {
    //            isResizing = false;
    //            document.removeEventListener('mousemove', doResize);
    //            document.removeEventListener('mouseup', stopResize);
    //        }
    //    });
//
    //});
</script>


<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Morbiditas Pasien Rawat Jalan</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('morbiditas-rawat-jalan') }}">
                @csrf
                <div class="date-form-container">
                    <div class="date-input-group">
                        <label for="tanggal_awal">Dari</label>
                        <input type="date" class="form-control" id="tanggal_awal" name="tanggal_awal" 
                               value="{{ $tanggalAwal }}">
                    </div>
                    <div class="date-input-group">
                        <label for="tanggal_akhir">Sampai</label>
                        <input type="date" class="form-control" id="tanggal_akhir" name="tanggal_akhir" 
                               value="{{ $tanggalAkhir }}">
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </div>
                </div>
            </form>

            <div class="table-wrapper mt-4">
                <table class="table table-bordered table-striped morbiditas-table">
                    <thead>
                        <!-- Baris 1: Header Utama -->
                        <tr class="header-main">
                            <th rowspan="3" class="sticky-col-1">Kode ICD</th>
                            <th rowspan="3" class="sticky-col-2">Diagnosa Penyakit</th>
                            <th colspan="48" class="text-center">Jumlah Kasus Baru Menurut Kelompok Umur & Jenis Kelamin</th>
                            <th colspan="3" rowspan="2" class="text-center">Jumlah Kasus Baru Menurut Jenis Kelamin</th>
                            <th colspan="3" rowspan="2" class="text-center">Jumlah Kunjungan</th>
                        </tr>
                        
                        <!-- Baris 2: Kelompok Umur -->
                        <tr class="header-age-group">
                            <th colspan="2" class="age-header">&lt;1 jam</th>
                            <th colspan="2" class="age-header">1-23 jam</th>
                            <th colspan="2" class="age-header">8-28 hari</th>
                            <th colspan="2" class="age-header">2-&lt;3 bln</th>
                            <th colspan="2" class="age-header">3-&lt;6 bln</th>
                            <th colspan="2" class="age-header">6-&lt;11 bln</th>
                            <th colspan="2" class="age-header">1-4 th</th>
                            <th colspan="2" class="age-header">5-9 th</th>
                            <th colspan="2" class="age-header">10-14 th</th>
                            <th colspan="2" class="age-header">15-19 th</th>
                            <th colspan="2" class="age-header">20-24 th</th>
                            <th colspan="2" class="age-header">25-29 th</th>
                            <th colspan="2" class="age-header">30-34 th</th>
                            <th colspan="2" class="age-header">35-39 th</th>
                            <th colspan="2" class="age-header">40-44 th</th>
                            <th colspan="2" class="age-header">45-49 th</th>
                            <th colspan="2" class="age-header">50-54 th</th>
                            <th colspan="2" class="age-header">55-59 th</th>
                            <th colspan="2" class="age-header">60-64 th</th>
                            <th colspan="2" class="age-header">65-69 th</th>
                            <th colspan="2" class="age-header">70-74 th</th>
                            <th colspan="2" class="age-header">75-79 th</th>
                            <th colspan="2" class="age-header">80-84 th</th>
                            <th colspan="2" class="age-header">&gt;=85 th</th>
                        </tr>
                        
                        <!-- Baris 3: Jenis Kelamin -->
                        <tr class="header-gender">
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>

                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">Total</th>
                            <th class="gender-header">L</th>
                            <th class="gender-header">P</th>
                            <th class="gender-header">Total</th></tr>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                            <tr>
                                <td class="sticky-col-1">{{ $item->kd_penyakit }}</td>
                                <td class="sticky-col-2">{{ $item->nm_penyakit }}</td>
                                <td class="data-cell">{{ $item->kurang_1hr_L }}</td>
                                <td class="data-cell">{{ $item->kurang_1hr_P }}</td>
                                <td class="data-cell">{{ $item->age_1_23hr_L }}</td>
                                <td class="data-cell">{{ $item->age_1_23hr_P }}</td>
                                <td class="data-cell">{{ $item->age_8_28hr_L }}</td>
                                <td class="data-cell">{{ $item->age_8_28hr_P }}</td>
                                <td class="data-cell">{{ $item->age_2_3bln_L }}</td>
                                <td class="data-cell">{{ $item->age_2_3bln_P }}</td>
                                <td class="data-cell">{{ $item->age_3_6bln_L }}</td>
                                <td class="data-cell">{{ $item->age_3_6bln_P }}</td>
                                <td class="data-cell">{{ $item->age_6_11bln_L }}</td>
                                <td class="data-cell">{{ $item->age_6_11bln_P }}</td>
                                <td class="data-cell">{{ $item->age_1_4th_L }}</td>
                                <td class="data-cell">{{ $item->age_1_4th_P }}</td>
                                <td class="data-cell">{{ $item->age_5_9_L }}</td>
                                <td class="data-cell">{{ $item->age_5_9_P }}</td>
                                <td class="data-cell">{{ $item->age_10_14_L }}</td>
                                <td class="data-cell">{{ $item->age_10_14_P }}</td>
                                <td class="data-cell">{{ $item->age_15_19_L }}</td>
                                <td class="data-cell">{{ $item->age_15_19_P }}</td>
                                <td class="data-cell">{{ $item->age_20_24_L }}</td>
                                <td class="data-cell">{{ $item->age_20_24_P }}</td>
                                <td class="data-cell">{{ $item->age_25_29_L }}</td>
                                <td class="data-cell">{{ $item->age_25_29_P }}</td>
                                <td class="data-cell">{{ $item->age_30_34_L }}</td>
                                <td class="data-cell">{{ $item->age_30_34_P }}</td>
                                <td class="data-cell">{{ $item->age_35_39_L }}</td>
                                <td class="data-cell">{{ $item->age_35_39_P }}</td>
                                <td class="data-cell">{{ $item->age_40_44_L }}</td>
                                <td class="data-cell">{{ $item->age_40_44_P }}</td>
                                <td class="data-cell">{{ $item->age_45_49_L }}</td>
                                <td class="data-cell">{{ $item->age_45_49_P }}</td>
                                <td class="data-cell">{{ $item->age_50_54_L }}</td>
                                <td class="data-cell">{{ $item->age_50_54_P }}</td>
                                <td class="data-cell">{{ $item->age_55_59_L }}</td>
                                <td class="data-cell">{{ $item->age_55_59_P }}</td>
                                <td class="data-cell">{{ $item->age_60_64_L }}</td>
                                <td class="data-cell">{{ $item->age_60_64_P }}</td>
                                <td class="data-cell">{{ $item->age_65_69_L }}</td>
                                <td class="data-cell">{{ $item->age_65_69_P }}</td>
                                <td class="data-cell">{{ $item->age_70_74_L }}</td>
                                <td class="data-cell">{{ $item->age_70_74_P }}</td>
                                <td class="data-cell">{{ $item->age_75_79_L }}</td>
                                <td class="data-cell">{{ $item->age_75_79_P }}</td>
                                <td class="data-cell">{{ $item->age_80_84_L }}</td>
                                <td class="data-cell">{{ $item->age_80_84_P }}</td>
                                <td class="data-cell">{{ $item->lebih_85_L }}</td>
                                <td class="data-cell">{{ $item->lebih_85_P }}</td>
                                <td class="total-col">{{ $item->total_L }}</td>
                                <td class="total-col">{{ $item->total_P }}</td>
                                <td class="total-col">{{ $item->total_kasus_baru }}</td>
                                <td class="total-col">{{ $item->kunjungan_L }}</td>
                                <td class="total-col">{{ $item->kunjungan_P }}</td>
                                <td class="total-col">{{ $item->total_kunjungan }}</td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
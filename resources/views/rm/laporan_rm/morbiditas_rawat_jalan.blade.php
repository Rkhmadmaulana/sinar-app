@extends('layout.app')

@section('content')
<style>
    /* Form styling */
    .date-form-container {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .date-input-group {
        display: flex;
        align-items: center;
        white-space: nowrap;
    }

    .date-input-group label {
        margin-right: 0.5rem;
        margin-bottom: 0;
        min-width: 50px;
    }

    .date-input-group input[type="date"] {
        width: 150px;
    }

    /* Table container with scroll */
    .table-container {
        border: 1px solid #dee2e6;
        overflow: auto;
        height: 70vh;
        position: relative;
    }

    /* Main table styling */
    .morbiditas-table {
        border-collapse: collapse;
        width: 100%;
        min-width: 2300px;
        font-size: 0.85rem;
        table-layout: fixed;
    }

    .morbiditas-table th,
    .morbiditas-table td {
        padding: 8px 6px;
        text-align: center;
        border: 1px solid #dee2e6;
        vertical-align: middle;
        white-space: nowrap;
    }

    /* Sticky header */
    .morbiditas-table th {
        background-color: lightseagreen;
        color: white;
        font-weight: bold;
        /*position: sticky;
        top: 0;*/
        z-index: 3;
    }

    /* Age group headers */
    .header-age-group {
        background-color: lightseagreen !important;
        color: white;
        font-weight: 600;
    }

    /* Gender headers */
    .header-gender {
        background-color: lightseagreen !important;
        color: white;
        font-weight: 600;
    }

    /* Header z-index override for frozen columns */
    
    /* Data cells */
    .data-cell {
        min-width: 35px;
        max-width: 35px;
        font-size: 0.8rem;
    }

    .columm-header-parent{
        position:sticky;
        top:0px;
    }

    /* Total columns */
    .total-col {
        background-color: #fff3cd;
        font-weight: bold;
        min-width: 60px;
    }

    /* Age and gender headers sizing */
    .age-header {
        font-size: 0.75rem;
        min-width: 70px;
        padding: 6px 4px;
        position:sticky;
        top:38px;
    }

    .gender-header {
        font-size: 0.8rem;
        min-width: 35px;
        padding: 6px 4px;
        position:sticky;
        top:73px;
    }

    /* Row hover effect */
    .morbiditas-table tbody tr:hover {
        background-color: #f5f5f5;
    }

    .morbiditas-table tbody tr:hover td:nth-child(1),
    .morbiditas-table tbody tr:hover td:nth-child(2) {
        background-color: #e9ecef;
    }

    /* Resize handle for second column */
    .morbiditas-table th:nth-child(2)::after {
        content: '';
        position: absolute;
        right: -3px;
        top: 0;
        width: 6px;
        height: 100%;
        cursor: col-resize;
        background: rgba(0, 0, 0, 0.1);
        opacity: 0;
        transition: opacity 0.2s;
    }

    .morbiditas-table th:nth-child(2):hover::after {
        opacity: 1;
    }

    /* Responsive design */
    @media (min-width: 576px) and (max-width: 768px) {
        .date-form-container {
            flex-direction: column;
            align-items: stretch;
        }
        
        .date-input-group {
            width: 100%;
        }
        
        .date-input-group input[type="date"] {
            width: 100%;
        }

        .morbiditas-table {
            font-size: 0.75rem;
        }

        .table-container {
            height: 60vh;
        }

        .column-icd {
            position: static;
            box-shadow: none;
        }

        .morbiditas-table td:nth-child(2),
        .morbiditas-table th:nth-child(2) {
            box-shadow: none;
            resize: none;
        }

        th.age-header{
            top:35px !important;
        }

        th.gender-header{
            top:70px !important;
        }

        .column-diagnosa{
            position: static !important;
        }
        
        .morbiditas-table th:nth-child(2)::after{
            display:none;
        }
    }

    @media (max-width: 576px) {
        .date-input-group {
            flex-direction: column;
            align-items: stretch;
        }
        
        .date-input-group label {
            margin-right: 0;
            margin-bottom: 0.25rem;
        }

        .morbiditas-table {
            font-size: 0.7rem;
        }

        .table-container {
            height: 50vh;
        }

        .data-cell {
            min-width: 30px;
            max-width: 30px;
            padding: 4px 2px;
        }
        .age-header{
            font-size: 0.7rem;
            top: 0px
        }
        .gender-header{
            font-size: 0.6rem;
            top: 34px
        }
        th.column-icd{
            top:-34px !important;
        }
        .column-diagnosa{
            position: static !important;
        }
        .morbiditas-table th:nth-child(2)::after{
            display:none;
        }
        .columm-header-parent{
            top:-15px;
        }
    }
    .column-icd{
        width:100px;
        /*background-color: #f8f9fa;*/
        position: sticky;
        left: 0px;
        
        /*z-index: 5;*/
        min-width: 80px;
        max-width: 80px;
        font-weight: bold;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1);
    }
    th.column-icd{
        top: 0px;
        background-color: lightseagreen !important;
        z-index:10;
    }
    .column-diagnosa{
        width:300px;
        /*background-color: #f8f9fa;*/
        position: sticky;
        left: 80px;
        
        /*z-index: 5;*/
        min-width: 250px;
        max-width: 250px;
        text-align: left !important;
        font-weight: 600;
        box-shadow: 2px 0 5px rgba(0,0,0,0.1) !important;
        resize: horizontal;
        overflow: hidden;
    }
    th.column-diagnosa{
        top:0px !important;
        background-color: lightseagreen !important;
        z-index:10;
    }
</style>

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

            <div class="table-container">
                <table class="table morbiditas-table">
                    <thead>
                        <!-- Baris 1: Header Utama -->
                        <tr>
                            <th rowspan="3" class="column-icd">Kode ICD</th>
                            <th rowspan="3" class="column-diagnosa">Diagnosa Penyakit</th>
                            <th colspan="48" class="columm-header-parent">Jumlah Kasus Baru Menurut Kelompok Umur & Jenis Kelamin</th>
                            <th colspan="3" rowspan="2" class="columm-header-parent" width="200px">Jumlah Kasus Baru<br> Menurut Jenis Kelamin</th>
                            <th colspan="3" rowspan="2" class="columm-header-parent" width="200px">Jumlah Kunjungan</th>
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
                            <th class="gender-header">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                            <tr>
                                <td class="column-icd">{{ $item->kd_penyakit }}</td>
                                <td class="column-diagnosa">{{ $item->nm_penyakit }}</td>
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
                                <td class="total-col" style="background-color: papayawhip;">{{ $item->total_L }}</td>
                                <td class="total-col" style="background-color: papayawhip;">{{ $item->total_P }}</td>
                                <td class="total-col" style="background-color:gainsboro;">{{ $item->total_kasus_baru }}</td>
                                <td class="total-col" style="background-color: papayawhip;">{{ $item->kunjungan_L }}</td>
                                <td class="total-col" style="background-color: papayawhip;">{{ $item->kunjungan_P }}</td>
                                <td class="total-col" style="background-color:gainsboro;">{{ $item->total_kunjungan }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const table = document.querySelector('.morbiditas-table');
    const resizableColumn = table.querySelector('th:nth-child(2)');
    
    let isResizing = false;
    let startX, startWidth;

    resizableColumn.addEventListener('mousedown', function(e) {
        const rect = this.getBoundingClientRect();
        if (e.clientX > rect.right - 10) {
            isResizing = true;
            startX = e.pageX;
            startWidth = parseInt(window.getComputedStyle(this).width, 10);
            document.body.style.cursor = 'col-resize';
            e.preventDefault();
        }
    });

    document.addEventListener('mousemove', function(e) {
        if (!isResizing) return;
        
        const newWidth = startWidth + (e.pageX - startX);
        if (newWidth > 150) { // Minimum width
            resizableColumn.style.width = `${newWidth}px`;
            resizableColumn.style.maxWidth = `${newWidth}px`;
            resizableColumn.style.minWidth = `${newWidth}px`;
            
            // Update all cells in the second column
            const cells = table.querySelectorAll('td:nth-child(2)');
            cells.forEach(cell => {
                cell.style.width = `${newWidth}px`;
                cell.style.maxWidth = `${newWidth}px`;
                cell.style.minWidth = `${newWidth}px`;
            });
            
            // Update left position for sticky elements if needed
            const stickyElements = table.querySelectorAll('td:nth-child(2), th:nth-child(2)');
            stickyElements.forEach(element => {
                element.style.left = '80px';
            });
        }
    });

    document.addEventListener('mouseup', function() {
        if (isResizing) {
            isResizing = false;
            document.body.style.cursor = 'default';
        }
    });
});
</script>

@endsection
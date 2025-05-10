@extends('layout.app')
@section('content')

<head>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<style>
    th,
    td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    #toast {
        visibility: hidden;
        min-width: 250px;
        margin-left: -125px;
        background-color:rgb(13, 110, 253);
        color: white;
        text-align: center;
        border-radius: 5px;
        padding: 10px;
        position: fixed;
        z-index: 999;
        right: 30px;
        top: 30px;
        font-size: 16px;
    }

    #toast.show {
        visibility: visible;
        -webkit-animation: fadein 0.5s, fadeout 0.5s 2.5s;
        animation: fadein 0.5s, fadeout 0.5s 2.5s;
    }

    @keyframes fadein {
        from { top: 0; opacity: 0; }
        to { top: 30px; opacity: 1; }
    }

    @keyframes fadeout {
        from { top: 30px; opacity: 1; }
        to { top: 0; opacity: 0; }
    }
</style>

<div id="toast">Verifikasi berhasil disimpan!</div>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between pb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <form id="filterForm" action="{{ route('kelengkapan') }}" method="POST">
                                @csrf
                                <div class="row clearfix">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-line">
                                                <dt>Dari Tanggal</dt>
                                                <dd>
                                                    @if (isset($tgl1))
                                                        <input type="date" value="{{ $tgl1 }}"
                                                            class="form-control" name="tgl1">
                                                    @else
                                                        <input type="date" class="form-control" name="tgl1">
                                                    @endif
                                                </dd>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <div class="form-line">
                                                <dt>Sampai Tanggal</dt>
                                                <dd>
                                                    @if (isset($tgl2))
                                                        <input type="date" value="{{ $tgl2 }}"
                                                            class="form-control" name="tgl2">
                                                    @else
                                                        <input type="date" class="form-control" name="tgl2">
                                                    @endif
                                                </dd>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <dt>&ensp;</dt>
                                            <dd>
                                                <button type="submit" name="tombol" value="filter"
                                                    class="btn btn-primary">Filter</button>
                                                
                                                </dd>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('rm.laporan_rm.layout.menu_laporan')
    </div>

    <br>

    <div class="row">
        <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <center>LAPORAN<br>KELENGKAPAN REKAM MEDIS PASIEN RAWAT INAP <br>{{ $tgllap }}
                    </center>
                    <small style="color:red;">*Data dibawah ini berdasarkan Tanggal Registrasi Pasien</small><br><br>
                    <div class="table-responsive">
                        <table id="kelengkapan" class="table table-bordered table-striped" style="width:100%;">
                            <thead>
                                <tr>
                                    <th >No. Rawat</th>
                                    <th >No. RM</th>
                                    <th >Nama Pasien</th>
                                    <th >Kamar Inap</th>
                                    <th >Status</th>
                                    <th >Status Berkas</th>
                                    <th >Aksi </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($nmr_rwt  as $a)
                                    <tr>
                                        <td >{{ $a->no_rawat }}</td>
                                        <td style="text-align: center;">
                                            {{ $a->no_rkm_medis }}
                                        </td>
                                        <td style="text-align: center;">
                                            {{ $a->nm_pasien }}
                                        </td>
                                        <td style="text-align: center;">
                                            {{ $a->nm_bangsal }}
                                        </td>
                                        <td style="text-align: center;">
                                            {{ $a->status_lanjut }}
                                        </td>
                                        <td class="status-verifikasi" style="text-align: center;">
                                            @if($a->verif_all == 1)
                                                <span class="badge bg-success">Terverifikasi ✅</span><br>
                                            @else
                                                <button class="btn btn-danger btn-sm verifikasiBtn" data-id="{{ $a->no_rawat }}" data-rkm="{{ $a->no_rkm_medis }}">Verifikasi</button>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <a href="{{route('modalrm', ['id' => $a->no_rawat])}}" id="openModal" class="btn btn-primary openModal" data-toggle="modal"
                                            data-target="#ermModal">Detail</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>

<div class="modal fade" id="ermModal" tabindex="-1" role="dialog" aria-labelledby="ermModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ermModalLabel">Detail Laporan RM</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-body-content">
                Loading...
            </div>
        </div>
    </div>
</div>

<script>
    function showToast(message) {
        const toast = document.getElementById("toast");
        toast.textContent = message;
        toast.className = "show";
        setTimeout(() => {
            toast.className = toast.className.replace("show", "");
        }, 3000);
    }

    // ✅ Verifikasi (delegated)
    $(document).on('click', '.verifikasiBtn', function () {
        console.log('Tombol Verifikasi diklik'); // Debug
        const noRawat = $(this).data('id');
        const noRkmMedis = $(this).data('rkm');
        const $btn = $(this);

        $.ajax({
            url: 'kelengkapan/simpan',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                no_rawat: noRawat,
                no_rkm_medis: noRkmMedis,
                verif_all_override: true
            },
            success: function () {
                showToast('Verifikasi berhasil disimpan!');
                const $row = $btn.closest('tr');
                $row.find('.status-verifikasi').html(`
                    <span class="badge bg-success">Terverifikasi ✅</span><br>
                `);
            },
            error: function () {
                alert('Gagal menyimpan verifikasi.');
            }
        });
    });

    // ✅ Batal Verifikasi (delegated)
    $(document).on('click', '.batalVerifikasi', function () {
        const noRawat = $(this).data('id');
        const $btn = $(this);

        if (confirm("Anda yakin ingin membatalkan verifikasi?")) {
            $.ajax({
                url: 'kelengkapan/simpan',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    no_rawat: noRawat,
                    no_rkm_medis: noRkmMedis,
                    verif_all_override: false
                },
                success: function () {
                    showToast('Verifikasi dibatalkan.');
                    const $row = $btn.closest('tr');
                    $row.find('.status-verifikasi').html(`
                        <button class="btn btn-sm btn-danger verifikasiBtn" data-id="{{ $a->no_rawat }}" data-rkm="{{ $a->no_rkm_medis }}">Verifikasi</button>
                    `);
                },
                error: function () {
                    alert('Gagal membatalkan verifikasi.');
                }
            });
        }
    });

    // ✅ Modal Handler
    $(document).on('click', '.openModal', function (e) {
        e.preventDefault();
        const url = $(this).attr('href');
        $('#modal-body-content').html('Loading...');
        $('#ermModal').modal('show');

        $.get(url, function (res) {
            $('#modal-body-content').html(res);

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#formKelengkapan').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const action = form.attr('action');
                const data = form.serialize();

                $.ajax({
                    type: 'POST',
                    url: action,
                    data: data,
                    dataType: 'json',
                    success: function () {
                        alert('Berhasil simpan');

                        const noRawat = form.find('input[name="no_rawat"]').val();
                        let $row = $(`.verifikasiBtn[data-id="{{ $a->no_rawat }}" data-rkm="{{ $a->no_rkm_medis }}"]`).closest('tr');

                        if ($row.length === 0) {
                            $row = $(`.batalVerifikasi[data-id="{{ $a->no_rawat }}" data-rkm="{{ $a->no_rkm_medis }}"]`).closest('tr');
                        }

                        // Ubah ke status belum terverifikasi
                        $row.find('.status-verifikasi').html(`
                            <button class="btn btn-sm btn-success verifikasiBtn" data-id="{{ $a->no_rawat }}" data-rkm="{{ $a->no_rkm_medis }}">Verifikasi</button>
                        `);

                        $('#ermModal').modal('hide');
                        showToast("Data berhasil disimpan dan status diperbarui.");
                    },
                    error: function (xhr) {
                        alert('Gagal: ' + xhr.responseText);
                    }
                });
            });
        });
    });
</script>


@endsection
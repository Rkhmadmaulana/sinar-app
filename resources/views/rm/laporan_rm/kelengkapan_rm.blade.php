@extends('layout.app')
@section('content')

<head>
<meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<style>
    th, td {
        padding: 8px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    #toast {
        visibility: hidden;
        min-width: 250px;
        margin-left: -125px;
        background-color: rgb(13, 110, 253);
        color: white;
        text-align: center;
        border-radius: 5px;
        padding: 10px;
        position: fixed;
        z-index: 9999; /* Higher than modal z-index (1055) */
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

    /* Fix modal backdrop issue */
    .modal-backdrop {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        z-index: 1050 !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
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
                                                        <input type="date" value="{{ $tgl1 }}" class="form-control" name="tgl1">
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
                                                        <input type="date" value="{{ $tgl2 }}" class="form-control" name="tgl2">
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
                                                <button type="submit" name="tombol" value="filter" class="btn btn-primary">Filter</button>
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
                    <center>LAPORAN<br>KELENGKAPAN REKAM MEDIS PASIEN RAWAT INAP <br>{{ $tgllap }}</center>
                    <small style="color:red;">*Data dibawah ini berdasarkan Tanggal Registrasi Pasien</small><br><br>
                    <div class="table-responsive">
                        <table id="kelengkapan" class="table table-bordered table-striped" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>No. Rawat</th>
                                    <th>No. RM</th>
                                    <th>Nama Pasien</th>
                                    <th>Kamar Inap</th>
                                    <th>Status</th>
                                    <th>Status Berkas</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($nmr_rwt as $a)
                                    <tr>
                                        <td>{{ $a->no_rawat }}</td>
                                        <td style="text-align: center;">{{ $a->no_rkm_medis }}</td>
                                        <td style="text-align: center;">{{ $a->nm_pasien }}</td>
                                        <td style="text-align: center;">{{ $a->nm_bangsal }}</td>
                                        <td style="text-align: center;">{{ $a->status_lanjut }}</td>
                                        <td class="status-verifikasi" style="text-align: center;">
                                            @if($a->verif_all == 1)
                                                <span class="badge bg-success">Terverifikasi ✅</span><br>
                                            @else
                                                <button class="btn btn-danger btn-sm verifikasiBtn" data-id="{{ $a->no_rawat }}" data-rkm="{{ $a->no_rkm_medis }}">Verifikasi</button>
                                            @endif
                                        </td>
                                        <td style="text-align: center;">
                                            <button class="btn btn-primary btn-detail" data-url="{{route('modalrm', ['id' => $a->no_rawat])}}">Detail</button>
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

<!-- Modal -->
<div class="modal fade" id="ermModal" tabindex="-1" aria-labelledby="ermModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ermModalLabel">Detail Laporan RM</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-body-content">
                Loading...
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Global modal instance
    let modalInstance = null;

    // Toast function with type support
    function showToast(message, type = 'success') {
        const toast = document.getElementById("toast");
        toast.textContent = message;
        
        // Change color based on type
        if (type === 'error') {
            toast.style.backgroundColor = '#dc3545'; // Bootstrap danger color
        } else if (type === 'warning') {
            toast.style.backgroundColor = '#ffc107'; // Bootstrap warning color
            toast.style.color = '#000'; // Black text for better contrast
        } else {
            toast.style.backgroundColor = 'rgb(13, 110, 253)'; // Default blue
            toast.style.color = 'white';
        }
        
        toast.className = "show";
        setTimeout(() => {
            toast.className = toast.className.replace("show", "");
        }, 3000);
    }

    // Verifikasi button handler
    $(document).on('click', '.verifikasiBtn', function() {
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
            success: function() {
                showToast('Verifikasi berhasil disimpan!');
                const $row = $btn.closest('tr');
                $row.find('.status-verifikasi').html('<span class="badge bg-success">Terverifikasi ✅</span><br>');
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                let errorMessage = 'Gagal menyimpan verifikasi';
                
                if (xhr.status === 403) {
                    errorMessage = 'Anda tidak memiliki akses untuk melakukan verifikasi.';
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                showToast(errorMessage, 'error');
            }
        });
    });

    // Detail button handler
    $(document).on('click', '.btn-detail', function() {
        const url = $(this).data('url');
        
        // Reset modal content
        $('#modal-body-content').html('Loading...');
        
        // Create/show modal using Bootstrap 5
        const modalElement = document.getElementById('ermModal');
        modalInstance = new bootstrap.Modal(modalElement, {
            backdrop: true,  // Allow clicking outside to close
            keyboard: true   // Allow ESC key to close
        });
        modalInstance.show();

        // Load content
        $.get(url)
            .done(function(response) {
                $('#modal-body-content').html(response);
                
                // Setup CSRF token for the loaded content
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                
                // Handle form submission in modal
                $('#formKelengkapan').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    const form = $(this);
                    const action = form.attr('action');
                    const data = form.serialize();

                    $.ajax({
                        type: 'POST',
                        url: action,
                        data: data,
                        dataType: 'json',
                        success: function(response) {
                            showToast('Data berhasil disimpan dan status diperbarui.');
                            
                            // Close modal using Bootstrap 5 method
                            if (modalInstance) {
                                modalInstance.hide();
                            }
                            
                            // Update status if needed
                            const noRawat = form.find('input[name="no_rawat"]').val();
                            const noRkmMedis = form.find('input[name="no_rkm_medis"]').val();
                            const $row = $(`.verifikasiBtn[data-id="${noRawat}"]`).closest('tr');
                            
                            if ($row.length > 0) {
                                $row.find('.status-verifikasi').html(`
                                    <button class="btn btn-sm btn-danger verifikasiBtn" data-id="${noRawat}" data-rkm="${noRkmMedis}">Verifikasi</button>
                                `);
                            }
                        },
                        error: function(xhr) {
                            console.error('Error:', xhr);
                            let errorMessage = 'Gagal menyimpan data';
                            
                            // Handle specific error cases
                            if (xhr.status === 403) {
                                errorMessage = 'Anda tidak memiliki akses untuk melakukan tindakan ini.';
                            } else if (xhr.status === 422) {
                                // Validation errors
                                if (xhr.responseJSON && xhr.responseJSON.errors) {
                                    const errors = Object.values(xhr.responseJSON.errors).flat();
                                    errorMessage = 'Validasi gagal: ' + errors.join(', ');
                                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                }
                            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseText) {
                                try {
                                    const response = JSON.parse(xhr.responseText);
                                    if (response.message) {
                                        errorMessage = response.message;
                                    }
                                } catch (e) {
                                    errorMessage = 'Terjadi kesalahan pada server';
                                }
                            }
                            
                            // Use showToast for error messages too
                            showToast(errorMessage, 'error');
                        }
                    });
                });
            })
            .fail(function(xhr) {
                console.error('Failed to load modal content:', xhr);
                $('#modal-body-content').html('<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>');
            });
    });

    // Handle modal hidden event
    $('#ermModal').on('hidden.bs.modal', function () {
        modalInstance = null;
        $('#modal-body-content').html('Loading...');
    });
});
</script>

@endpush
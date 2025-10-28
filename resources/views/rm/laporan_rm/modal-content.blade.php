<div class="row">
    <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <center>LAPORAN<br>KELENGKAPAN CATATAN REKAM MEDIS 
                <br>{{ $data->no_rawat }}
                <br>{{ $data->nm_pasien }} - {{ $data->no_rkm_medis }}
                <a href="{{route('erm_ranap', ['id' => $data->no_rawat])}}" id="openModal" class="btn btn-primary" target="_blank">ERM</a>
                </center>
                <br>
                
                <form id="formKelengkapan" method="POST" action="{{ route('kelengkapan.simpan') }}">
                    @csrf
                    <input type="hidden" name="no_rawat" value="{{ $data->no_rawat }}">
                    <input type="hidden" name="no_rkm_medis" value="{{ $data->no_rkm_medis }}">

                    <!-- Berkas Umum -->
                    <div class="card mb-3">
                        <div class="card-header">
                            <h6 class="mb-0">
                                <i class="bi bi-file-medical text-primary me-2"></i>
                                Berkas Umum
                            </h6>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nama Berkas</th>
                                        <th class="text-center">L/TL</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $allowedUsers = ['199305082020122015', '198611162020122005', '23.05.034', 'ridahayati'];
                                        $isUserAllowed = in_array($loggedInUserNip, $allowedUsers);
                                    @endphp
                                    
                                    @foreach ($list as $field => $info)
                                        <tr>
                                            <td>
                                                <a href="{{ route($info['route'], ['id' => $data->no_rawat]) }}" target="_blank" style="color: black;">
                                                    {{ $info['label'] }}
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                @if ($isUserAllowed)
                                                    {{-- User yang diizinkan bisa mencentang --}}
                                                    <input type="checkbox" 
                                                           name="{{ $field }}" 
                                                           {{ isset($kelengkapan->$field) && $kelengkapan->$field ? 'checked' : '' }}>
                                                @else
                                                    {{-- User lain hanya bisa lihat (disabled) --}}
                                                    <input type="checkbox" 
                                                           {{ isset($kelengkapan->$field) && $kelengkapan->$field ? 'checked' : '' }}
                                                           disabled
                                                           style="pointer-events: none; opacity: 0.6;">
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Berkas Operasi -->
                    <div class="card mb-3">
                        <div class="card-header" 
                             data-bs-toggle="collapse" 
                             data-bs-target="#operasiSection" 
                             aria-expanded="{{ $isOperasi ? 'true' : 'false' }}" 
                             aria-controls="operasiSection"
                             style="cursor: pointer;">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <i class="bi bi-scissors text-primary me-2"></i>
                                    Berkas Operasi
                                    @if($isOperasi)
                                        <span class="badge bg-success ms-2">Pasien Operasi</span>
                                    @else
                                        <span class="badge bg-secondary ms-2">Non Operasi</span>
                                    @endif
                                </h6>
                                <i class="bi bi-chevron-{{ $isOperasi ? 'up' : 'down' }}"></i>
                            </div>
                        </div>
                        
                        <div class="collapse {{ $isOperasi ? 'show' : '' }}" id="operasiSection">
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Nama Berkas</th>
                                            <th class="text-center">L/TL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($isOperasi)
                                            @foreach ($listOperasi as $key => $item)
                                                <tr>
                                                    <td>
                                                        @if($item['route'] !== '#')
                                                            <a href="{{ route($item['route'], ['id' => $data->no_rawat]) }}" target="_blank" style="color: black;">
                                                                {{ $item['label'] }}
                                                            </a>
                                                        @else
                                                            <span style="color: black;">{{ $item['label'] }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if ($isUserAllowed)
                                                            <input type="checkbox" 
                                                                   name="{{ $key }}" 
                                                                   {{ isset($kelengkapan->$key) && $kelengkapan->$key == 1 ? 'checked' : '' }}>
                                                        @else
                                                            <input type="checkbox" 
                                                                   {{ isset($kelengkapan->$key) && $kelengkapan->$key == 1 ? 'checked' : '' }}
                                                                   disabled
                                                                   style="pointer-events: none; opacity: 0.6;">
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            @foreach ($listOperasi as $key => $item)
                                                <tr>
                                                    <td>
                                                        <span class="text-muted">
                                                            {{ $item['label'] }}
                                                            <small class="ms-2">(Tidak berlaku untuk non-operasi)</small>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="checkbox" disabled style="pointer-events: none; opacity: 0.6;">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-3">
                        @if ($isUserAllowed)
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Simpan
                            </button>
                        @else
                            <div class="alert alert-info mb-0 text-center">
                                <i class="bi bi-info-circle me-1"></i>
                                Anda hanya dapat melihat status kelengkapan
                            </div>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 

<script>
$(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Handle collapsible chevron rotation
    $(document).on('click', '[data-bs-toggle="collapse"]', function() {
        const chevron = $(this).find('.bi-chevron-down, .bi-chevron-up');
        chevron.toggleClass('bi-chevron-down bi-chevron-up');
    });

    // Reset non-operasi checkboxes jika tidak sengaja tercentang
    @if(!$isOperasi)
        $('#operasiSection input[type="checkbox"]:not([disabled])').prop('checked', false);
    @endif
});
</script>
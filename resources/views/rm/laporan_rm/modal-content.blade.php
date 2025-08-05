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
                {{-- <small style="color:red;">*Data dibawah ini berdasarkan Tanggal Registrasi Pasien</small><br><br> --}}
                <form id="formKelengkapan" method="POST" action="{{ route('kelengkapan.simpan') }}">
                    @csrf
                    <input type="hidden" name="no_rawat" value="{{ $data->no_rawat }}">
                    <input type="hidden" name="no_rkm_medis" value="{{ $data->no_rkm_medis }}">

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama Berkas</th>
                                @php
                                    $allowedUsers = ['199305082020122015', '198611162020122005', '23.05.034', 'ridahayati'];
                                    $isUserAllowed = in_array($loggedInUserNip, $allowedUsers);
                                @endphp

                                @if ($isUserAllowed)
                                    <th>L/TL</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($list as $field => $info)
                                <tr>
                                    <td>
                                        <a href="{{ route($info['route'], ['id' => $data->no_rawat]) }}" target="_blank" style="color: black;">
                                            {{ $info['label'] }}
                                        </a>
                                    </td>
                                    @if ($isUserAllowed)
                                        <td>
                                            <input type="checkbox" name="{{ $field }}" {{ isset($kelengkapan->$field) && $kelengkapan->$field ? 'checked' : '' }}>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-end mt-3">
                        @if ($isUserAllowed)
                            <button type="submit" class="btn btn-primary">Simpan</button>
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

        // Hapus event handler yang ada di modal-content karena sudah dihandle di parent
        // Form submission akan dihandle oleh kelengkapan_rm.blade.php
        // - NAUFAL -

        //https://code.jquery.com/jquery-3.6.0.min.js hapus juga ini.. sudah di handle di layout.app
        // Bila ga percaya coba uncomment baris di bawah ini
        // alert("Jquery sudah kebaca! \"$\" di \"$(function\" bisa dieksekusi tanpa error  ");
    });
</script>
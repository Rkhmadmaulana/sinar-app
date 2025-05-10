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
                <!-- <small style="color:red;">*Data dibawah ini berdasarkan Tanggal Registrasi Pasien</small><br><br> -->
                <form id="formKelengkapan" method="POST" action="{{ route('kelengkapan.simpan') }}">
                    @csrf
                    <input type="hidden" name="no_rawat" value="{{ $data->no_rawat }}">
                    <input type="hidden" name="no_rkm_medis" value="{{ $data->no_rkm_medis }}">

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama Berkas</th>
                                <th>L/TL</th>
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
                                    <td>
                                        <input type="checkbox" name="{{ $field }}" {{ isset($kelengkapan->$field) && $kelengkapan->$field ? 'checked' : '' }}>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="text-end mt-3">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div> 

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#formKelengkapan').on('submit', function (e) {
            e.preventDefault();

            $.post($(this).attr('action'), $(this).serialize())
                .done(function (res) {
                    alert('Berhasil disimpan');
                    $('#ermModal').modal('hide');
                })
                .fail(function (xhr) {
                    alert('Gagal menyimpan: ' + xhr.responseText);
                });
        });
    });
</script>



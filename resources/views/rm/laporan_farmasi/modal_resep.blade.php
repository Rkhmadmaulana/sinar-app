<div class="row">
    <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
        <div class="card h-100">
            <div class="card-body">
                <center style="margin-top:15px; font-size:20px;">Rincian Resep</center>
                <br>
                <div class="table-responsive">
                    <table id="kelengkapan2" class="table table-bordered table-striped" style="width:100%;">
                        <thead>
                            <tr>
                                <th class="text-center" style="background-color: #CCEBFF;">No. Resep</th>
                                <th class="text-center" style="background-color: #CCEBFF;">Nama Obat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($data)
                                <tr>
                                    <td>{{ $data->no_resep }}</td>
                                    <td class="text-left">
                                        <ul class="list-unstyled">
                                            @foreach (explode(', ', $data->daftar_nama_brng) as $nama_obat)
                                                <li>- {{ $nama_obat }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="2" class="text-center">Data tidak tersedia</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> 


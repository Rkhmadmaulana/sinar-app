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
                    <div class="table-responsive">
                        <table id="kelengkapan2" class="table table-bordered table-striped" style="width:100%;">
                            <thead>
                                <tr>
                                    <th >Nama Berkas</th>
                                    <th >L / TL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    
                                        @if ($data->status_lanjut == 'Ralan')
                                        <td> Resume Rawat Jalan </td>
                                        <td>{{ $data2 }}</td>
                                        @elseif ($data->status_lanjut == 'Ranap')
                                        <td> Resume Rawat Inap </td>
                                        <td>{{ $data3 }}</td>
                                        @else
                                            Data tidak tersedia
                                        @endif
                                    
                                </tr>
                                <tr>
                                    <td> Berkas 2 </td>
                                    <td> TL </td>
                                </tr>
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> 


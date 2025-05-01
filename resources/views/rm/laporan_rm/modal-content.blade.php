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
                                    <th>Checklist</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- <tr>
                                    @if ($data->status_lanjut == 'Ralan')
                                    <td> Resume Rawat Jalan </td>
                                    <td>{{ $data2 }}</td>
                                    @elseif ($data->status_lanjut == 'Ranap')
                                    <td> Ringkasan Pasien Keluar Rawat Inap (Resume Medis)</td>
                                    <td>{{ $data3 }}</td>
                                    @else
                                        Data tidak tersedia
                                    @endif
                                    <td><input type="checkbox" name="check_resume" value="resume"></td>

                                </tr> -->
                                <tr>
                                    <td><a href="{{route('erm_ranap_resume', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Ringkasan Pasien Keluar Rawat Inap (Resume Medis)</a></td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_persetujuan_umum', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">General Consent</a></td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> EWS Dewasa </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> EWS Anak </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> EWS Obstetri </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Partograf </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_medis_umum', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Asesmen Awal Medis</a></td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_rekonsiliasi_obat', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Rekonsiliasi Obat</a></td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> <a href="{{route('erm_ranap_cppt', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;"> CPPT </a></td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_catatan_perkembangan', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;"> Catatan Perkembangan </a></td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_cpo', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">CPO</a></td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_penunjang', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Pemeriksaan Penunjang Medis</a></td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Asesmen Kebutuhan Edukasi Dan Informasi </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Discharge Planning </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Transfer Pasien Ke Unit Penunjang </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Form DPJP </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Triase </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_medis_igd', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Asesmen Gawat Darurat</a></td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Transfer Pasien Antar Ruangan </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Observasi TTV </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Asesmen Resiko Jatuh Dewasa </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{ route('erm_ranap_resikogabungan', ['id' => $data->no_rawat]) }}" target="_blank" style="color: black;">Asesmen Resiko Pasien (DEWASA/ANAK/LANSIA)</td>
                                    <td>TL</td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_icta', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;"> Inform Consent Tindakan Anastesi </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Penandaan Pria / Perempuan </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Checklist Serah Terima Pasien Pre Operatif </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Penilaian Pra Anastesi / Sedasi </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Laporan Anastesi </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Inventaris Kasa </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Form Persetujuan Tindakan Kedokteran </td>
                                    <td> TL </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> 


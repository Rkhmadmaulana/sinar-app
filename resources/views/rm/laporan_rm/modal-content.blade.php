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
                                    <th>L/TL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><a href="{{route('erm_ranap_resume', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Ringkasan Pasien Keluar Rawat Inap (Resume Medis)</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_persetujuan_umum', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">General Consent</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_ews', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">EWS Neonatus/PEWS Anak/PEWS Dewasa/MEOWS Obstetri</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> Partograf </td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_medis_umum', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Asesmen Awal Medis</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_rekonsiliasi_obat', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Rekonsiliasi Obat</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td> <a href="{{route('erm_ranap_cppt', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;"> CPPT </a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_catatan_perkembangan', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;"> Catatan Perkembangan/Keperawatan Rawat Inap </a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_cpo', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">CPO</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_penunjang', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Pemeriksaan Penunjang Medis</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{ route('erm_edukasi_pasien_keluarga_rj', ['id' => $data->no_rawat]) }}"id="openModal" target="_blank" style="color: black;">Asesmen Kebutuhan Edukasi Dan Informasi</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{ route('erm_perencanaan_pemulangan', ['id' => $data->no_rawat]) }}"id="openModal" target="_blank" style="color: black;">Discharge Planning</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{ route('erm_dpjp', ['id' => $data->no_rawat]) }}" id="openModal"target="_blank" style="color: black;">Form DPJP</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{ route('erm_data_triase_igd', ['id' => $data->no_rawat]) }}"id="openModal" target="_blank" style="color: black;">Triase</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_medis_igd', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Asesmen Gawat Darurat</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{ route('erm_transfer_pasien_antar_ruang', ['id' => $data->no_rawat]) }}"id="openModal" target="_blank" style="color: black;">Transfer Pasien Antar Ruangan</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{ route('erm_catatan_observasi_ranap', ['id' => $data->no_rawat]) }}"id="openModal" target="_blank" style="color: black;">Observasi TTV</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{ route('erm_ranap_resikogabungan', ['id' => $data->no_rawat]) }}" target="_blank" style="color: black;">Asesmen Resiko Jatuh Anak / Dewasa / Lansia</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_ranap_icta', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Inform Consent Tindakan Anastesi</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_penandaanop', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Penandaan Pria / Perempuan</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_checklistpreop', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Checklist Serah Terima Pasien Pre Operatif</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_penilaianprean', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Penilaian Pra Anastesi / Sedasi</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_laporananestesi', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Laporan Anastesi</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_signoutsebelummenutupluka', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Inventaris Kasa</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                                <tr>
                                    <td><a href="{{route('erm_persetujuanpenolakan', ['id' => $data->no_rawat])}}" id="openModal" target="_blank" style="color: black;">Form Persetujuan Tindakan Kedokteran</a></td>
                                    <td><input type="checkbox" name="check_berkas2" value="berkas2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div> 


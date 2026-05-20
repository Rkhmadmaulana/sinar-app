{{-- Partial: Penyakit Menular --}}
<div class="partial-filter">
    <form id="filterForm" data-ajax="true" action="{{ route('penyakitmenular') }}" method="POST">
        @csrf
        <div class="filter-row">
            <div class="filter-group">
                <label>Dari Tanggal</label>
                <input type="date" name="tgl1" value="{{ $tgl1 ?? '' }}" class="form-control form-control-sm">
            </div>
            <div class="filter-group">
                <label>Sampai Tanggal</label>
                <input type="date" name="tgl2" value="{{ $tgl2 ?? '' }}" class="form-control form-control-sm">
            </div>
            <div class="filter-group btn-group">
                <button type="submit" name="tombol" value="filter" class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                <button type="submit" name="download_pdf" value="1" class="btn btn-danger"><i class="bx bx-download me-1"></i>PDF</button>
            </div>
        </div>
    </form>
</div>

<div class="partial-body">
    <div class="subsection-card">
        <div class="card-head" style="background:#343a40;">
            <i class="bx bx-plus-medical me-2"></i>PENYAKIT MENULAR DI RSUD PANGERAN JAYA SUMITRA
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Nama Penyakit</th><th>Anggota</th><th>PNS</th><th>Keluarga</th>
                            <th>Siswa Dikbang</th><th>Siswa Diktuk</th><th>Mandiri</th>
                            <th>BPJS</th><th>Lainnya</th><th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="td-green text-start fw-semibold">HIV</td>
                            <td>{{ $anggotahiv->hiv ?? 0 }}</td>
                            <td>{{ $pnshiv->hiv ?? 0 }}</td>
                            <td>{{ $kelpolrihiv->hiv ?? 0 }}</td>
                            <td>{{ $dikbanghiv->hiv ?? 0 }}</td>
                            <td>{{ $diktukhiv->hiv ?? 0 }}</td>
                            <td>{{ $umumhiv->hiv ?? 0 }}</td>
                            <td>{{ $bpjshiv }}</td>
                            <td>{{ $otherhiv->hiv ?? 0 }}</td>
                            <td class="td-red">{{ $total_hiv }}</td>
                        </tr>
                        <tr>
                            <td class="td-green text-start fw-semibold">Tuberkulosis (TB)</td>
                            <td>{{ $anggotatb->tb ?? 0 }}</td>
                            <td>{{ $pnstb->tb ?? 0 }}</td>
                            <td>{{ $kelpolritb->tb ?? 0 }}</td>
                            <td>{{ $dikbangtb->tb ?? 0 }}</td>
                            <td>{{ $diktuktb->tb ?? 0 }}</td>
                            <td>{{ $umumtb->tb ?? 0 }}</td>
                            <td>{{ $bpjstb }}</td>
                            <td>{{ $othertb->tb ?? 0 }}</td>
                            <td class="td-red">{{ $total_tb }}</td>
                        </tr>
                        <tr>
                            <td class="td-green text-start fw-semibold">Malaria</td>
                            <td>{{ $anggotamalaria->malaria ?? 0 }}</td>
                            <td>{{ $pnsmalaria->malaria ?? 0 }}</td>
                            <td>{{ $kelpolrimalaria->malaria ?? 0 }}</td>
                            <td>{{ $dikbangmalaria->malaria ?? 0 }}</td>
                            <td>{{ $diktukmalaria->malaria ?? 0 }}</td>
                            <td>{{ $umummalaria->malaria ?? 0 }}</td>
                            <td>{{ $bpjsmalaria }}</td>
                            <td>{{ $othermalaria->malaria ?? 0 }}</td>
                            <td class="td-red">{{ $total_malaria }}</td>
                        </tr>
                        <tr>
                            <td class="td-green text-start fw-semibold">DBD</td>
                            <td>{{ $anggotadbd->dbd ?? 0 }}</td>
                            <td>{{ $pnsdbd->dbd ?? 0 }}</td>
                            <td>{{ $kelpolridbd->dbd ?? 0 }}</td>
                            <td>{{ $dikbangdbd->dbd ?? 0 }}</td>
                            <td>{{ $diktukdbd->dbd ?? 0 }}</td>
                            <td>{{ $umumdbd->dbd ?? 0 }}</td>
                            <td>{{ $bpjsdbd }}</td>
                            <td>{{ $otherdbd->dbd ?? 0 }}</td>
                            <td class="td-red">{{ $total_dbd }}</td>
                        </tr>
                        <tr>
                            <td class="td-green text-start fw-semibold">PMS</td>
                            <td>{{ $anggotapms->pms ?? 0 }}</td>
                            <td>{{ $pnspms->pms ?? 0 }}</td>
                            <td>{{ $kelpolripms->pms ?? 0 }}</td>
                            <td>{{ $dikbangpms->pms ?? 0 }}</td>
                            <td>{{ $diktukpms->pms ?? 0 }}</td>
                            <td>{{ $umumpms->pms ?? 0 }}</td>
                            <td>{{ $bpjspms }}</td>
                            <td>{{ $otherpms->pms ?? 0 }}</td>
                            <td class="td-red">{{ $total_pms }}</td>
                        </tr>
                        <tr>
                            <td class="td-green text-start fw-semibold">Hepatitis</td>
                            <td>{{ $anggotahepatitis->hepatitis ?? 0 }}</td>
                            <td>{{ $pnshepatitis->hepatitis ?? 0 }}</td>
                            <td>{{ $kelpolrihepatitis->hepatitis ?? 0 }}</td>
                            <td>{{ $dikbanghepatitis->hepatitis ?? 0 }}</td>
                            <td>{{ $diktukhepatitis->hepatitis ?? 0 }}</td>
                            <td>{{ $umumhepatitis->hepatitis ?? 0 }}</td>
                            <td>{{ $bpjshepatitis }}</td>
                            <td>{{ $otherhepatitis->hepatitis ?? 0 }}</td>
                            <td class="td-red">{{ $total_hepatitis }}</td>
                        </tr>
                        <tr>
                            <td class="td-green text-start fw-semibold">Covid</td>
                            <td>{{ $anggotacovid->covid ?? 0 }}</td>
                            <td>{{ $pnscovid->covid ?? 0 }}</td>
                            <td>{{ $kelpolricovid->covid ?? 0 }}</td>
                            <td>{{ $dikbangcovid->covid ?? 0 }}</td>
                            <td>{{ $diktukcovid->covid ?? 0 }}</td>
                            <td>{{ $umumcovid->covid ?? 0 }}</td>
                            <td>{{ $bpjscovid }}</td>
                            <td>{{ $othercovid->covid ?? 0 }}</td>
                            <td class="td-red">{{ $total_covid }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <p class="table-note mt-2">*Data berdasarkan Tanggal Registrasi | Periode: {{ $tgllap }}</p>
</div>

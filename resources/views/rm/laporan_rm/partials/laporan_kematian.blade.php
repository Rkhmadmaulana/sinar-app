{{-- Partial: Kematian --}}
<div class="partial-filter">
    <form id="filterForm" data-ajax="true" action="{{ route('kematian') }}" method="POST">
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
        <input type="hidden" name="limit_diagnosa_kematian" id="hidden_limit_diagnosa_kematian" value="{{ $limitDiagnosaKematian ?? 20 }}">
    </form>
</div>

<div class="partial-body">
    {{-- Rekapitulasi per Golongan --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#343a40;">
            <i class="bx bx-dizzy me-2"></i>REKAPITULASI KEMATIAN PER GOLONGAN.
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr><th>Anggota</th><th>PNS</th><th>Keluarga</th><th>Siswa Dikbang</th>
                            <th>Siswa Diktuk</th><th>Mandiri</th><th>BPJS</th><th>Lainnya</th><th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{{ $anggota->total }}</td><td>{{ $pns->total }}</td><td>{{ $keluarga->total }}</td>
                            <td>{{ $dikbang->total }}</td><td>{{ $diktuk->total }}</td><td>{{ $umum->total }}</td>
                            <td>{{ $bpjs }}</td><td>{{ $lainnya->total }}</td>
                            <td class="td-red">{{ $total }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Per Unit --}}
    <div class="subsection-card">
        <div class="card-head" style="background:#6f42c1;">
            <i class="bx bx-building me-2"></i>REKAPITULASI KEMATIAN PER UNIT
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Rawat Inap</th><th>IGD</th><th>Total</th></tr></thead>
                    <tbody>
                        <tr>
                            <td>{{ $ranap->total2 }}</td><td>{{ $igd->total2 }}</td>
                            <td class="td-red">{{ $total2 }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Diagnosa Penyebab Kematian --}}
    <div class="subsection-card">
        <div class="card-head d-flex justify-content-between align-items-center flex-wrap" style="background:#dc3545;">
            <span><i class="bx bx-list-ul me-2"></i>{{ $limitDiagnosaKematian ?? 20 }} PENYAKIT PENYEBAB KEMATIAN PASIEN RAWAT INAP</span>
            <div class="d-flex align-items-center gap-2">
                <select id="inline_limit_diagnosa_kematian" class="form-select form-select-sm" style="width:auto;font-size:12px;padding:2px 28px 2px 8px;">
                    @foreach ([5, 10, 15, 20, 25, 30, 50] as $opt)
                        <option value="{{ $opt }}" {{ ($limitDiagnosaKematian ?? 20) == $opt ? 'selected' : '' }}>{{ $opt }} data</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-success btn-sm" onclick="downloadDiagnosaKematianExcel()">
                    <i class="bx bx-download me-1"></i>Excel
                </button>
            </div>
        </div>
        <div class="card-body-table">
            <div class="table-responsive">
                @if(!empty($diagnosaKematian) && count($diagnosaKematian) > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Diagnosa</th>
                            <th>Jumlah</th>
                            <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($diagnosaKematian as $idx => $row)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td style="text-align:left;max-width:300px;white-space:pre-line;">{{ $row->nm_penyakit }}@if(strpos($row->kd_penyakit, ',') === false) ({{ $row->kd_penyakit }})@endif</td>
                            <td>{{ $row->total }}</td>
                            <td>{{ $totalSemuaDiagnosa > 0 ? number_format($row->total / $totalSemuaDiagnosa * 100, 1) : '0' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="font-weight:bold;">
                            <td colspan="2">Jumlah</td>
                            <td class="td-red">{{ $totalSemuaDiagnosa }}</td>
                            <td>100%</td>
                        </tr>
                    </tfoot>
                </table>
                @else
                <div style="padding:20px;text-align:center;color:#888;">
                    Tidak ada data diagnosa kematian untuk periode ini.
                </div>
                @endif
            </div>
        </div>
    </div>

    <p class="table-note mt-2">*Data berdasarkan Tanggal Registrasi | Periode: {{ $tgllap }}</p>
</div>

<script>
(function(){
    var form = document.getElementById('filterForm');
    var limitSelect = document.getElementById('inline_limit_diagnosa_kematian');
    var hiddenLimit = document.getElementById('hidden_limit_diagnosa_kematian');

    if (limitSelect && hiddenLimit && form) {
        limitSelect.addEventListener('change', function() {
            hiddenLimit.value = this.value;
            form.submit();
        });
    }
})();

function downloadDiagnosaKematianExcel() {
    var form = document.getElementById('filterForm');
    if (!form) {
        alert('Form filter tidak ditemukan');
        return;
    }
    var formData = new FormData(form);
    var downloadForm = document.createElement('form');
    downloadForm.method = 'POST';
    downloadForm.action = '{{ route("kematian.download.excel") }}';
    downloadForm.style.display = 'none';
    var csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    downloadForm.appendChild(csrfInput);
    for (var [key, value] of formData.entries()) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        downloadForm.appendChild(input);
    }
    document.body.appendChild(downloadForm);
    downloadForm.submit();
    setTimeout(function() {
        document.body.removeChild(downloadForm);
    }, 1000);
}
</script>

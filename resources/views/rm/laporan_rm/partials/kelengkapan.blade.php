{{-- Partial: Filter Form --}}
<div class="partial-filter">
    <form id="filterForm" data-ajax="true" action="{{ route('kelengkapan') }}" method="POST">
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
            <div class="filter-group">
                <label>Bangsal</label>
                <select class="form-control form-control-sm" id="bangsal" name="bangsal">
                    <option value="semua">Semua Bangsal</option>
                </select>
            </div>
            <div class="filter-group btn-group">
                <button type="submit" name="tombol" value="filter" class="btn btn-primary"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                <button type="button" id="resetBtn" class="btn btn-outline-secondary">Reset</button>
                <button type="button" id="downloadExcel" class="btn btn-success"><i class="bi bi-file-earmark-excel me-1"></i>Excel</button>
            </div>
        </div>
    </form>
</div>

{{-- Partial: Summary Cards --}}
<div class="partial-body">
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:12px;padding:20px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size:2rem;font-weight:700;line-height:1;">{{ $totalData }}</div>
                        <div style="font-size:1rem;font-weight:600;opacity:.9;">Total Data</div>
                        <small style="opacity:.7;">Pasien Rawat Inap</small>
                    </div>
                    <i class="bi bi-database-fill" style="font-size:2.5rem;opacity:.25;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#11998e,#38ef7d);color:#fff;border-radius:12px;padding:20px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size:2rem;font-weight:700;line-height:1;">{{ $terverifikasi }}</div>
                        <div style="font-size:1rem;font-weight:600;opacity:.9;">Terverifikasi</div>
                        <small style="opacity:.7;">Sudah Dicek</small>
                    </div>
                    <i class="bi bi-check-circle-fill" style="font-size:2.5rem;opacity:.25;"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="background:linear-gradient(135deg,#ff6b6b,#ee5a24);color:#fff;border-radius:12px;padding:20px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div style="font-size:2rem;font-weight:700;line-height:1;">{{ $belumVerifikasi }}</div>
                        <div style="font-size:1rem;font-weight:600;opacity:.9;">Belum Diverifikasi</div>
                        <small style="opacity:.7;">Perlu Ditinjau</small>
                    </div>
                    <i class="bi bi-x-circle-fill" style="font-size:2.5rem;opacity:.25;"></i>
                </div>
            </div>
        </div>
    </div>

    @if($totalData > 0)
    <div class="card border-0 shadow-sm mb-3" style="border-radius:10px;padding:16px;background:#f8f9fa;border:1px solid #e9ecef;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span style="font-weight:600;font-size:13px;color:#495057;"><i class="bi bi-graph-up text-success me-1"></i>Progress Verifikasi</span>
            <span style="font-weight:700;font-size:1.1rem;color:#28a745;">{{ number_format(($terverifikasi / $totalData) * 100, 1) }}%</span>
        </div>
        <div style="height:10px;background:#e9ecef;border-radius:10px;overflow:hidden;">
            <div style="height:100%;width:{{ ($terverifikasi / $totalData) * 100 }}%;background:linear-gradient(90deg,#28a745,#20c997);border-radius:10px;transition:width .8s ease;"></div>
        </div>
        <small class="text-muted d-block mt-1">{{ $terverifikasi }} dari {{ $totalData }} berkas telah diverifikasi</small>
    </div>
    @endif

    {{-- Table --}}
    <div class="card border-0 shadow-sm" style="border-radius:10px;">
        <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-semibold" style="font-size:13px;"><i class="bi bi-clipboard2-check-fill text-primary me-2"></i>Laporan Kelengkapan Rekam Medis Pasien Rawat Inap</h6>
            <span class="text-muted" style="font-size:12px;">{{ $tgllap }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table id="kelengkapan" class="table table-bordered table-striped align-middle" style="font-size:13px;width:100%;margin:0;">
                    <thead>
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>No. Rawat</th>
                            <th>No. RM</th>
                            <th>Nama Pasien</th>
                            <th>Kamar Inap</th>
                            <th>Tanggal Keluar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modals --}}
<div class="modal fade" id="ermModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Detail Laporan RM</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="modal-body-content">Loading...</div></div></div></div>
<div class="modal fade" id="confirmModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Konfirmasi Pembatalan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body text-center"><i class="text-warning" style="font-size:42px;">&#9888;&#65039;</i><p class="mt-2">Yakin ingin <b>membatalkan verifikasi</b>?</p><small class="text-muted">No. Rawat: <span id="confirm-no-rawat"></span><br>No. RM: <span id="confirm-no-rkm"></span></small></div><div class="modal-footer"><button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Tidak</button><button type="button" class="btn btn-sm btn-danger" id="confirmBatalBtn">Ya, Batalkan</button></div></div></div></div>

<script>
$(function(){
    var dt, filters = { tgl1: '{{ $tgl1 ?? "" }}', tgl2: '{{ $tgl2 ?? "" }}', bangsal:'semua' };

    loadBangsal();
    initDT();

    $('#resetBtn').on('click', function(){ $('[name=tgl1]').val(''); $('[name=tgl2]').val(''); $('#bangsal').val('semua'); filters={tgl1:'',tgl2:'',bangsal:'semua'}; loadBangsal(); if(dt) dt.ajax.reload(null,false); });

    $('#downloadExcel').on('click', function(){
        var frameName = 'excel_dl_' + Date.now();
        var $iframe = $('<iframe>',{name:frameName,style:'display:none;width:0;height:0;border:none;'}).appendTo('body');

        var f = $('<form>',{method:'POST',action:'{{ route("kelengkapan.export.excel") }}',target:frameName});
        f.append($('<input>',{type:'hidden',name:'_token',value:$('meta[name="csrf-token"]').attr('content')}));
        f.append($('<input>',{type:'hidden',name:'tgl1',value:filters.tgl1 || '{{ date("Y-m-01") }}'}));
        f.append($('<input>',{type:'hidden',name:'tgl2',value:filters.tgl2 || '{{ date("Y-m-d") }}'}));
        f.append($('<input>',{type:'hidden',name:'bangsal',value:filters.bangsal}));
        f.appendTo('body').submit();

        if(window.showToast) showToast('File Excel sedang diunduh...','info');

        setTimeout(function(){ $iframe.remove(); f.remove(); }, 10000);
    });

    function loadBangsal(){
        var t1=filters.tgl1||'{{ date("Y-m-01") }}', t2=filters.tgl2||'{{ date("Y-m-d") }}';
        $.get('{{ route("kelengkapan.bangsal.bydate") }}',{tgl1:t1,tgl2:t2},function(d){
            var s=$('#bangsal'), cv=s.val(); s.empty().append('<option value="semua">Semua Bangsal</option>');
            d.forEach(function(b){ s.append('<option value="'+b.kd_bangsal+'">'+b.nm_bangsal+'</option>'); });
            if(d.some(function(b){return b.kd_bangsal===cv;})) s.val(cv);
        });
    }

    function initDT(){
        dt=$('#kelengkapan').DataTable({
            retrieve:true,
            processing:true, serverSide:false,
            ajax:{ url:'{{ route("kelengkapan.json") }}', data:function(){ return filters; }, dataSrc:'data' },
            columns:[
                {data:null,render:function(d,t,r,n){ return n.row+1+n.settings._iDisplayStart; }},
                {data:'no_rawat'},
                {data:'no_rkm_medis',className:'text-center'},
                {data:'nm_pasien'},
                {data:'nm_bangsal'},
                {data:'tgl_keluar',render:function(d){ if(!d||d==='0000-00-00') return '-'; return new Date(d).toLocaleDateString('id-ID'); }},
                {data:null,className:'text-center',render:function(d,t,r){
                    if(r.verif_all==1) return '<span class="badge bg-success verif-badge" data-id="'+r.no_rawat+'" data-rkm="'+r.no_rkm_medis+'" style="cursor:pointer;">Terverifikasi &#9989;</span>';
                    return '<button class="btn btn-danger btn-sm verifikasiBtn" data-id="'+r.no_rawat+'" data-rkm="'+r.no_rkm_medis+'">Verifikasi</button>';
                }},
                {data:null,className:'text-center',render:function(d,t,r){
                    return '<button class="btn btn-primary btn-sm btn-detail" data-url="{{ route("modalrm",["id"=>""]) }}'+r.no_rawat+'">Detail</button>';
                }}
            ],
            responsive:true, order:[[0,'desc']],
            language:{ search:"Cari:", lengthMenu:"Tampilkan _MENU_", zeroRecords:"Data tidak ditemukan", info:"_START_-_END_ dari _TOTAL_", infoEmpty:"0 data", paginate:{first:"Pertama",last:"Terakhir",next:">>",previous:"<<"} }
        });
    }

    // Destroy DT when tab switches (prevent memory leak)
    $(window).on('beforeunload', function(){ if(dt){ dt.destroy(); dt=null; } });

    // Listen for tab switch to clean up
    var origLoadTab = window.loadTab;
    // Delegate from master - we clean up via custom event
    $(document).on('destroy-kelengkapan-dt', function(){ if(dt){ dt.destroy(); $('#kelengkapan').remove(); dt=null; } });

    $(document).on('click','.verifikasiBtn',function(){
        var nr=$(this).data('id'), nrm=$(this).data('rkm');
        $.ajax({url:'{{ route("kelengkapan.simpan") }}',type:'POST',data:{_token:$('meta[name="csrf-token"]').attr('content'),no_rawat:nr,no_rkm_medis:nrm,verif_all_override:true},dataType:'json',
            success:function(){ dt.ajax.reload(null,false); if(window.showToast) showToast('Verifikasi berhasil disimpan.'); },
            error:function(x){ if(window.showToast) showToast('Gagal menyimpan verifikasi','error'); }
        });
    });

    $(document).on('click','.verif-badge',function(){
        var nr=$(this).data('id'),nrm=$(this).data('rkm');
        $('#confirm-no-rawat').text(nr); $('#confirm-no-rkm').text(nrm);
        $('#confirmBatalBtn').data('no-rawat',nr).data('no-rkm',nrm);
        new bootstrap.Modal(document.getElementById('confirmModal')).show();
    });

    $(document).on('click','#confirmBatalBtn',function(){
        var nr=$(this).data('no-rawat'),nrm=$(this).data('no-rkm');
        bootstrap.Modal.getInstance(document.getElementById('confirmModal')).hide();
        $.ajax({url:'{{ route("kelengkapan.simpan") }}',type:'POST',data:{_token:$('meta[name="csrf-token"]').attr('content'),no_rawat:nr,no_rkm_medis:nrm,verif_all_override:false},
            success:function(){ if(window.showToast) showToast('Verifikasi dibatalkan!','warning'); dt.ajax.reload(null,false); },
            error:function(){ if(window.showToast) showToast('Gagal membatalkan','error'); }
        });
    });

    $(document).on('click','.btn-detail',function(){
        var url=$(this).data('url');
        $('#modal-body-content').html('Loading...');
        var m=new bootstrap.Modal(document.getElementById('ermModal'),{backdrop:true,keyboard:true}); m.show();
        $.get(url).done(function(r){
            $('#modal-body-content').html(r);
            $.ajaxSetup({headers:{'X-CSRF-TOKEN':$('meta[name="csrf-token"]').attr('content')}});
            $('#formKelengkapan').off('submit').on('submit',function(e){
                e.preventDefault();
                $.ajax({type:'POST',url:$(this).attr('action'),data:$(this).serialize(),dataType:'json',
                    success:function(){ if(window.showToast) showToast('Data berhasil disimpan.'); m.hide(); dt.ajax.reload(null,false); },
                    error:function(){ if(window.showToast) showToast('Gagal menyimpan','error'); }
                });
            });
        }).fail(function(){ $('#modal-body-content').html('<div class="alert alert-danger">Gagal memuat data.</div>'); });
    });

    $('#ermModal').on('hidden.bs.modal',function(){ $('#modal-body-content').html('Loading...'); });
});
</script>

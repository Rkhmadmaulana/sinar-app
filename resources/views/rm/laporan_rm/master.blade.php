@extends('layout.app')

@section('title', 'Laporan Rekam Medis')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.min.css"/>
<style>
/* ─── Loading Overlay ─── */
#loading-overlay {
    display: none;
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,.75);
    z-index: 20;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    gap: 12px;
    border-radius: .375rem;
}
#loading-overlay.active { display: flex; }

.spinner-ring {
    width: 42px; height: 42px;
    border: 4px solid #e2e8f0;
    border-top-color: #0d6efd;
    border-radius: 50%;
    animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Tab Navigation ─── */
.laporan-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 12px 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #e2e8f0;
    border-radius: .375rem .375rem 0 0;
}
.laporan-tabs .tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    font-size: 13px;
    font-weight: 500;
    color: #495057;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    cursor: pointer;
    transition: all .2s ease;
    white-space: nowrap;
}
.laporan-tabs .tab-btn:hover {
    color: #0d6efd;
    border-color: #0d6efd;
    background: #e7f1ff;
}
.laporan-tabs .tab-btn.active {
    color: #fff;
    background: #0d6efd;
    border-color: #0d6efd;
    box-shadow: 0 2px 8px rgba(13,110,253,.3);
}
.laporan-tabs .tab-btn i { font-size: 15px; }

/* ─── Content Shell ─── */
.content-shell {
    position: relative;
    min-height: 400px;
    border: 1px solid #e2e8f0;
    border-top: none;
    border-radius: 0 0 .375rem .375rem;
    background: #fff;
}

/* ─── NProgress override ─── */
#nprogress .bar { background: #0d6efd !important; height: 3px !important; }
#nprogress .peg { box-shadow: 0 0 10px #0d6efd, 0 0 5px #0d6efd !important; }

/* ─── Fade-in animation ─── */
#laporan-content.fade-in {
    animation: fadeIn .3s ease;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ─── Partial filter form styling ─── */
.partial-filter {
    padding: 20px 24px 16px;
    background: #fff;
    border-bottom: 1px solid #e9ecef;
}
.partial-filter .filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}
.partial-filter .filter-group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: .3px;
}
.partial-filter .filter-group input,
.partial-filter .filter-group select {
    font-size: 13px;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    padding: 6px 10px;
}
.partial-filter .btn-group {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.partial-filter .btn { font-size: 13px; border-radius: 6px; padding: 6px 14px; }

/* ─── Table styling (matching Kunjungan Poli reference) ─── */
.partial-body { padding: 20px 24px; }
.partial-body .table-caption {
    text-align: center;
    font-weight: 600;
    font-size: 14px;
    color: #212529;
    margin-bottom: 4px;
}
.partial-body .table-subcaption {
    text-align: center;
    font-size: 13px;
    color: #212529;
    margin-bottom: 2px;
}
.partial-body .table-period {
    text-align: center;
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 12px;
}
.partial-body .table-note {
    font-size: 11px;
    color: #dc3545;
    margin-bottom: 12px;
}
.partial-body .data-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    font-family: 'Segoe UI', sans-serif;
    border-color: #dee2e6;
}
.partial-body .data-table thead th {
    background: #343a40;
    color: #fff;
    font-weight: 500;
    padding: 8px 10px;
    text-align: center;
    border-color: #495057;
    font-size: 12px;
    white-space: nowrap;
}
.partial-body .data-table tbody td {
    padding: 7px 10px;
    text-align: center;
    border-color: #dee2e6;
    vertical-align: middle;
}
.partial-body .data-table tbody tr:nth-child(even) { background: #f8f9fa; }
.partial-body .data-table tbody tr:hover { background: #e7f1ff; }
.partial-body .data-table .th-green { background: #bdd9bf !important; color: #212529 !important; border-color: #a3c9a6 !important; }
.partial-body .data-table .th-red { background: #F47174 !important; color: #fff !important; border-color: #e05a5d !important; }
.partial-body .data-table tfoot td {
    padding: 7px 10px;
    text-align: center;
    border-color: #dee2e6;
    vertical-align: middle;
}
.partial-body .data-table .td-green { background: #bdd9bf; }
.partial-body .data-table .td-red { background: #F47174; color: #fff; font-weight: 600; }

/* card-based sub-sections inside partials */
.subsection-card {
    border: 1px solid #e2e8f0;
    border-radius: .375rem;
    margin-bottom: 16px;
    overflow: hidden;
}
.subsection-card .card-head {
    padding: 10px 16px;
    font-weight: 600;
    font-size: 13px;
    color: #fff;
}
.subsection-card .card-body-table { padding: 0; }
.subsection-card .card-body-table .data-table { margin: 0; }
.subsection-card .card-body-content { padding: 16px; }

@media (max-width: 768px) {
    .laporan-tabs { gap: 4px; padding: 8px 10px; }
    .laporan-tabs .tab-btn { padding: 5px 10px; font-size: 12px; }
    .partial-filter .filter-row { flex-direction: column; }
    .partial-body { padding: 12px; }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        {{-- Tab Navigation --}}
        <div class="laporan-tabs" role="tablist">
            @php
                $menus = [
                    ['key' => 'kelengkapan',    'route' => 'kelengkapan',       'icon' => 'bx bx-folder-plus',       'label' => 'Kelengkapan RM'],
                    ['key' => 'rajal',           'route' => 'kunjunganrajal',    'icon' => 'bx bx-handicap',          'label' => 'Pasien Rawat Jalan'],
                    ['key' => 'ranap',           'route' => 'kunjunganranap',    'icon' => 'bx bx-hotel',             'label' => 'Pasien Rawat Ranap'],
                    ['key' => 'penyakterbanyak',  'route' => 'penyakitterbanyak', 'icon' => 'bx bx-line-chart',        'label' => 'Penyakit Terbanyak'],
                    ['key' => 'penyakitmenular',  'route' => 'penyakitmenular',   'icon' => 'bx bx-plus-medical',      'label' => 'Penyakit Menular'],
                    ['key' => 'igd',             'route' => 'igd',               'icon' => 'bx bx-bell-plus',         'label' => 'Laporan IGD'],
                    ['key' => 'operasi',         'route' => 'operasi',           'icon' => 'bx bx-shield-plus',       'label' => 'Kegiatan Operasi'],
                    ['key' => 'kematian',        'route' => 'kematian',          'icon' => 'bx bx-dizzy',             'label' => 'Kematian'],
                    ['key' => 'pertumbuhan',     'route' => 'pertumbuhan',       'icon' => 'bx bx-trending-up',       'label' => 'Pertumbuhan'],
                    ['key' => 'ibudanbayi',      'route' => 'ibudanbayi',        'icon' => 'bx bx-child',             'label' => 'Ibu dan Bayi'],
                    ['key' => 'radlab',          'route' => 'laporan_radlab',    'icon' => 'bx bx-home-circle',       'label' => 'Radiologi & Laboratorium'],
                ];
            @endphp
            @foreach($menus as $m)
                <button class="tab-btn {{ $activeTab === $m['key'] ? 'active' : '' }}"
                        data-key="{{ $m['key'] }}"
                        data-url="{{ route($m['route']) }}"
                        type="button" role="tab">
                    <i class="tf-icons {{ $m['icon'] }}"></i>
                    <span>{{ $m['label'] }}</span>
                </button>
            @endforeach
        </div>

        {{-- Dynamic Content Area --}}
        <div class="content-shell">
            <div id="loading-overlay">
                <div class="spinner-ring"></div>
                <span style="font-size:13px;color:#6c757d;">Memuat data...</span>
            </div>
            <div id="laporan-content">
                {{-- Content loaded via AJAX on page ready --}}
            </div>
        </div>
    </div>
</div>

{{-- Modals container (for kelengkapan etc.) --}}
<div id="modal-container"></div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.min.js"></script>
<script>
$(function () {
    const $content  = $('#laporan-content');
    const $overlay  = $('#loading-overlay');
    let   currentKey = '{{ $activeTab }}';

    // ── Active tab on first load ──
    function activateTab(key) {
        currentKey = key;
        $('.tab-btn').removeClass('active')
            .filter('[data-key="'+key+'"]').addClass('active');
    }

    // ── Destroy any existing DataTables in content area ──
    function destroyExistingDataTables() {
        try {
            $content.find('table').each(function () {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().destroy(true);
                }
            });
        } catch(e) { /* ignore if DT not loaded yet */ }
    }

    // ── Evaluate inline scripts inside content ──
    function evalContentScripts() {
        $content.find('script').each(function () {
            if (!this.src) {
                try { $.globalEval(this.textContent || this.innerHTML); }
                catch(e) { console.error('Script eval error:', e); }
            }
        });
    }

    // ── Replace "0" with empty string in non-Total table cells ──
    function applyZeroReplacement() {
        $content.find('.data-table tbody td:not(.td-red)').each(function () {
            if ($.trim($(this).text()) === '0' && !$(this).find('button, span, a, i, input, select').length) {
                $(this).text('-');
            }
        });
    }

    // ── Common success handler for AJAX content load ──
    function onContentLoaded(html) {
        $content.html(html).removeClass('fade-in');
        void $content[0].offsetWidth;
        $content.addClass('fade-in');
        $overlay.removeClass('active');
        NProgress.done();
        evalContentScripts();
        applyZeroReplacement();
    }

    // ── Load content via AJAX ──
    function loadTab(url, key) {
        if (url === '#' || !url) return;
        activateTab(key);
        NProgress.start();
        $overlay.addClass('active');
        destroyExistingDataTables();

        $.ajax({
            url: url,
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) { onContentLoaded(html); },
            error: function () {
                $content.html('<div class="text-center py-5 text-danger"><i class="bx bx-error-circle" style="font-size:40px;"></i><p class="mt-2">Gagal memuat konten. Silakan coba lagi.</p></div>');
                $overlay.removeClass('active');
                NProgress.done();
            }
        });
    }

    // ── Tab click handler ──
    $(document).on('click', '.tab-btn', function () {
        const url = $(this).data('url');
        const key = $(this).data('key');
        history.pushState({ key, url }, '', url);
        loadTab(url, key);
    });

    // ── Browser back/forward ──
    $(window).on('popstate', function (e) {
        if (e.originalEvent.state) {
            const s = e.originalEvent.state;
            activateTab(s.key);
            loadTab(s.url, s.key);
        }
    });

    // ── Track PDF download button clicks ──
    var _pendingPdfDownload = false;
    $(document).on('click', '#laporan-content button[name="download_pdf"]', function () {
        _pendingPdfDownload = true;
    });

    // ── Intercept form submissions inside dynamic content (POST → AJAX) ──
    //    But NOT for PDF downloads — those should submit normally
    $(document).on('submit', '#laporan-content form[data-ajax="true"]', function (e) {
        // If a PDF download button was clicked, let the form submit normally
        if (_pendingPdfDownload) {
            _pendingPdfDownload = false;
            return; // allow default form submission
        }
        e.preventDefault();
        const $form = $(this);
        NProgress.start();
        $overlay.addClass('active');
        destroyExistingDataTables(); // destroy BEFORE replacing content

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) { onContentLoaded(html); },
            error: function () {
                $overlay.removeClass('active');
                NProgress.done();
                showToast('Terjadi kesalahan saat memproses data.', 'error');
            }
        });
    });

    // ── Simple toast ──
    window.showToast = function (msg, type) {
        type = type || 'success';
        var colors = { success:'#28a745', error:'#dc3545', warning:'#ffc107', info:'#17a2b8' };
        var $t = $('<div>').css({
            position:'fixed',top:20,right:20,zIndex:9999,
            padding:'12px 20px',borderRadius:8,
            background:colors[type]||colors.success,color:'#fff',
            fontSize:'13px',fontWeight:500,
            boxShadow:'0 4px 12px rgba(0,0,0,.15)',
            opacity:0,transition:'opacity .3s'
        }).text(msg).appendTo('body');
        setTimeout(function(){ $t.css('opacity',1); },10);
        setTimeout(function(){ $t.css('opacity',0); setTimeout(function(){$t.remove();},300); },3000);
    };

    // ── Initial load: fetch active tab content via AJAX ──
    var initialUrl = $('.tab-btn.active').data('url');
    if (initialUrl) {
        loadTab(initialUrl, currentKey);
    }

    // ── Push initial state ──
    history.replaceState({ key: currentKey, url: location.href }, '', location.href);
});
</script>
@endpush

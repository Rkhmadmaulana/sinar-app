<div class="container-fluid">
    <ul class="nav nav-pills mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link @if(!request()->ajax() && Route::currentRouteName() === 'poliklinik') active @endif"
                    data-url="{{ route('poliklinik') }}">
                <i class="tf-icons bx bx-home-circle me-1"></i> Poliklinik
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link @if(!request()->ajax() && Route::currentRouteName() === 'hemodialisa') active @endif"
                    data-url="{{ route('hemodialisa') }}">
                <i class="tf-icons bx bx-home-circle me-1"></i> Unit Hemodialisa
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link @if(!request()->ajax() && Route::currentRouteName() === 'igdk') active @endif"
                    data-url="{{ route('igdk') }}">
                <i class="tf-icons bx bx-home-circle me-1"></i> Instalasi Gawat Darurat
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link @if(!request()->ajax() && Route::currentRouteName() === 'allpoliklinikkhusus') active @endif"
                    data-url="{{ route('allpoliklinikkhusus', ['kd_poli' => 'MCU']) }}">
                <i class="tf-icons bx bx-home-circle me-1"></i> Medical Check Up
            </button>
        </li>
    </ul>
</div>

<script>
(function() {
    'use strict';

    // ── Track active AJAX for anti-spam (abort pattern like kelengkapan) ──
    let _menuXhr = null;
    let _formXhr = null;

    // ── Helper: Execute scripts from AJAX response ──
    function loadAjaxContent(container, html) {
        var wrapper = typeof container === 'string' ? document.querySelector(container) : container;
        if (!wrapper) return;

        wrapper.innerHTML = html;

        var pendingListeners = [];
        var origAddEventListener = document.addEventListener;

        document.addEventListener = function(type, listener, options) {
            if (type === 'DOMContentLoaded') {
                pendingListeners.push(listener);
            } else {
                origAddEventListener.call(document, type, listener, options);
            }
        };

        var scripts = wrapper.querySelectorAll('script');
        scripts.forEach(function(oldScript) {
            var newScript = document.createElement('script');
            if (oldScript.src) {
                newScript.src = oldScript.src;
            }
            newScript.textContent = oldScript.textContent;
            document.body.appendChild(newScript);
            if (!oldScript.src) {
                newScript.remove();
            }
        });

        document.addEventListener = origAddEventListener;

        pendingListeners.forEach(function(listener) {
            try { listener(); } catch(e) { console.error('Chart init error:', e); }
        });

        wrapper.querySelectorAll('script').forEach(function(s) { s.remove(); });
    }

    function setActiveByUrl(url) {
        document.querySelectorAll('.nav-pills .nav-link').forEach(function(btn) {
            var btnUrl = btn.getAttribute('data-url');
            if (btnUrl && url.includes(btnUrl.split('?')[0])) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        setActiveByUrl(window.location.href);

        // ── Menu click: abort previous, NO disable buttons, NO timeout ──
        document.querySelectorAll('.nav-pills .nav-link').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                var url = this.getAttribute('data-url');
                if (!url) return;

                // Anti-spam: abort previous request only
                if (_menuXhr) { _menuXhr.abort(); _menuXhr = null; }
                if (_formXhr) { _formXhr.abort(); _formXhr = null; }

                // Set active state
                var allBtns = document.querySelectorAll('.nav-pills .nav-link');
                allBtns.forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');

                // Loading indicator
                var contentEl = document.getElementById('rajal-content');
                if (contentEl) {
                    contentEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Memuat data...</p></div>';
                }

                // AJAX request via jQuery (like kelengkapan pattern)
                _menuXhr = $.ajax({
                    url: url,
                    type: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(html) {
                        _menuXhr = null;
                        if (contentEl) {
                            loadAjaxContent(contentEl, html);
                        }
                    },
                    error: function(xhr, status) {
                        _menuXhr = null;
                        if (status === 'abort') return;
                        if (contentEl) {
                            contentEl.innerHTML = '<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>';
                        }
                    }
                });
            });
        });

        // ── Form Filter: AJAX submit (abort pattern) ──
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (!form.id || form.id !== 'filterForm') return;

            var contentEl = document.getElementById('rajal-content');
            if (!contentEl || !contentEl.contains(form)) return;

            e.preventDefault();

            if (_formXhr) { _formXhr.abort(); _formXhr = null; }
            if (_menuXhr) { _menuXhr.abort(); _menuXhr = null; }

            contentEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Memproses filter...</p></div>';

            _formXhr = $.ajax({
                url: form.action,
                type: 'POST',
                data: $(form).serialize(),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(html) {
                    _formXhr = null;
                    if (contentEl) {
                        loadAjaxContent(contentEl, html);
                    }
                },
                error: function(xhr, status) {
                    _formXhr = null;
                    if (status === 'abort') return;
                    form.submit();
                }
            });
        });
    });
})();
</script>

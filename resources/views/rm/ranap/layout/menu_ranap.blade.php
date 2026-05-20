<div class="container-fluid">
    <ul class="nav nav-pills mb-3" role="tablist">
        <li class="nav-item">
            <button class="nav-link active"
                    data-url="{{ route('ranap') }}">
                <i class="tf-icons bx bx-home-circle me-1"></i> Perawatan
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

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.nav-pills .nav-link').forEach(function(btn) {
            var btnUrl = btn.getAttribute('data-url');
            if (btnUrl && window.location.href.includes(btnUrl.split('?')[0])) {
                btn.classList.add('active');
            }
        });

        // ── Menu click: abort previous, NO disable buttons, NO timeout ──
        document.querySelectorAll('.nav-pills .nav-link').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                var url = this.getAttribute('data-url');
                if (!url) return;

                if (_menuXhr) { _menuXhr.abort(); _menuXhr = null; }
                if (_formXhr) { _formXhr.abort(); _formXhr = null; }

                var allBtns = document.querySelectorAll('.nav-pills .nav-link');
                allBtns.forEach(function(b) { b.classList.remove('active'); });
                this.classList.add('active');

                var contentEl = document.getElementById('ranap-content');
                if (contentEl) {
                    contentEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Memuat data...</p></div>';
                }

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

            var contentEl = document.getElementById('ranap-content');
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

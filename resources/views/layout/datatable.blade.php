@php
    $tableId = $tableId ?? 'dataTable';
    $searchPlaceholder = $searchPlaceholder ?? 'Cari...';
@endphp

{{-- Custom Controls - Top --}}
<div class="dt_custom_n_controls_top">
    <div class="dt_custom_n_info_container">
        <div class="dt_custom_n_info"></div>
    </div>
    <div class="dt_custom_n_controls_row">
        <div class="dt_custom_n_search_container">
            <input type="text" id="dt_custom_n_search_{{ $tableId }}" class="dt_custom_n_search" placeholder="{{ $searchPlaceholder }}">
        </div>
        <div class="dt_custom_n_length_container">
            <select id="dt_custom_n_length_{{ $tableId }}" class="dt_custom_n_length">
                <option value="10">10 per halaman</option>
                <option value="25">25 per halaman</option>
                <option value="50">50 per halaman</option>
                <option value="100">100 per halaman</option>
                <option value="-1">Semua</option>
            </select>
        </div>
    </div>
</div>

{{-- Table Container --}}
<div class="table-responsive dt_custom_n_table_container" id="dt_custom_n_container_{{ $tableId }}">
    <table class="table table-bordered table-striped" id="{{ $tableId }}">
        <thead>
            <tr>
                @if(isset($columns))
                    @foreach($columns as $column)
                        <th>{{ $column['title'] ?? ucfirst($column['data']) }}</th>
                    @endforeach
                @else
                    {{ $slot ?? '' }}
                @endif
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

{{-- Custom Controls - Bottom --}}
<div class="dt_custom_n_controls_bottom">
    <div class="dt_custom_n_pagination_wrapper">
        <div class="dt_custom_n_pagination">
            <!-- Pagination will be inserted here -->
        </div>
        <div class="dt_custom_n_page_jump">
            <!-- Page jump will be inserted here -->
        </div>
    </div>
</div>

{{-- Styles --}}
@push('styles')
<style>
/* Hide default DataTables controls */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter,
.dataTables_wrapper .dataTables_paginate,
.dataTables_wrapper .dataTables_info {
    display: none !important;
}

/* Loading overlay styles */
.dt_custom_n_loading_overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.8);
    z-index: 9999 !important;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
}

.dt_custom_n_loading_spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #007bff;
    border-radius: 50%;
    animation: dt_custom_n_spin 1s linear infinite;
}

.dt_custom_n_loading_text {
    margin-top: 15px;
    color: #007bff;
    font-weight: bold;
    font-size: 16px;
}

@keyframes dt_custom_n_spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Table container relative positioning for overlay */
#dt_custom_n_container_{{ $tableId }} {
    position: relative;
}

/* Blur effect when loading */
.dt_custom_n_table_blur {
    filter: blur(2px);
    opacity: 0.5;
    pointer-events: none;
}

/* Hide default processing indicator */
#{{ $tableId }}_processing {
    display: none !important;
}


/* Custom Controls Styling - Top */
.dt_custom_n_controls_top {
    margin-bottom: 15px;
}

.dt_custom_n_info_container {
    text-align: center;
    margin-bottom: 15px;
}

@media (min-width: 768px) {
    .dt_custom_n_info_container {
        text-align: left;
        margin-bottom: 10px;
    }
}

.dt_custom_n_info {
    display: inline-block;
    padding: 8px 15px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 2px 4px rgba(0,123,255,0.2);
}

.dt_custom_n_controls_row {
    display: flex;
    flex-direction: column;
    gap: 15px;
    align-items: center;
}

@media (min-width: 768px) {
    .dt_custom_n_controls_row {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

.dt_custom_n_search_container {
    width: 100%;
    max-width: 350px;
}

@media (min-width: 768px) {
    .dt_custom_n_search_container {
        max-width: 300px;
        order: 2;
    }
}

.dt_custom_n_search {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

@media (max-width: 767px) {
    .dt_custom_n_search {
        text-align: center;
        padding: 14px 16px;
        font-size: 16px; /* Prevent zoom on iOS */
    }
}

.dt_custom_n_search:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.dt_custom_n_length_container {
    width: 100%;
    max-width: 200px;
}

@media (min-width: 768px) {
    .dt_custom_n_length_container {
        max-width: 180px;
        order: 1;
    }
}

.dt_custom_n_length {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    background-color: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: all 0.2s ease;
}

@media (max-width: 767px) {
    .dt_custom_n_length {
        text-align: center;
        text-align-last: center;
        padding: 14px 16px;
        font-size: 16px; /* Prevent zoom on iOS */
    }
}

.dt_custom_n_length:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

/* Custom Controls Styling - Bottom */
.dt_custom_n_controls_bottom {
    margin-top: 20px;
}

.dt_custom_n_pagination_wrapper {
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

@media (max-width: 576px) {
    .dt_custom_n_pagination_wrapper {
        padding: 15px;
    }
}

.dt_custom_n_pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 15px;
    flex-wrap: wrap;
    gap: 6px;
}

.dt_custom_n_pagination .dt_custom_n_paginate_button {
    display: inline-block;
    padding: 10px 14px;
    margin: 2px;
    border: 1px solid #ddd;
    border-radius: 6px;
    background: white;
    color: #007bff;
    text-decoration: none;
    font-size: 14px;
    min-width: 44px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    font-weight: 500;
}

.dt_custom_n_pagination .dt_custom_n_paginate_button:hover:not(.dt_custom_n_disabled):not(.dt_custom_n_current) {
    background: #e9ecef;
    border-color: #007bff;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}

.dt_custom_n_pagination .dt_custom_n_paginate_button.dt_custom_n_current {
    background: #007bff;
    color: white;
    border-color: #007bff;
    box-shadow: 0 2px 6px rgba(0,123,255,0.3);
    font-weight: 600;
}

.dt_custom_n_pagination .dt_custom_n_paginate_button.dt_custom_n_disabled {
    color: #6c757d;
    cursor: not-allowed;
    opacity: 0.5;
    background: #f8f9fa;
}

.dt_custom_n_page_jump {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 12px;
    font-size: 14px;
    color: #495057;
    font-weight: 500;
}

.dt_custom_n_page_jump span {
    color: #666;
}

.dt_custom_n_page_jump input {
    width: 70px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    text-align: center;
    font-size: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

.dt_custom_n_page_jump input:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
}

.dt_custom_n_page_jump input::-webkit-outer-spin-button,
.dt_custom_n_page_jump input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

.dt_custom_n_page_jump input[type=number] {
    -moz-appearance: textfield;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .dt_custom_n_pagination .dt_custom_n_paginate_button {
        padding: 12px 16px;
        font-size: 15px;
        min-width: 48px;
    }
    
    .dt_custom_n_page_jump {
        gap: 10px;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .dt_custom_n_page_jump input {
        width: 60px;
        padding: 10px;
    }
}

/* Table responsiveness */
@media (max-width: 768px) {
    .table-responsive.dt_custom_n_table_container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #ddd;
        border-radius: 6px;
    }
}
</style>
@endpush

{{-- JavaScript --}}
@push('scripts')
<script>
function initCustomDataTable(tableId, options = {}) {
    const defaultOptions = {
        processing: true,
        serverSide: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        language: {
            processing: "Memuat data...",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            search: "Cari:",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },
        scrollX: false,
        responsive: false,
        order: [[0, 'desc']]
    };

    // Merge options
    const finalOptions = Object.assign({}, defaultOptions, options);

    // Function to show loading overlay
    function showLoading() {
        const container = $(`#dt_custom_n_container_${tableId}`);
        if ($(`#dt_custom_n_loading_${tableId}`).length === 0) {
            container.append(`
                <div id="dt_custom_n_loading_${tableId}" class="dt_custom_n_loading_overlay">
                    <div class="dt_custom_n_loading_spinner"></div>
                    <div class="dt_custom_n_loading_text">Memuat data...</div>
                </div>
            `);
        }
        $(`#${tableId}`).addClass('dt_custom_n_table_blur');
        $(`#dt_custom_n_loading_${tableId}`).show();
    }

    // Function to hide loading overlay
    function hideLoading() {
        $(`#${tableId}`).removeClass('dt_custom_n_table_blur');
        $(`#dt_custom_n_loading_${tableId}`).hide();
    }

    // Add loading callbacks if ajax is used
    if (finalOptions.ajax) {
        const originalAjax = finalOptions.ajax;
        finalOptions.ajax = {
            ...originalAjax,
            beforeSend: function() {
                showLoading();
                if (originalAjax.beforeSend) originalAjax.beforeSend.apply(this, arguments);
            },
            complete: function() {
                hideLoading();
                if (originalAjax.complete) originalAjax.complete.apply(this, arguments);
            }
        };
    }

    // Add draw callback
    finalOptions.drawCallback = function(settings) {
        const api = this.api();
        updateCustomPagination(api, tableId);
        updateCustomInfo(api, tableId);
        
        if (options.drawCallback) {
            options.drawCallback.call(this, settings);
        }
    };

    // Initialize DataTable
    const table = $(`#${tableId}`).DataTable(finalOptions);

    // Function to update custom pagination
    function updateCustomPagination(api, tableId) {
        const pageInfo = api.page.info();
        let paginationHtml = '';
        
        // Previous button
        if (pageInfo.page > 0) {
            paginationHtml += `<a href="#" class="dt_custom_n_paginate_button dt_custom_n_previous" data-page="${pageInfo.page - 1}">‹</a>`;
        } else {
            paginationHtml += '<span class="dt_custom_n_paginate_button dt_custom_n_disabled">‹</span>';
        }
        
        // Page numbers (show max 5 pages on desktop, 3 on mobile)
        const maxPages = $(window).width() <= 576 ? 3 : 5;
        const halfMaxPages = Math.floor(maxPages / 2);
        let startPage = Math.max(0, pageInfo.page - halfMaxPages);
        let endPage = Math.min(pageInfo.pages - 1, pageInfo.page + halfMaxPages);
        
        // Adjust range if near beginning or end
        if (endPage - startPage + 1 < maxPages) {
            if (startPage === 0) {
                endPage = Math.min(pageInfo.pages - 1, maxPages - 1);
            } else if (endPage === pageInfo.pages - 1) {
                startPage = Math.max(0, pageInfo.pages - maxPages);
            }
        }

        if (startPage > 0) {
            paginationHtml += '<a href="#" class="dt_custom_n_paginate_button" data-page="0">1</a>';
            if (startPage > 1) {
                paginationHtml += '<span class="dt_custom_n_paginate_button dt_custom_n_disabled">...</span>';
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            if (i === pageInfo.page) {
                paginationHtml += `<span class="dt_custom_n_paginate_button dt_custom_n_current">${i + 1}</span>`;
            } else {
                paginationHtml += `<a href="#" class="dt_custom_n_paginate_button" data-page="${i}">${i + 1}</a>`;
            }
        }
        
        if (endPage < pageInfo.pages - 1) {
            if (endPage < pageInfo.pages - 2) {
                paginationHtml += '<span class="dt_custom_n_paginate_button dt_custom_n_disabled">...</span>';
            }
            paginationHtml += `<a href="#" class="dt_custom_n_paginate_button" data-page="${pageInfo.pages - 1}">${pageInfo.pages}</a>`;
        }
        
        // Next button
        if (pageInfo.page < pageInfo.pages - 1) {
            paginationHtml += `<a href="#" class="dt_custom_n_paginate_button dt_custom_n_next" data-page="${pageInfo.page + 1}">›</a>`;
        } else {
            paginationHtml += '<span class="dt_custom_n_paginate_button dt_custom_n_disabled">›</span>';
        }
        
        $('.dt_custom_n_pagination').html(paginationHtml);
        
        // Page jump
        const pageJumpHtml = `
            <span>Halaman:</span>
            <input type="number" class="dt_custom_n_page_input" value="${pageInfo.page + 1}" min="1" max="${pageInfo.pages}">
            <span>dari ${pageInfo.pages}</span>
        `;
        $('.dt_custom_n_page_jump').html(pageJumpHtml);
    }
    
    // Function to update custom info
    function updateCustomInfo(api, tableId) {
        const pageInfo = api.page.info();
        let infoText = '';
        
        if (pageInfo.recordsTotal > 0) {
            if (pageInfo.recordsDisplay !== pageInfo.recordsTotal) {
                infoText = `Menampilkan ${pageInfo.start + 1}-${pageInfo.end} dari ${pageInfo.recordsDisplay} data (difilter dari ${pageInfo.recordsTotal} total data)`;
            } else {
                infoText = `Menampilkan ${pageInfo.start + 1}-${pageInfo.end} dari ${pageInfo.recordsTotal} data`;
            }
        } else {
            infoText = 'Tidak ada data';
        }
        
        $('.dt_custom_n_info').text(infoText);
    }

    // Custom pagination click events
    $(document).on('click', `.dt_custom_n_pagination .dt_custom_n_paginate_button:not(.dt_custom_n_disabled):not(.dt_custom_n_current)`, function(e) {
        e.preventDefault();
        const page = $(this).data('page');
        if (page !== undefined) {
            table.page(page).draw('page');
        }
    });

    // Custom page jump
    $(document).on('change keypress', '.dt_custom_n_page_input', function(e) {
        if (e.type === 'keypress' && e.which !== 13) return;
        
        const pageNumber = parseInt($(this).val());
        const pageInfo = table.page.info();
        
        if (pageNumber >= 1 && pageNumber <= pageInfo.pages) {
            table.page(pageNumber - 1).draw('page');
        } else {
            $(this).val(pageInfo.page + 1);
            alert('Nomor halaman tidak valid. Masukkan angka antara 1 dan ' + pageInfo.pages);
        }
    });

    // Custom search
    let customSearchDelay = null;
    $(`#dt_custom_n_search_${tableId}`).on('input', function() {
        const keyword = this.value;
        clearTimeout(customSearchDelay);
        customSearchDelay = setTimeout(function() {
            table.search(keyword).draw();
        }, 500);
    });

    // Custom length change
    $(`#dt_custom_n_length_${tableId}`).on('change', function() {
        table.page.len($(this).val()).draw();
    });

    return table;
}
</script>
@endpush
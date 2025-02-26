@extends('layout.app')
@section('content')
    @include('rm.ranap.layout.menu_ranap')

    <div class="container-xxl flex-grow-1 container-p-y">

        <small style="color:red;">*Data dibawah ini berdasarkan Tanggal Masuk Pasien Ke Kamar Inap</small><br><br>
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <form id="filterForm" action="{{ route('ranap') }}" method="POST">
                                    @csrf
                                    <div class="row clearfix">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <div class="form-line">
                                                    <dt>Dari Tanggal</dt>
                                                    <dd>
                                                        @if (isset($tgl1))
                                                            <input type="date" value="{{ $tgl1 }}"
                                                                class="form-control" name="tgl1">
                                                        @else
                                                            <input type="date" class="form-control" name="tgl1">
                                                        @endif
                                                    </dd>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <div class="form-line">
                                                    <dt>Sampai Tanggal</dt>
                                                    <dd>
                                                        @if (isset($tgl2))
                                                            <input type="date" value="{{ $tgl2 }}"
                                                                class="form-control" name="tgl2">
                                                        @else
                                                            <input type="date" class="form-control" name="tgl2">
                                                        @endif
                                                    </dd>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <div class="form-line">
                                                    <dt>Kamar</dt>
                                                    <dd>
                                                        <select name="kode_kamar" class="form-control" style="width:100%">
                                                            <option value="" selected>Semua Kamar</option>
                                                            @foreach ($pilihan_kamar as $item)
                                                                <option value="{{ $item->kd_bangsal }}"
                                                                    @if (isset($kodekamar) && $kodekamar == $item->kd_bangsal) selected @endif>
                                                                    {{ $item->nm_bangsal }}</option>
                                                            @endforeach

                                                        </select>
                                                    </dd>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <div class="form-line">
                                                    <dt>Cara Bayar</dt>
                                                    <dd>
                                                        <select name="kodepj" class="form-control" style="width:100%">
                                                            <option value=""
                                                                @if (isset($kodepj) && $kodepj == '') selected @endif>Semua
                                                            </option>
                                                            @foreach ($pilihan_cara_bayar as $pj)
                                                                <option value="{{ $pj->kd_pj }}"
                                                                    @if (isset($kodepj) && $kodepj == $pj->kd_pj) selected @endif>
                                                                    {{ $pj->png_jawab }}</option>
                                                            @endforeach
                                                        </select>
                                                    </dd>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row clearfix">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <dd><button type="submit" name="tombol" value="filter"
                                                            class="btn btn-primary">Filter</button> </dd>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <small style="color:red;">*Data Tanpa Filter Cara Bayar</small>
                            <div id="chart_line"></div>
                            <div id="chart_cara_bayar"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <small style="color:red;">*Data Tanpa Filter Kamar</small>
                            <div id="chart_linekelas"></div>
                            <div id="chart_pie_kelas"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <small style="color:red;">*Data Tanpa Filter Kamar</small>
                            <div id="chart_bar_pelayanan_dokter"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <small style="color:red;">*Data Tanpa Filter Kamar</small>
                            <div id="chart_bar_pelayanan_prw"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="chart_bar_status_daftar"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="chart_bar_pel"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <br>
        <div class="row">
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="chart_bar_prosedur"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="chart_bar_diagnosa"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <br>
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex align-items-center justify-content-between pb-0">
                            <div class="card-body">
                                <div id="chart_bar_gizi"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            createLineChart("#chart_line", @json($judul_line), @json($subjudul_line),
                @json($labelstat), [{
                        name: 'INHEALTH',
                        data: @json($inhealth)
                    },
                    {
                        name: 'BKK',
                        data: @json($bkk)
                    },
                    {
                        name: 'JAMKESDA',
                        data: @json($jamkesda)
                    },
                    {
                        name: 'Pendamping JKN',
                        data: @json($pjkn)
                    },
                    {
                        name: 'BPJS',
                        data: @json($bpjs)
                    },
                    {
                        name: 'Umum',
                        data: @json($umum)
                    }
                ], ['#FF4560', '#00E396', '#008FFB', '#775DD0', '#FEB019', '#FF66C3']);

            createPieChart("#chart_cara_bayar", @json($judul_pie_cara_bayar), @json($subjudul_pie_cara_bayar),
                @json($labelcara_bayar), @json($datacara_bayar), @json($warnabayar));

            createPieChart("#chart_pie_kelas", @json($judul_pie_kelas), @json($subjudul_pie_kelas),
                @json($labelkelas), @json($datakelas), @json($warnakelas));

            createLineChart("#chart_linekelas", @json($judul_linekelas), @json($subjudul_linekelas),
                @json($labelstatkelas), [{
                        name: 'VVIP',
                        data: @json($vvip)
                    },
                    {
                        name: 'VIP',
                        data: @json($vip)
                    },
                    {
                        name: 'Utama',
                        data: @json($utama)
                    },
                    {
                        name: 'Kelas 1',
                        data: @json($kelas1)
                    },
                    {
                        name: 'Kelas 2',
                        data: @json($kelas2)
                    },
                    {
                        name: 'Kelas 3',
                        data: @json($kelas3)
                    }
                ], ['#FF4560', '#00E396', '#008FFB', '#775DD0', '#FEB019', '#FF66C3']);

            createBarChart("#chart_bar_prosedur", @json($judul_pie_sqlprosedur), @json($subjudul_pie_sqlprosedur),
                @json($labelsprosedur), [{
                    name: 'Jumlah',
                    data: @json($data_sqlprosedur)
                }], @json($warna_sqlprosedur));

            createBarChart("#chart_bar_diagnosa", @json($judul_pie_sqldiagnosa), @json($subjudul_pie_sqldiagnosa),
                @json($labelsdiagnosa), [{
                    name: 'Jumlah',
                    data: @json($data_sqldiagnosa)
                }], @json($warna_sqldiagnosa));

            createBarChart("#chart_bar_pelayanan_dokter", @json($judul_pie_peldokter),
                @json($subjudul_pie_peldokter), @json($labelspeldokter), [{
                    name: 'Jumlah',
                    data: @json($datapeldokter)
                }], @json($warnapeldokter));

            createBarChart("#chart_bar_pelayanan_prw", @json($judul_pie_pelprw), @json($subjudul_pie_pelprw),
                @json($labelspelprw), [{
                    name: 'Jumlah',
                    data: @json($datapelprw)
                }], @json($warnapelprw));

            createBarChart("#chart_bar_status_daftar", @json($judul_bar_stts_daftar), @json($subjudul_bar_stts_daftar),
                @json($labels_stts_daftar), [{
                    name: 'Jumlah',
                    data: @json($data_stts_daftar)
                }], @json($warnastts_daftar));

            createBarChart("#chart_bar_pel", @json($judul_pie_pel), @json($subjudul_pie_pel),
                @json($labelspel), [{
                    name: 'Jumlah',
                    data: @json($datapel)
                }], @json($warnapel));



            createBarChart("#chart_bar_gizi",
                @json($judul_bar_adime),
                @json($subjudul_bar_adime),
                @json($labels_adime),
                [{
                    name: 'Jumlah',
                    data: @json(array_values($data_adime)) // Pastikan data dalam array numerik
                }],
                @json($warnastts_adime)
            );
            // Fungsi untuk membuat Line Chart
            function createLineChart(selector, title, subtitle, categories, series, colors) {
                var options = {
                    series: series,
                    chart: {
                        type: 'line',
                        height: 350
                    },
                    colors: colors,
                    stroke: {
                        width: 2,
                        curve: 'smooth'
                    },
                    title: {
                        text: title,
                        align: 'center'
                    },
                    subtitle: {
                        text: subtitle,
                        align: 'center'
                    },
                    xaxis: {
                        categories: categories,
                        title: {
                            text: 'Waktu'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Jumlah'
                        }
                    },
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        shared: true,
                        intersect: false
                    }
                };
                var chart = new ApexCharts(document.querySelector(selector), options);
                chart.render();
            }

            // Fungsi untuk membuat Pie Chart
            function createPieChart(selector, title, subtitle, labels, series, colors) {
                var options = {
                    series: series,
                    chart: {
                        type: 'pie',
                        height: 350
                    },
                    labels: labels,
                    colors: colors,
                    title: {
                        text: title,
                        align: 'center'
                    },
                    subtitle: {
                        text: subtitle,
                        align: 'center'
                    },
                    legend: {
                        position: 'bottom'
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 300
                            },
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }]
                };
                var chart = new ApexCharts(document.querySelector(selector), options);
                chart.render();
            }

            // Fungsi untuk membuat Bar Chart
            function createBarChart(selector, title, subtitle, categories, series, colors) {
                var options = {
                    series: series,
                    chart: {
                        type: 'bar',
                        height: 350
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 10,
                            dataLabels: {
                                position: 'top'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        style: {
                            fontSize: '12px',
                            colors: ["#304758"]
                        }
                    },
                    colors: colors,
                    xaxis: {
                        categories: categories,
                        position: 'bottom'
                    },
                    yaxis: {
                        title: {
                            text: 'Jumlah'
                        }
                    },
                    title: {
                        text: title,
                        align: 'center'
                    },
                    subtitle: {
                        text: subtitle,
                        align: 'center'
                    }
                };
                var chart = new ApexCharts(document.querySelector(selector), options);
                chart.render();
            }
        });
    </script>
@endsection

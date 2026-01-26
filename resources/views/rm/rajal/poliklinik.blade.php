@extends('layout.app')
@section('content')
@include('rm.rajal.layout.menu_rajal')

<section class="section">

<!-- <div class="container-xxl flex-grow-1 container-p-y"> -->
<small style="color:red;">*Data dibawah ini berdasarkan hasil registrasi dengan status sudah periksa dan batal periksa</small><br><br>
  <div class="row">
      <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
          <div class="card h-100">
              <div class="card-header d-flex align-items-center justify-content-between pb-0">
                  <div class="card-body" style="margin-top:30px">
                      <div class="d-flex justify-content-between align-items-center mb-3">
                        <form id="filterForm" action="{{ route('poliklinik') }}" method="POST"> 
                          @csrf
                              <div class="row clearfix">
                                  <div class="col-md-2">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <dt>Dari Tanggal</dt>
                                            <dd>
                                                <input type="date" 
                                                    value="{{ $tgl1 ?? now()->startOfMonth()->format('Y-m-d') }}" 
                                                    class="form-control" 
                                                    name="tgl1">
                                            </dd>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <div class="form-line">
                                            <dt>Sampai Tanggal</dt>
                                            <dd>
                                                <input type="date" 
                                                    value="{{ $tgl2 ?? now()->format('Y-m-d') }}" 
                                                    class="form-control" 
                                                    name="tgl2">
                                            </dd>
                                        </div>
                                    </div>
                                </div>
                                  <div class="col-md-4">
                                    <div class="form-group">
                                      <div class="form-line">
                                        <dt>Poliklinik</dt>
                                        <dd>
                                          <select name="poli"  class="form-control"  id="filterDropdown"  style="width:100%">
                                          <option value="" selected>Semua Poli</option>
                                          @foreach ($pilihan_poli  as $item)
                                            <option value="{{ $item->kd_poli }}" @if(isset($kdpoli) && $kdpoli == $item->kd_poli) selected @endif>
                                              {{ $item->nm_poli }}
                                            </option>
                                          @endforeach
                                          </select>
                                        </dd>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-md-4">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Dokter</dt>
                                          <dd>
                                            <select name="dokter"  class="form-control" style="width:100%">
                                            <option value="" selected>Semua Dokter</option>
                                            @foreach ($pilihan_dokter  as $item)
                                              <option value="{{ $item->kd_dokter }}" @if(isset($kddokter) && $kddokter == $item->kd_dokter) selected @endif> {{ $item->nm_dokter }}</option>
                                            @endforeach
                                            </select>
                                          </dd>
                                        </div>
                                      </div>
                                  </div>
                                  <div class="col-md-2">
                                    <div class="form-group">
                                      <div class="form-line">
                                        <dt>Cara Bayar</dt>
                                        <dd>
                                          <select name="cara_bayar"  class="form-control" style="width:100%">
                                          <option value="" @if(isset($kd_pj) && $kd_pj == "") selected @endif>Semua</option>
                                          @foreach ($pilihan_cara_bayar  as $pj)
                                            <option value="{{ $pj->kd_pj }}" @if(isset($kd_pj) && $kd_pj == $pj->kd_pj) selected @endif> {{ $pj->png_jawab }}</option>
                                          @endforeach
                                          </select>
                                        </dd>
                                      </div>
                                    </div>
                                </div>
                                  <div class="col-md-4">
                                      <div class="form-group">
                                        <div class="form-line">
                                          <dt>Status</dt>
                                          <dd>
                                            <select name="status"  class="form-control" style="width:100%">
                                            <option value="" @if(isset($status) && $status == "") selected @endif>Semua</option>
                                            <option value="Sudah" @if(isset($status) && $status == "Sudah") selected @endif>Sudah</option>
                                            <option value="Batal"@if(isset($status) && $status == "Batal") selected @endif>Batal</option>
                                            </select>
                                          </dd>
                                        </div>
                                      </div>
                                  </div>
                              </div>
                              <div class="row clearfix">
                                  <div class="col-md-12">
                                    <div class="form-group">
                                      <dd>
                                        <button type="submit" name="tombol" value="filter" class="btn btn-primary" style="margin-top:10px">Filter</button> 
                                        <!-- <button type="submit" name="tombol" value="print" class="btn btn-success">print</button> -->
                                    </dd>
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
<!-- </div> -->

<div class="row">
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="chart_line"></div> 
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="chart_poli"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y mb-4">
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="rujuk_masuk"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="kabupaten"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="kecamatan"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="kelurahan"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="chart_dokter"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y mb-4"> 
        <div class="row">
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="chart_cara_bayar"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="chart_stts"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="chart_prosedur"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="chart_diagnosa"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y mt-4">
        <div class="row">
            <div class="col-md-12 col-lg-12 col-xl-12 order-0 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="chart_pelayanan"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="status_daftar"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-6 col-xl-6 order-0 mb-6">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between pb-0">
                        <div class="card-body">
                            <div id="jenis_kelamin"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Chart Line --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var options = {
                series: [{
                    name: 'BPJS',
                    data: @json($bpjs)
                }, {
                    name: 'Umum',
                    data: @json($umum)
                }],
                chart: {
                    height: 350,
                    type: 'line',
                    zoom: {
                        enabled: false
                    }
                },
                colors: ['#008FFB', '#FF4560'],
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                title: {
                    text: @json($judul_line),
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_line),
                    align: 'center'
                },
                xaxis: {
                    categories: @json($labelstat),
                    title: {
                        text: 'Waktu'
                    }
                },
                yaxis: {
                    title: {
                        text: 'Jumlah'
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'center'
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: {
                            height: 400
                        },
                        xaxis: {
                            labels: {
                                rotate: -90,
                                style: {
                                    fontSize: '8px'
                                }
                            }
                        },
                        dataLabels: {
                            enabled: false
                        }
                    }
                }, {
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 350
                        },
                        legend: {
                            position: 'bottom',
                            fontSize: '9px'
                        }
                    }
                }]
            };

            var chart = new ApexCharts(document.querySelector("#chart_line"), options);
            chart.render();
        });
    </script>

    {{-- Chart Bar Poli --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var genderDataPoli = @json($tooltip_gender ?? []);
            var percentages = @json($percentages_poli ?? []);

            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data),
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                colors: @json($warnapoli),
                xaxis: {
                    categories: @json($labels),
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    //crosshairs: {
                    //    fill: {
                    //        type: 'gradient',
                    //        gradient: {
                    //            colorFrom: '#D8E3F0',
                    //            colorTo: '#BED1E6',
                    //            stops: [0, 100],
                    //            opacityFrom: 0.4,
                    //            opacityTo: 0.5,
                    //        }
                    //    }
                    //},
                    tooltip: {
                        enabled: true,
                    },
                    labels: {
                        //formatter: function (val) {
                        //    if (typeof val === 'string' && val.length > 20) {
                        //        return val.substring(0, 20) + '...';
                        //    }
                        //    return val;
                        //},
                        //trim: true,
                    }
                },
                yaxis: {
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false,
                    },
                    labels: {
                        formatter: function (val) {
                            return val;
                        }
                    }
                },
                title: {
                    text: @json($judul_pie_poli),
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_poli),
                    align: 'center'
                },
                tooltip: {
                    enabled: true,
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        var total = series[seriesIndex][dataPointIndex];
                        var gender = genderDataPoli[dataPointIndex] || {L: 0, P: 0};
                        var perc = percentages[dataPointIndex] || 0;
                        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + w.globals.labels[dataPointIndex] + '</div>' +
                               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
                               '<span class="apexcharts-tooltip-marker" style="background-color: #008FFB;"></span>' +
                               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
                               '</div></div>';
                    }
                },
                //responsive: [{
                //    breakpoint: 768,
                //    options: {
                //        chart: {
                //            height: 400
                //        },
                //        xaxis: {
                //            labels: {
                //                rotate: -90,
                //                style: {
                //                    fontSize: '8px'
                //                }
                //            }
                //        },
                //        dataLabels: {
                //            enabled: false
                //        }
                //    }
                //}, {
                //    breakpoint: 480,
                //    options: {
                //        chart: {
                //            height: 350
                //        },
                //        legend: {
                //            position: 'bottom',
                //            fontSize: '9px'
                //        }
                //    }
                //}]
            };

            var chart = new ApexCharts(document.querySelector("#chart_poli"), options);
            chart.render();
        });
    </script>

    {{-- Kabupaten --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var genderDataKab = @json($tooltip_gender_kab ?? []);
            var percentages = @json($percentages_kab ?? []);

            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_sql_kab)
                }],
                chart: {
                    height: 350,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                colors: @json($warna_sql_Kabupaten),
                xaxis: {
                    categories: @json($labels_kab),
                    position: 'bottom',
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    crosshairs: { fill: { type: 'gradient', gradient: { colorFrom: '#D8E3F0', colorTo: '#BED1E6', stops: [0, 100], opacityFrom: 0.4, opacityTo: 0.5, } } },
                    tooltip: { enabled: true },
                    formatter: function (val) {
                        if (typeof val === 'string' && val.length > 10) {
                            return val.substring(0, 30) + '...';
                        }
                        return val;
                    },
                },
                yaxis: {
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { formatter: function (val) { return val; } }
                },
                title: {
                    text: @json($judul_pie_sql_kab),
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_sql_kab),
                    align: 'center'
                },
                tooltip: {
                    enabled: true,
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        var total = series[seriesIndex][dataPointIndex];
                        var gender = genderDataKab[dataPointIndex] || {L: 0, P: 0};
                        var perc = percentages[dataPointIndex] || 0;
                        
                        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + w.globals.labels[dataPointIndex] + '</div>' +
                               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
                               '<span class="apexcharts-tooltip-marker" style="background-color: #FFD700;"></span>' +
                               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
                               '</div></div>';
                    }
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 400 },
                        xaxis: { labels: { rotate: -90, style: { fontSize: '8px' } } },
                        dataLabels: { enabled: false }
                    }
                }, {
                    breakpoint: 480,
                    options: {
                        chart: { height: 350 },
                        legend: { position: 'bottom', fontSize: '9px' }
                    }
                }]
            };

            var chart = new ApexCharts(document.querySelector("#kabupaten"), options);
            chart.render();
        });
    </script>

    {{-- Kecamatan --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var genderDataKec = @json($tooltip_gender_kecamatan ?? []);
            var percentages = @json($percentages_kecamatan ?? []);

            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_kecamatan)
                }],
                chart: {
                    height: 350,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                colors: @json($warnakec),
                xaxis: {
                    categories: @json($labels_kecamatan),
                    position: 'bottom',
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    crosshairs: { fill: { type: 'gradient', gradient: { colorFrom: '#D8E3F0', colorTo: '#BED1E6', stops: [0, 100], opacityFrom: 0.4, opacityTo: 0.5, } } },
                    tooltip: { enabled: true }
                },
                yaxis: {
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { formatter: function (val) { return val; } }
                },
                title: {
                    text: @json($judul_pie_kecamatan),
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_kecamatan),
                    align: 'center'
                },
                tooltip: {
                    enabled: true,
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        var total = series[seriesIndex][dataPointIndex];
                        var gender = genderDataKec[dataPointIndex] || {L: 0, P: 0};
                        var perc = percentages[dataPointIndex] || 0;
                        
                        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + w.globals.labels[dataPointIndex] + '</div>' +
                               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
                               '<span class="apexcharts-tooltip-marker" style="background-color: #ADFF2F;"></span>' +
                               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
                               '</div></div>';
                    }
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 400 },
                        xaxis: { labels: { rotate: -90, style: { fontSize: '8px' } } },
                        dataLabels: { enabled: false }
                    }
                }, {
                    breakpoint: 480,
                    options: {
                        chart: { height: 350 },
                        legend: { position: 'bottom', fontSize: '9px' }
                    }
                }]
            };

            var chart = new ApexCharts(document.querySelector("#kecamatan"), options);
            chart.render();
        });
    </script>

    {{-- Kelurahan --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var genderDataKel = @json($tooltip_gender_kel ?? []);
            var percentages = @json($percentages_kel ?? []);

            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_sql_kel)
                }],
                chart: {
                    height: 350,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                colors: @json($warna_sql_kelurahan),
                xaxis: {
                    categories: @json($labels_kel),
                    position: 'bottom',
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    crosshairs: { fill: { type: 'gradient', gradient: { colorFrom: '#D8E3F0', colorTo: '#BED1E6', stops: [0, 100], opacityFrom: 0.4, opacityTo: 0.5, } } },
                    tooltip: { enabled: true },
                    labels: {
                        formatter: function (val) {
                            if (typeof val === 'string' && val.length > 10) {
                                return val.substring(0, 30) + '...';
                            }
                            return val;
                        }
                    }
                },
                yaxis: {
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { formatter: function (val) { return val; } }
                },
                title: {
                    text: @json($judul_pie_sql_kel),
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_sql_kel),
                    align: 'center'
                },
                tooltip: {
                    enabled: true,
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        var total = series[seriesIndex][dataPointIndex];
                        var gender = genderDataKel[dataPointIndex] || {L: 0, P: 0};
                        var perc = percentages[dataPointIndex] || 0;
                        
                        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + w.globals.labels[dataPointIndex] + '</div>' +
                               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
                               '<span class="apexcharts-tooltip-marker" style="background-color: #4169E1;"></span>' +
                               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
                               '</div></div>';
                    }
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 400 },
                        xaxis: { labels: { rotate: -90, style: { fontSize: '8px' } } },
                        dataLabels: { enabled: false }
                    }
                }, {
                    breakpoint: 480,
                    options: {
                        chart: { height: 350 },
                        legend: { position: 'bottom', fontSize: '9px' }
                    }
                }]
            };

            var chart = new ApexCharts(document.querySelector("#kelurahan"), options);
            chart.render();
        });
    </script>

    
    {{-- Chart Pie Cara Bayar --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var genderDataBayar = @json($tooltip_gender_cara_bayar ?? []);
            var percentages = @json($percentages_cara_bayar ?? []);

            var options = {
                series: @json($datacara_bayar),
                chart: {
                    type: 'pie',
                    height: 350
                },
                labels: @json($labelcara_bayar),
                colors: @json($warnabayar),
                title: {
                    text: @json($judul_pie_cara_bayar),
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_cara_bayar),
                    align: 'center'
                },
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    enabled: true,
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        var total = series[seriesIndex];
                        var gender = genderDataBayar[seriesIndex] || {L: 0, P: 0};
                        var perc = percentages[seriesIndex] || 0;

                        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + w.globals.labels[seriesIndex] + '</div>' +
                               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
                               '<span class="apexcharts-tooltip-marker" style="background-color: ' + w.config.colors[seriesIndex] + ';"></span>' +
                               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
                               '</div></div>';
                    }
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

            var chart = new ApexCharts(document.querySelector("#chart_cara_bayar"), options);
            chart.render();
        });
    </script>

        
    {{-- Chart Pie Status --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var options = {
                series: @json($datastts),
                chart: {
                    type: 'pie',
                    height: 350
                },
                labels: @json($labelsstts),
                colors: @json($warnastts),
                title: {
                    text: @json($judul_pie_stts),
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_stts),
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

            var chart = new ApexCharts(document.querySelector("#chart_stts"), options);
            chart.render();
        });
    </script>

    {{-- Chart Bar Diagnosa --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var genderDataDiagnosa = @json($tooltip_gender_diagnosa ?? []);
            var percentages = @json($percentages_diagnosa ?? []);
            var fullNames = @json($fullnames_diagnosa ?? []);
            var kodeDiagnosa = @json($kode_diagnosa ?? []);

            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_sqldiagnosa),
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                colors: @json($warna_sqldiagnosa),
                xaxis: {
                    categories: @json($labelsdiagnosa),
                    position: 'bottom',
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    crosshairs: { fill: { type: 'gradient', gradient: { colorFrom: '#D8E3F0', colorTo: '#BED1E6', stops: [0, 100], opacityFrom: 0.4, opacityTo: 0.5, } } },
                    tooltip: { enabled: true },
                    labels: {
                        formatter: function (val) {
                            if (typeof val === 'string' && val.length > 10) {
                                return val.substring(0, 25) + '...';
                            }
                            return val;
                        }
                    }
                },
                yaxis: {
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { formatter: function (val) { return val; } }
                },
                title: {
                    text: @json($judul_pie_sqldiagnosa),
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_sqldiagnosa),
                    align: 'center'
                },
                tooltip: {
                    enabled: true,
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        var total = series[seriesIndex][dataPointIndex];
                        var gender = genderDataDiagnosa[dataPointIndex] || {L: 0, P: 0};
                        var perc = percentages[dataPointIndex] || 0;
                        var namaLengkap = fullNames[dataPointIndex] || 'Unknown'; 
                        var kode = kodeDiagnosa[dataPointIndex] || '-';

                        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + kode + ' - ' + namaLengkap + '</div>' +
                               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
                               '<span class="apexcharts-tooltip-marker" style="background-color: #9ea10d;"></span>' +
                               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
                               '</div></div>';
                    }
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 400 },
                        xaxis: { labels: { rotate: -90, style: { fontSize: '8px' } } },
                        dataLabels: { enabled: false }
                    }
                }, {
                    breakpoint: 480,
                    options: {
                        chart: { height: 350 },
                        legend: { position: 'bottom', fontSize: '9px' }
                    }
                }]
            };

            var chart = new ApexCharts(document.querySelector("#chart_diagnosa"), options);
            chart.render();
        });
    </script>

    {{-- Chart Bar Prosedur --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var genderDataProsedur = @json($tooltip_gender_prosedur ?? []);
            var percentages = @json($percentages_prosedur ?? []);
            var fullNames = @json($fullnames_prosedur ?? []);
            var kodeProsedur = @json($kode_prosedur ?? []);

            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_sqlprosedur),
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                colors: @json($warna_sqlprosedur),
                xaxis: {
                    categories: @json($labelsprosedur),
                    position: 'bottom',
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    crosshairs: { fill: { type: 'gradient', gradient: { colorFrom: '#D8E3F0', colorTo: '#BED1E6', stops: [0, 100], opacityFrom: 0.4, opacityTo: 0.5, } } },
                    tooltip: { enabled: true },
                    labels: {
                        formatter: function (val) {
                            if (typeof val === 'string' && val.length > 10) {
                                return val.substring(0, 25) + '...';
                            }
                            return val;
                        }
                    }
                },
                yaxis: {
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { formatter: function (val) { return val; } }
                },
                title: {
                    text: @json($judul_pie_sqlprosedur),
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_sqlprosedur),
                    align: 'center'
                },
                tooltip: {
                    enabled: true,
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        var total = series[seriesIndex][dataPointIndex];
                        var gender = genderDataProsedur[dataPointIndex] || {L: 0, P: 0};
                        var perc = percentages[dataPointIndex] || 0;
                        var namaLengkap = fullNames[dataPointIndex] || 'Unknown';
                        var kode = kodeProsedur[dataPointIndex] || '-';
                        
                        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + kode + ' - ' + namaLengkap + '</div>' +
                               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
                               '<span class="apexcharts-tooltip-marker" style="background-color: #0da168;"></span>' +
                               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
                               '</div></div>';
                    }
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 400 },
                        xaxis: { labels: { rotate: -90, style: { fontSize: '8px' } } },
                        dataLabels: { enabled: false }
                    }
                }, {
                    breakpoint: 480,
                    options: {
                        chart: { height: 350 },
                        legend: { position: 'bottom', fontSize: '9px' }
                    }
                }]
            };

            var chart = new ApexCharts(document.querySelector("#chart_prosedur"), options);
            chart.render();
        });
    </script>

    {{-- Rujukan Masuk --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var genderDataRujuk = @json($tooltip_gender_rujuk ?? []);
            var percentages = @json($percentages_rujuk_masuk ?? []);

            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_sql_rujuk_masuk)
                }],
                chart: {
                    height: 350,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                colors: @json($warnaperujuk),
                xaxis: {
                    categories: @json($labels_rujuk_masuk),
                    position: 'bottom',
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    crosshairs: { fill: { type: 'gradient', gradient: { colorFrom: '#D8E3F0', colorTo: '#BED1E6', stops: [0, 100], opacityFrom: 0.4, opacityTo: 0.5, } } },
                    tooltip: { enabled: true },
                    labels: {
                        formatter: function (val) {
                            if (typeof val === 'string' && val.length > 10) {
                                return val.substring(0, 25) + '...';
                            }
                            return val;
                        }
                    }
                },
                yaxis: {
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { formatter: function (val) { return val; } }
                },
                title: {
                    text: @json($judul_pie_sql_rujuk_masuk),
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_sql_rujuk_masuk),
                    align: 'center'
                },
                tooltip: {
                    enabled: true,
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        var total = series[seriesIndex][dataPointIndex];
                        var gender = genderDataRujuk[dataPointIndex] || {L: 0, P: 0};
                        var perc = percentages[dataPointIndex] || 0;
                        
                        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + w.globals.labels[dataPointIndex] + '</div>' +
                               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
                               '<span class="apexcharts-tooltip-marker" style="background-color: #00FFFF;"></span>' +
                               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
                               '</div></div>';
                    }
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 400 },
                        xaxis: { labels: { rotate: -90, style: { fontSize: '8px' } } },
                        dataLabels: { enabled: false }
                    }
                }, {
                    breakpoint: 480,
                    options: {
                        chart: { height: 350 },
                        legend: { position: 'bottom', fontSize: '9px' }
                    }
                }]
            };

            var chart = new ApexCharts(document.querySelector("#rujuk_masuk"), options);
            chart.render();
        });
    </script>

    {{-- Chart Bar Dokter --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var genderDataDokter = @json($tooltip_gender_dokter ?? []);
            var percentages = @json($percentages_dokter ?? []);

            var options = {
                series: [{
                    name: 'Jumlah Pasien',
                    data: @json($datadokter)
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                colors: @json($warnadokter),
                xaxis: {
                    categories: @json($labeldokter),
                    position: 'bottom',
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    crosshairs: { fill: { type: 'gradient', gradient: { colorFrom: '#D8E3F0', colorTo: '#BED1E6', stops: [0, 100], opacityFrom: 0.4, opacityTo: 0.5, } } },
                    tooltip: { enabled: true },
                    labels: {
                        formatter: function (val) {
                            if (typeof val === 'string' && val.length > 10) {
                                return val.substring(0, 30) + '...';
                            }
                            return val;
                        }
                    },
                },
                yaxis: {
                    title: {
                        text: 'Jumlah Pasien'
                    }
                },
                title: {
                    text: @json($judul_pie_dokter),
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_dokter),
                    align: 'center'
                },
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    enabled: true,
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        var total = series[seriesIndex][dataPointIndex];
                        var gender = genderDataDokter[dataPointIndex] || {L: 0, P: 0};
                        var perc = percentages[dataPointIndex] || 0;
                        
                        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + w.globals.labels[dataPointIndex] + '</div>' +
                               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
                               '<span class="apexcharts-tooltip-marker" style="background-color: #008FFB;"></span>' +
                               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
                               '</div></div>';
                    }
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 400 },
                        xaxis: { labels: { rotate: -90, style: { fontSize: '8px' } } },
                        dataLabels: { enabled: false }
                    }
                }, {
                    breakpoint: 480,
                    options: {
                        chart: { height: 350 },
                        legend: { position: 'bottom', fontSize: '9px' }
                    }
                }]
            };

            var chart = new ApexCharts(document.querySelector("#chart_dokter"), options);
            chart.render();
        });
    </script>

    {{-- Status Daftar --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var genderDataSttsDaftar = @json($tooltip_gender_stts_daftar ?? []);
            var percentages = @json($percentages_stts_daftar ?? []);

            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_stts_daftar)
                }],
                chart: {
                    height: 350,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                colors: @json($warnastts_daftar),
                xaxis: {
                    categories: @json($labels_stts_daftar),
                    position: 'bottom',
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    crosshairs: { fill: { type: 'gradient', gradient: { colorFrom: '#D8E3F0', colorTo: '#BED1E6', stops: [0, 100], opacityFrom: 0.4, opacityTo: 0.5, } } },
                    tooltip: { enabled: true }
                },
                yaxis: {
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { formatter: function (val) { return val; } }
                },
                title: {
                    text: @json($judul_bar_stts_daftar),
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_bar_stts_daftar),
                    align: 'center'
                },
                tooltip: {
                    enabled: true,
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        var total = series[seriesIndex][dataPointIndex];
                        var gender = genderDataSttsDaftar[dataPointIndex] || {L: 0, P: 0};
                        var perc = percentages[dataPointIndex] || 0;
                        
                        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + w.globals.labels[dataPointIndex] + '</div>' +
                               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
                               '<span class="apexcharts-tooltip-marker" style="background-color: #3cb371;"></span>' +
                               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Laki-laki: </span><span class="apexcharts-tooltip-text-value">' + gender.L + '</span></div>' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Perempuan: </span><span class="apexcharts-tooltip-text-value">' + gender.P + '</span></div>' +
                               '</div></div>';
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#status_daftar"), options);
            chart.render();
        });
    </script>

    {{-- Chart Bar Pelayanan --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var percentages = @json($percentages_pelayanan ?? []);
            var fullNames = @json($fullnames_pelayanan ?? []);

            var options = {
                series: [{
                    name: 'Jumlah Pelayanan',
                    data: @json($datapel)
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                colors: @json($warnapel),
                xaxis: {
                    categories: @json($labelspel),
                    position: 'bottom',
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    crosshairs: { fill: { type: 'gradient', gradient: { colorFrom: '#D8E3F0', colorTo: '#BED1E6', stops: [0, 100], opacityFrom: 0.4, opacityTo: 0.5, } } },
                    tooltip: { enabled: true },
                    labels: {
                        formatter: function (val) {
                            if (typeof val === 'string' && val.length > 10) {
                                return val.substring(0, 20) + '...';
                            }
                            return val;
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'Jumlah Pelayanan'
                    }
                },
                title: {
                    text: @json($judul_pie_pel),
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_pel),
                    align: 'center'
                },
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    enabled: true,
                    custom: function({series, seriesIndex, dataPointIndex, w}) {
                        var total = series[seriesIndex][dataPointIndex];
                        var perc = percentages[dataPointIndex] || 0;
                        var namaLengkap = fullNames[dataPointIndex] || 'Unknown';
                        
                        return '<div class="apexcharts-tooltip-title" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' + namaLengkap + '</div>' +
                               '<div class="apexcharts-tooltip-series-group apexcharts-active" style="order: 1; display: flex;">' +
                               '<span class="apexcharts-tooltip-marker" style="background-color: #008FFB;"></span>' +
                               '<div class="apexcharts-tooltip-text" style="font-family: Helvetica, Arial, sans-serif; font-size: 12px;">' +
                               '<div class="apexcharts-tooltip-y-group"><span class="apexcharts-tooltip-text-label">Total: </span><span class="apexcharts-tooltip-text-value">' + total + ' (' + perc + '%)</span></div>' +
                               '</div></div>';
                    }
                },
                responsive: [{
                    breakpoint: 768,
                    options: {
                        chart: { height: 400 },
                        xaxis: { labels: { rotate: -90, style: { fontSize: '8px' } } },
                        dataLabels: { enabled: false }
                    }
                }, {
                    breakpoint: 480,
                    options: {
                        chart: { height: 350 },
                        legend: { position: 'bottom', fontSize: '9px' }
                    }
                }]
            };

            var chart = new ApexCharts(document.querySelector("#chart_pelayanan"), options);
            chart.render();
        });
    </script>

    {{-- Jenis Kelamin --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_jk)
                }],
                chart: {
                    height: 350,
                    type: 'bar',
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top',
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val;
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                colors: @json($warnajk),
                xaxis: {
                    categories: @json($labels_jk),
                    position: 'bottom',
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    crosshairs: { fill: { type: 'gradient', gradient: { colorFrom: '#D8E3F0', colorTo: '#BED1E6', stops: [0, 100], opacityFrom: 0.4, opacityTo: 0.5, } } },
                    tooltip: { enabled: true },
                    labels: {
                        formatter: function (val) {
                            if (typeof val === 'string' && val.length > 10) {
                                return val.substring(0, 10) + '...';
                            }
                            return val;
                        }
                    }
                },
                yaxis: {
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: { formatter: function (val) { return val; } }
                },
                title: {
                    text: @json($judul_bar_jk),
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_bar_jk),
                    align: 'center'
                }
            };

            var chart = new ApexCharts(document.querySelector("#jenis_kelamin"), options);
            chart.render();
        });
    </script>

</div>
@endsection
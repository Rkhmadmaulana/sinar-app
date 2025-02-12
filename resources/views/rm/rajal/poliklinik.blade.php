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
                                          @if(isset($tgl1))
                                          <input type="date" value="{{ $tgl1 }}" class="form-control" name="tgl1">
                                          @else
                                          <input type="date" class="form-control" name="tgl1">
                                          @endif
                                        </dd>
                                      </div>
                                    </div>
                                  </div>
                                  <div class="col-md-2">
                                    <div class="form-group">
                                      <div class="form-line">
                                        <dt>Sampai Tanggal</dt>
                                        <dd>
                                          @if(isset($tgl2))
                                          <input type="date" value="{{ $tgl2 }}" class="form-control" name="tgl2">
                                          @else
                                          <input type="date" class="form-control" name="tgl2">
                                          @endif
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
                    data: @json($bpjs) // Data BPJS dari PHP
                }, {
                    name: 'Umum',
                    data: @json($umum) // Data Umum dari PHP
                }],
                chart: {
                    height: 350,
                    type: 'line',
                    zoom: {
                        enabled: false
                    }
                },
                colors: ['#008FFB', '#FF4560'], // Contoh warna untuk BPJS dan Umum
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth'
                },
                title: {
                    text: @json($judul_line), // Judul chart dari PHP
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_line), // Subjudul chart dari PHP
                    align: 'center'
                },
                xaxis: {
                    categories: @json($labelstat), // Label sumbu X dari PHP (misalnya, bulan atau tanggal)
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
                }
            };

            var chart = new ApexCharts(document.querySelector("#chart_line"), options);
            chart.render();
        });
    </script>

    {{-- Chart Bar Poli --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data), // Data untuk bar chart dari PHP
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top', // Posisi label data
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val; // Menampilkan nilai data
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                colors: @json($warnapoli), // Warna bar chart dari PHP
                xaxis: {
                    categories: @json($labels), // Label sumbu X dari PHP
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
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
                            return val; // Menampilkan nilai sumbu Y
                        }
                    }
                },
                title: {
                    text: @json($judul_pie_poli), // Judul chart dari PHP
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_poli), // Subjudul chart dari PHP
                    align: 'center'
                }
            };

            var chart = new ApexCharts(document.querySelector("#chart_poli"), options);
            chart.render();
        });
    </script>

    {{-- Kabupaten --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_sql_kab) // Data jumlah dari PHP
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
                colors: @json($warna_sql_Kabupaten), // Warna bar dari PHP
                xaxis: {
                    categories: @json($labels_kab), // Label sumbu X dari PHP
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
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
                    text: @json($judul_pie_sql_kab), // Judul chart dari PHP
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_sql_kab), // Subjudul chart dari PHP
                    align: 'center'
                }
            };

            var chart = new ApexCharts(document.querySelector("#kabupaten"), options);
            chart.render();
        });
    </script>

    {{-- Kecamatan --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_kecamatan) // Data jumlah dari PHP
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
                colors: @json($warnakec), // Warna bar dari PHP
                xaxis: {
                    categories: @json($labels_kecamatan), // Label sumbu X dari PHP
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
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
                    text: @json($judul_pie_kecamatan), // Judul chart dari PHP
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_kecamatan), // Subjudul chart dari PHP
                    align: 'center'
                }
            };

            var chart = new ApexCharts(document.querySelector("#kecamatan"), options);
            chart.render();
        });
    </script>

    {{-- Kelurahan --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_sql_kel) // Data jumlah dari PHP
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
                colors: @json($warna_sql_kelurahan), // Warna bar dari PHP
                xaxis: {
                    categories: @json($labels_kel), // Label sumbu X dari PHP
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
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
                    text: @json($judul_pie_sql_kel), // Judul chart dari PHP
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_sql_kel), // Subjudul chart dari PHP
                    align: 'center'
                }
            };

            var chart = new ApexCharts(document.querySelector("#kelurahan"), options);
            chart.render();
        });
    </script>

    
    {{-- Chart Pie Cara Bayar --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var options = {
                series: @json($datacara_bayar), // Data untuk pie chart dari PHP
                chart: {
                    type: 'pie',
                    height: 350
                },
                labels: @json($labelcara_bayar), // Label untuk setiap bagian pie dari PHP
                colors: @json($warnabayar), // Warna untuk setiap bagian pie dari PHP
                title: {
                    text: @json($judul_pie_cara_bayar), // Judul chart dari PHP
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_cara_bayar), // Subjudul chart dari PHP
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

            var chart = new ApexCharts(document.querySelector("#chart_cara_bayar"), options);
            chart.render();
        });
    </script>

        
    {{-- Chart Pie Status --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var options = {
                series: @json($datastts), // Data untuk pie chart dari PHP
                chart: {
                    type: 'pie',
                    height: 350
                },
                labels: @json($labelsstts), // Label untuk setiap bagian pie dari PHP
                colors: @json($warnastts), // Warna untuk setiap bagian pie dari PHP
                title: {
                    text: @json($judul_pie_stts), // Judul chart dari PHP
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_stts), // Subjudul chart dari PHP
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
            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_sqldiagnosa), // Data untuk bar chart dari PHP
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top', // Posisi label data
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val; // Menampilkan nilai data
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                colors: @json($warna_sqldiagnosa), // Warna bar chart dari PHP
                xaxis: {
                    categories: @json($labelsdiagnosa), // Label sumbu X dari PHP
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
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
                            return val; // Menampilkan nilai sumbu Y
                        }
                    }
                },
                title: {
                    text: @json($judul_pie_sqldiagnosa), // Judul chart dari PHP
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_sqldiagnosa), // Subjudul chart dari PHP
                    align: 'center'
                }
            };

            var chart = new ApexCharts(document.querySelector("#chart_diagnosa"), options);
            chart.render();
        });
    </script>

    {{-- Chart Bar Prosedur --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_sqlprosedur), // Data untuk bar chart dari PHP
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top', // Posisi label data
                        },
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val; // Menampilkan nilai data
                    },
                    offsetY: -20,
                    style: {
                        fontSize: '12px',
                        colors: ["#304758"]
                    }
                },
                colors: @json($warna_sqlprosedur), // Warna bar chart dari PHP
                xaxis: {
                    categories: @json($labelsprosedur), // Label sumbu X dari PHP
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
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
                            return val; // Menampilkan nilai sumbu Y
                        }
                    }
                },
                title: {
                    text: @json($judul_pie_sqlprosedur), // Judul chart dari PHP
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_sqlprosedur), // Subjudul chart dari PHP
                    align: 'center'
                }
            };

            var chart = new ApexCharts(document.querySelector("#chart_prosedur"), options);
            chart.render();
        });
    </script>

    {{-- Rujukan Masuk --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_sql_rujuk_masuk) // Data jumlah dari PHP
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
                colors: @json($warnaperujuk), // Warna bar dari PHP
                xaxis: {
                    categories: @json($labels_rujuk_masuk), // Label sumbu X dari PHP
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
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
                    text: @json($judul_pie_sql_rujuk_masuk), // Judul chart dari PHP
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_sql_rujuk_masuk), // Subjudul chart dari PHP
                    align: 'center'
                }
            };

            var chart = new ApexCharts(document.querySelector("#rujuk_masuk"), options);
            chart.render();
        });
    </script>

    {{-- Chart Bar Dokter --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var options = {
                series: [{
                    name: 'Jumlah Pasien',
                    data: @json($datadokter) // Data untuk chart batang dari PHP
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top', // Menampilkan label di atas bar
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
                colors: @json($warnadokter), // Warna untuk setiap bar dari PHP
                xaxis: {
                    categories: @json($labeldokter), // Label untuk setiap bar dari PHP
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
                    }
                },
                yaxis: {
                    title: {
                        text: 'Jumlah Pasien'
                    }
                },
                title: {
                    text: @json($judul_pie_dokter), // Judul chart dari PHP
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_dokter), // Subjudul chart dari PHP
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

            var chart = new ApexCharts(document.querySelector("#chart_dokter"), options);
            chart.render();
        });
    </script>

    {{-- Status Daftar --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var options = {
                series: [{
                    name: 'Jumlah',
                    data: @json($data_stts_daftar) // Data jumlah dari PHP
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
                colors: @json($warnastts_daftar), // Warna bar dari PHP
                xaxis: {
                    categories: @json($labels_stts_daftar), // Label sumbu X dari PHP
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
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
                    text: @json($judul_bar_stts_daftar), // Judul chart dari PHP
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_bar_stts_daftar), // Subjudul chart dari PHP
                    align: 'center'
                }
            };

            var chart = new ApexCharts(document.querySelector("#status_daftar"), options);
            chart.render();
        });
    </script>

    {{-- Chart Bar Pelayanan --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var options = {
                series: [{
                    name: 'Jumlah Pelayanan',
                    data: @json($datapel) // Data untuk chart batang dari PHP
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        borderRadius: 10,
                        dataLabels: {
                            position: 'top', // Posisi label data di atas batang
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
                colors: @json($warnapel), // Warna untuk setiap batang dari PHP
                xaxis: {
                    categories: @json($labelspel), // Label untuk setiap batang dari PHP
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
                    }
                },
                yaxis: {
                    title: {
                        text: 'Jumlah Pelayanan'
                    }
                },
                title: {
                    text: @json($judul_pie_pel), // Judul chart dari PHP
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_pie_pel), // Subjudul chart dari PHP
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
                    data: @json($data_jk) // Data jumlah dari PHP
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
                colors: @json($warnajk), // Warna bar dari PHP
                xaxis: {
                    categories: @json($labels_jk), // Label sumbu X dari PHP
                    position: 'bottom',
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    },
                    crosshairs: {
                        fill: {
                            type: 'gradient',
                            gradient: {
                                colorFrom: '#D8E3F0',
                                colorTo: '#BED1E6',
                                stops: [0, 100],
                                opacityFrom: 0.4,
                                opacityTo: 0.5,
                            }
                        }
                    },
                    tooltip: {
                        enabled: true,
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
                    text: @json($judul_bar_jk), // Judul chart dari PHP
                    align: 'center'
                },
                subtitle: {
                    text: @json($subjudul_bar_jk), // Subjudul chart dari PHP
                    align: 'center'
                }
            };

            var chart = new ApexCharts(document.querySelector("#jenis_kelamin"), options);
            chart.render();
        });
    </script>

</div>
@endsection
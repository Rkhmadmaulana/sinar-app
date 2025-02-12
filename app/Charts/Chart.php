<?php

namespace App\Charts;

use ArielMejiaDev\LarapexCharts\LarapexChart;

class Chart
{
    protected $line;
    protected $pie;
    protected $bar;

    public function __construct(LarapexChart $line, LarapexChart $pie, LarapexChart $bar)
    {   
        $this->line = $line;
        $this->pie = $pie;
        $this->bar = $bar;
        
    }
    public function lineranap($non,$bkk,$jr,$bls,$bpjs,$umum,$labelstat,$judul,$subjudul): \ArielMejiaDev\LarapexCharts\LineChart
    {
        return $this->line->lineChart()
            ->setTitle($judul)
            ->setSubtitle($subjudul)
            ->addData('BPJS', $bpjs)
            ->addData('BPJS Ketenaga Kerjaan', $bkk)
            ->addData('UMUM', $umum)
            ->addData('Bayi Lahir Sehat', $bls)
            ->addData('Jasa Raharja', $jr)
            ->addData('-', $non)
            ->setXAxis($labelstat)
            ->setGrid()
            ;
    }
    public function lineranapkelas($vvip,$vip,$utama,$kelas1,$kelas2,$kelas3,$labelstat,$judul,$subjudul): \ArielMejiaDev\LarapexCharts\LineChart
    {
        return $this->line->lineChart()
            ->setTitle($judul)
            ->setSubtitle($subjudul)
            ->addData('Kelas 3', $kelas3)
            ->addData('Kelas 2', $kelas2)
            ->addData('Kelas 1', $kelas1)
            ->addData('Kelas Utama', $utama)
            ->addData('Kelas VIP', $vip)
            ->addData('Kelas VVIP', $vvip)
            ->setXAxis($labelstat)
            ->setGrid()
            ;
    }
    public function line($bpjs,$umum,$labelstat,$judul,$subjudul): \ArielMejiaDev\LarapexCharts\LineChart
    {
        return $this->line->lineChart()
            ->setTitle($judul)
            ->setSubtitle($subjudul)
            
            ->addData('BPJS', $bpjs)
            ->addData('UMUM', $umum)
            ->setXAxis($labelstat)
            ->setGrid()
            ;
    }
    public function line2($dewasa,$bayi,$labelstat,$judul,$subjudul): \ArielMejiaDev\LarapexCharts\LineChart
    {
        return $this->line->lineChart()
            ->setTitle($judul)
            ->setSubtitle($subjudul)
            ->addData('Dewasa', $dewasa)
            ->addData('Bayi', $bayi)
            ->setXAxis($labelstat)
            ->setGrid()
            ;
    }

    public function pie($data,$label,$judul,$subjudul,$warna): \ArielMejiaDev\LarapexCharts\PieChart
    {
        return $this->pie->pieChart()
            ->setTitle($judul)
            ->setSubtitle($subjudul)
            ->addData($data)
            ->setLabels($label)
            ->setColors($warna);
    }
    public function bar($data, $labels, $judul, $subjudul, $warna): \ArielMejiaDev\LarapexCharts\BarChart
    {
        return $this->bar->barChart()
            ->setTitle($judul)
            ->setSubtitle($subjudul)
            ->setLabels($labels)
            ->addData('Jumlah ', $data)
            ->setColors($warna)
            ->setGrid()
            ;
    }
    
    
    
    



}

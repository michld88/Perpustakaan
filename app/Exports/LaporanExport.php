<?php

namespace App\Exports;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Denda;
use App\Models\Peminjaman;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(
        private string $jenis,
        private int    $tahun
    ) {}

    public function collection()
    {
        return match($this->jenis) {
            'buku_terpopuler'  => $this->bukuTerpopuler(),
            'anggota_teraktif' => $this->anggotaTeraktif(),
            'denda'            => $this->denda(),
            default            => $this->peminjaman(),
        };
    }

    public function headings(): array
    {
        return match($this->jenis) {
            'buku_terpopuler'  => ['No', 'Judul Buku', 'Kategori', 'Penulis', 'Total Dipinjam'],
            'anggota_teraktif' => ['No', 'Nama Anggota', 'NIM/NIP', 'Email', 'Total Peminjaman'],
            'denda'            => ['No', 'Nama Anggota', 'Hari Terlambat', 'Total Denda', 'Status', 'Tanggal Bayar'],
            default            => ['No', 'Nama Anggota', 'Buku Dipinjam', 'Tgl Pinjam', 'Jatuh Tempo', 'Status'],
        };
    }

    public function title(): string
    {
        return 'Laporan ' . ucfirst(str_replace('_', ' ', $this->jenis)) . ' ' . $this->tahun;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function peminjaman()
    {
        return Peminjaman::with(['anggota.user', 'detail.buku'])
            ->whereYear('tanggal_pinjam', $this->tahun)
            ->get()
            ->map(fn ($p, $i) => [
                $i + 1,
                $p->anggota->user->name ?? '-',
                $p->detail->map(fn ($d) => $d->buku->judul)->join(', '),
                $p->tanggal_pinjam->format('d/m/Y'),
                $p->tanggal_jatuh_tempo->format('d/m/Y'),
                $p->status,
            ]);
    }

    private function bukuTerpopuler()
    {
        return Buku::withCount(['detail as total_dipinjam' => function ($q) {
                $q->whereHas('peminjaman', fn ($q) => $q->whereYear('tanggal_pinjam', $this->tahun));
            }])
            ->with(['kategori', 'penulis'])
            ->orderByDesc('total_dipinjam')
            ->get()
            ->map(fn ($b, $i) => [
                $i + 1,
                $b->judul,
                $b->kategori->nama ?? '-',
                $b->penulis->map(fn ($p) => $p->nama)->join(', '),
                $b->total_dipinjam,
            ]);
    }

    private function anggotaTeraktif()
    {
        return Anggota::withCount(['peminjaman as total_peminjaman' => function ($q) {
                $q->whereYear('tanggal_pinjam', $this->tahun);
            }])
            ->with('user')
            ->orderByDesc('total_peminjaman')
            ->get()
            ->map(fn ($a, $i) => [
                $i + 1,
                $a->user->name ?? '-',
                $a->nim_nip ?? '-',
                $a->user->email ?? '-',
                $a->total_peminjaman,
            ]);
    }

    private function denda()
    {
        return Denda::with(['peminjaman.anggota.user'])
            ->whereYear('created_at', $this->tahun)
            ->get()
            ->map(fn ($d, $i) => [
                $i + 1,
                $d->peminjaman->anggota->user->name ?? '-',
                $d->hari_terlambat,
                'Rp ' . number_format($d->total_denda, 0, ',', '.'),
                $d->status === 'sudah_bayar' ? 'Sudah Bayar' : 'Belum Bayar',
                $d->tanggal_bayar ? $d->tanggal_bayar->format('d/m/Y') : '-',
            ]);
    }
}
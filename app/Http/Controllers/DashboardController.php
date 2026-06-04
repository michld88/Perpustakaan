<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Denda;
use App\Models\Peminjaman;
use App\Models\Reservasi;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function admin(): Response
    {
        $stats = [
            'total_buku'             => Buku::count(),
            'total_anggota'          => Anggota::where('status', 'aktif')->count(),
            'peminjaman_hari_ini'    => Peminjaman::whereDate('tanggal_pinjam', today())->count(),
            'peminjaman_aktif'       => Peminjaman::whereIn('status', ['dipinjam', 'terlambat'])->count(),
            'buku_terlambat'         => Peminjaman::where('status', 'terlambat')->count(),
            'denda_belum_bayar'      => Denda::where('status', 'belum_bayar')->sum('total_denda'),
            'reservasi_menunggu'     => Reservasi::where('status', 'menunggu')->count(),
            'total_peminjaman_bulan' => Peminjaman::whereMonth('tanggal_pinjam', now()->month)
                                        ->whereYear('tanggal_pinjam', now()->year)->count(),
        ];

        $terlambat = Peminjaman::with(['anggota.user', 'detail.buku'])
            ->where('status', 'terlambat')
            ->latest()->limit(5)->get();

        $peminjamanTerbaru = Peminjaman::with(['anggota.user', 'detail.buku'])
            ->latest()->limit(5)->get()
            ->map(fn ($p) => [
                'id'                  => $p->id,
                'anggota'             => $p->anggota?->user?->name,
                'buku'                => $p->detail->map(fn ($d) => $d->buku?->judul)->join(', '),
                'tanggal_pinjam'      => $p->tanggal_pinjam?->format('d/m/Y'),
                'tanggal_jatuh_tempo' => $p->tanggal_jatuh_tempo?->format('d/m/Y'),
                'status'              => $p->status,
            ]);

        $bukuPopuler = Buku::withCount(['detail as total_dipinjam' => fn ($q) =>
                $q->whereHas('peminjaman', fn ($q) => $q
                    ->whereMonth('tanggal_pinjam', now()->month)
                    ->whereYear('tanggal_pinjam', now()->year))])
            ->with('kategori')
            ->orderByDesc('total_dipinjam')
            ->limit(5)->get();

        $grafikBulanan = [];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $grafikBulanan[] = [
                'label'  => $bulan->format('M Y'),
                'jumlah' => Peminjaman::whereMonth('tanggal_pinjam', $bulan->month)
                    ->whereYear('tanggal_pinjam', $bulan->year)->count(),
            ];
        }

        return Inertia::render('Dashboard/AdminDashboard', [
            'stats'             => $stats,
            'terlambat'         => $terlambat,
            'peminjamanTerbaru' => $peminjamanTerbaru,
            'bukuPopuler'       => $bukuPopuler,
            'grafikBulanan'     => $grafikBulanan,
        ]);
    }

    public function pustakawan(): Response
    {
        $stats = [
            'total_buku'           => Buku::count(),
            'total_anggota'        => Anggota::where('status', 'aktif')->count(),
            'peminjaman_hari_ini'  => Peminjaman::whereDate('tanggal_pinjam', today())->count(),
            'peminjaman_aktif'     => Peminjaman::whereIn('status', ['dipinjam', 'terlambat'])->count(),
            'buku_terlambat'       => Peminjaman::where('status', 'terlambat')->count(),
            'reservasi_menunggu'   => Reservasi::where('status', 'menunggu')->count(),
        ];

        $peminjamanTerbaru = Peminjaman::with(['anggota.user', 'detail.buku'])
            ->latest()->limit(5)->get()
            ->map(fn ($p) => [
                'id'                  => $p->id,
                'anggota'             => $p->anggota?->user?->name,
                'buku'                => $p->detail->map(fn ($d) => $d->buku?->judul)->join(', '),
                'tanggal_pinjam'      => $p->tanggal_pinjam?->format('d/m/Y'),
                'tanggal_jatuh_tempo' => $p->tanggal_jatuh_tempo?->format('d/m/Y'),
                'status'              => $p->status,
            ]);

        $terlambat = Peminjaman::with(['anggota.user', 'detail.buku'])
            ->where('status', 'terlambat')
            ->latest()->limit(5)->get();

        return Inertia::render('Dashboard/PustakawanDashboard', [
            'stats'             => $stats,
            'peminjamanTerbaru' => $peminjamanTerbaru,
            'terlambat'         => $terlambat,
        ]);
    }

    public function anggota(): Response
    {
        $user    = auth()->user();
        $anggota = $user->anggota;

        $stats = [
            'peminjaman_aktif'     => 0,
            'peminjaman_terlambat' => 0,
            'denda_belum_bayar'    => 0,
            'reservasi_aktif'      => 0,
        ];

        $peminjamanAktif = collect();
        $riwayatTerbaru  = collect();
        $reservasiAktif  = collect();

        if ($anggota) {
            $stats = [
                'peminjaman_aktif'     => Peminjaman::where('anggota_id', $anggota->id)
                    ->whereIn('status', ['dipinjam', 'terlambat'])->count(),
                'peminjaman_terlambat' => Peminjaman::where('anggota_id', $anggota->id)
                    ->where('status', 'terlambat')->count(),
                'denda_belum_bayar'    => Denda::whereHas('peminjaman', fn ($q) =>
                    $q->where('anggota_id', $anggota->id))
                    ->where('status', 'belum_bayar')->sum('total_denda'),
                'reservasi_aktif'      => Reservasi::where('anggota_id', $anggota->id)
                    ->whereIn('status', ['menunggu', 'tersedia'])->count(),
            ];

            $peminjamanAktif = Peminjaman::with(['detail.buku'])
                ->where('anggota_id', $anggota->id)
                ->whereIn('status', ['dipinjam', 'terlambat'])
                ->get()->map(fn ($p) => [
                    'id'                  => $p->id,
                    'buku'                => $p->detail->map(fn ($d) => $d->buku?->judul)->join(', '),
                    'tanggal_pinjam'      => $p->tanggal_pinjam?->format('d/m/Y'),
                    'tanggal_jatuh_tempo' => $p->tanggal_jatuh_tempo?->format('d/m/Y'),
                    'status'              => $p->status,
                ]);

            $riwayatTerbaru = Peminjaman::with(['detail.buku'])
                ->where('anggota_id', $anggota->id)
                ->where('status', 'dikembalikan')
                ->latest()->limit(5)->get()
                ->map(fn ($p) => [
                    'id'              => $p->id,
                    'buku'            => $p->detail->map(fn ($d) => $d->buku?->judul)->join(', '),
                    'tanggal_pinjam'  => $p->tanggal_pinjam?->format('d/m/Y'),
                    'tanggal_kembali' => $p->tanggal_kembali?->format('d/m/Y'),
                ]);

            $reservasiAktif = Reservasi::with(['buku'])
                ->where('anggota_id', $anggota->id)
                ->whereIn('status', ['menunggu', 'tersedia'])->get();
        }

        return Inertia::render('Dashboard/AnggotaDashboard', [
            'stats'           => $stats,
            'peminjamanAktif' => $peminjamanAktif,
            'riwayatTerbaru'  => $riwayatTerbaru,
            'reservasiAktif'  => $reservasiAktif,
        ]);
    }
}
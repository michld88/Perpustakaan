<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Denda;
use App\Models\Peminjaman;
use App\Models\Anggota;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LaporanController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Laporan/Index');
    }

    public function statistikPeminjaman(Request $request): Response
    {
        $periode = $request->get('periode', 'bulanan');
        $tahun   = $request->get('tahun', now()->year);
        $bulan   = $request->get('bulan', now()->month);

        $data = match($periode) {
            'harian'   => $this->getDataHarian($tahun, $bulan),
            'mingguan' => $this->getDataMingguan($tahun, $bulan),
            'tahunan'  => $this->getDataTahunan($tahun),
            default    => $this->getDataBulanan($tahun),
        };

        // Summary statistik
        $summary = [
            'total_peminjaman'    => Peminjaman::whereYear('tanggal_pinjam', $tahun)->count(),
            'total_dikembalikan'  => Peminjaman::whereYear('tanggal_pinjam', $tahun)->where('status', 'dikembalikan')->count(),
            'total_terlambat'     => Peminjaman::whereYear('tanggal_pinjam', $tahun)->where('status', 'terlambat')->count(),
            'total_aktif'         => Peminjaman::whereIn('status', ['dipinjam', 'terlambat'])->count(),
        ];

        return Inertia::render('Laporan/StatistikPeminjaman', [
            'data'    => $data,
            'summary' => $summary,
            'filters' => $request->only(['periode', 'tahun', 'bulan']),
            'tahunList' => range(now()->year - 3, now()->year),
        ]);
    }

    public function bukuTerpopuler(Request $request): Response
    {
        $tahun = $request->get('tahun', now()->year);
        $limit = $request->get('limit', 10);

        $buku = Buku::withCount(['detail as total_dipinjam' => function ($q) use ($tahun) {
                $q->whereHas('peminjaman', fn ($q) => $q->whereYear('tanggal_pinjam', $tahun));
            }])
            ->with(['kategori', 'penulis'])
            ->orderByDesc('total_dipinjam')
            ->limit($limit)
            ->get();

        return Inertia::render('Laporan/BukuTerpopuler', [
            'buku'      => $buku,
            'filters'   => $request->only(['tahun', 'limit']),
            'tahunList' => range(now()->year - 3, now()->year),
        ]);
    }

    public function anggotaTeraktif(Request $request): Response
    {
        $tahun = $request->get('tahun', now()->year);
        $limit = $request->get('limit', 10);

        $anggota = Anggota::withCount(['peminjaman as total_peminjaman' => function ($q) use ($tahun) {
                $q->whereYear('tanggal_pinjam', $tahun);
            }])
            ->with('user')
            ->orderByDesc('total_peminjaman')
            ->limit($limit)
            ->get();

        return Inertia::render('Laporan/AnggotaTeraktif', [
            'anggota'   => $anggota,
            'filters'   => $request->only(['tahun', 'limit']),
            'tahunList' => range(now()->year - 3, now()->year),
        ]);
    }

    public function dendaTerkumpul(Request $request): Response
    {
        $tahun = $request->get('tahun', now()->year);
        $bulan = $request->get('bulan', '');

        $query = Denda::with(['peminjaman.anggota.user'])
            ->whereYear('created_at', $tahun);

        if ($bulan) {
            $query->whereMonth('created_at', $bulan);
        }

        $denda = $query->paginate(15)->withQueryString();

        $totalBelumBayar = Denda::whereYear('created_at', $tahun)
            ->when($bulan, fn ($q) => $q->whereMonth('created_at', $bulan))
            ->where('status', 'belum_bayar')
            ->sum('total_denda');

        $totalSudahBayar = Denda::whereYear('created_at', $tahun)
            ->when($bulan, fn ($q) => $q->whereMonth('created_at', $bulan))
            ->where('status', 'sudah_bayar')
            ->sum('total_denda');

        return Inertia::render('Laporan/DendaTerkumpul', [
            'denda'           => $denda,
            'totalBelumBayar' => $totalBelumBayar,
            'totalSudahBayar' => $totalSudahBayar,
            'filters'         => $request->only(['tahun', 'bulan']),
            'tahunList'       => range(now()->year - 3, now()->year),
        ]);
    }

    // ==================== PBI-037 & 038: EKSPOR ====================

    public function eksporPDF(Request $request)
    {
        $jenis = $request->get('jenis', 'peminjaman');
        $tahun = $request->get('tahun', now()->year);

        $data = match($jenis) {
            'buku_terpopuler'   => $this->getDataBukuTerpopuler($tahun),
            'anggota_teraktif'  => $this->getDataAnggotaTeraktif($tahun),
            'denda'             => $this->getDataDenda($tahun),
            default             => $this->getDataPeminjaman($tahun),
        };

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf', [
            'jenis' => $jenis,
            'tahun' => $tahun,
            'data'  => $data,
        ]);

        return $pdf->download("laporan-{$jenis}-{$tahun}.pdf");
    }

    public function eksporExcel(Request $request)
    {
        $jenis = $request->get('jenis', 'peminjaman');
        $tahun = $request->get('tahun', now()->year);

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LaporanExport($jenis, $tahun),
            "laporan-{$jenis}-{$tahun}.xlsx"
        );
    }

    // ==================== PRIVATE HELPERS ====================

    private function getDataHarian(int $tahun, int $bulan): array
    {
        $result = [];
        $daysInMonth = Carbon::create($tahun, $bulan)->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $tanggal = Carbon::create($tahun, $bulan, $day);
            $result[] = [
                'label'  => $tanggal->format('d/m'),
                'jumlah' => Peminjaman::whereDate('tanggal_pinjam', $tanggal->toDateString())->count(),
            ];
        }

        return $result;
    }

    private function getDataMingguan(int $tahun, int $bulan): array
    {
        $result = [];
        $start  = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $end    = $start->copy()->endOfMonth();
        $week   = 1;

        while ($start->lte($end)) {
            $weekEnd = $start->copy()->endOfWeek()->min($end);
            $result[] = [
                'label'  => 'Minggu ' . $week,
                'jumlah' => Peminjaman::whereBetween('tanggal_pinjam', [
                    $start->toDateString(),
                    $weekEnd->toDateString(),
                ])->count(),
            ];
            $start = $weekEnd->addDay();
            $week++;
        }

        return $result;
    }

    private function getDataBulanan(int $tahun): array
    {
        $namaBulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $result    = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $result[] = [
                'label'  => $namaBulan[$bulan - 1],
                'jumlah' => Peminjaman::whereYear('tanggal_pinjam', $tahun)
                    ->whereMonth('tanggal_pinjam', $bulan)
                    ->count(),
            ];
        }

        return $result;
    }

    private function getDataTahunan(int $tahun): array
    {
        $result = [];
        for ($y = $tahun - 4; $y <= $tahun; $y++) {
            $result[] = [
                'label'  => (string) $y,
                'jumlah' => Peminjaman::whereYear('tanggal_pinjam', $y)->count(),
            ];
        }
        return $result;
    }

    private function getDataPeminjaman(int $tahun): array
    {
        return Peminjaman::with(['anggota.user', 'detail.buku'])
            ->whereYear('tanggal_pinjam', $tahun)
            ->get()
            ->toArray();
    }

    private function getDataBukuTerpopuler(int $tahun): array
    {
        return Buku::withCount(['detail as total_dipinjam' => function ($q) use ($tahun) {
                $q->whereHas('peminjaman', fn ($q) => $q->whereYear('tanggal_pinjam', $tahun));
            }])
            ->with(['kategori', 'penulis'])
            ->orderByDesc('total_dipinjam')
            ->limit(20)
            ->get()
            ->toArray();
    }

    private function getDataAnggotaTeraktif(int $tahun): array
    {
        return Anggota::withCount(['peminjaman as total_peminjaman' => function ($q) use ($tahun) {
                $q->whereYear('tanggal_pinjam', $tahun);
            }])
            ->with('user')
            ->orderByDesc('total_peminjaman')
            ->limit(20)
            ->get()
            ->toArray();
    }

    private function getDataDenda(int $tahun): array
    {
        return Denda::with(['peminjaman.anggota.user'])
            ->whereYear('created_at', $tahun)
            ->get()
            ->toArray();
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Denda;
use App\Models\Peminjaman;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\ReservasiController;
use App\Notifications\NotifikasiDenda;

class PengembalianController extends Controller
{
    const TARIF_DENDA_PER_HARI = 1000;
    const DURASI_PERPANJANGAN  = 7;

    public function index(Request $request): Response
    {
        $query = Peminjaman::with(['anggota.user', 'detail.buku', 'denda'])
            ->whereIn('status', ['dipinjam', 'terlambat'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('anggota.user', fn ($q) =>
                $q->where('name', 'like', "%{$search}%")
            );
        }

        $peminjaman = $query->paginate(10)->withQueryString();

        return Inertia::render('Pengembalian/Index', [
            'peminjaman' => $peminjaman,
            'filters'    => $request->only(['search']),
        ]);
    }

    public function show(Peminjaman $peminjaman): Response
    {
        $peminjaman->load(['anggota.user', 'detail.buku.kategori', 'pustakawan', 'denda']);

        $hariTerlambat = 0;
        $totalDenda    = 0;

        if ($peminjaman->status === 'dipinjam' &&
            now()->startOfDay()->gt($peminjaman->tanggal_jatuh_tempo->startOfDay())) {
            $hariTerlambat = now()->startOfDay()->diffInDays($peminjaman->tanggal_jatuh_tempo->startOfDay());
            $totalDenda    = $hariTerlambat * self::TARIF_DENDA_PER_HARI;
        }

        return Inertia::render('Pengembalian/Show', [
            'peminjaman'    => $peminjaman,
            'hariTerlambat' => $hariTerlambat,
            'totalDenda'    => $totalDenda,
            'tarifPerHari'  => self::TARIF_DENDA_PER_HARI,
        ]);
    }

    public function proses(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        if ($peminjaman->status === 'dikembalikan') {
            return back()->withErrors(['error' => 'Buku sudah dikembalikan.']);
        }

        $tanggalKembaliStr = now()->toDateString();

        $jatuhTempoStr = \Carbon\Carbon::parse(
            $peminjaman->getRawOriginal('tanggal_jatuh_tempo')
        )->toDateString();

        $hariTerlambat = 0;
        $totalDenda = 0;

        if ($tanggalKembaliStr > $jatuhTempoStr) {

            $hariTerlambat = \Carbon\Carbon::parse($jatuhTempoStr)
                ->diffInDays(\Carbon\Carbon::parse($tanggalKembaliStr));

            $totalDenda = $hariTerlambat * self::TARIF_DENDA_PER_HARI;

            Denda::create([
                'peminjaman_id'  => $peminjaman->id,
                'hari_terlambat' => $hariTerlambat,
                'tarif_per_hari' => self::TARIF_DENDA_PER_HARI,
                'total_denda'    => $totalDenda,
                'status'         => 'belum_bayar',
            ]);

            $dendaRecord = Denda::create([
                'peminjaman_id'  => $peminjaman->id,
                'hari_terlambat' => $hariTerlambat,
                'tarif_per_hari' => self::TARIF_DENDA_PER_HARI,
                'total_denda'    => $totalDenda,
                'status'         => 'belum_bayar',
            ]);

            // Kirim notifikasi denda ke anggota
            $peminjaman->anggota->user->notify(new NotifikasiDenda($dendaRecord));
        }

        $peminjaman->update([
            'status'          => 'dikembalikan',
            'tanggal_kembali' => $tanggalKembaliStr,
        ]);

        ActivityLog::catat(
            'pengembalian.proses',
            "Pengembalian buku ID {$peminjaman->id}" . ($hariTerlambat > 0 ? " dengan denda Rp " . number_format($totalDenda, 0, ',', '.') : " tepat waktu"),
            'Peminjaman',
            $peminjaman->id
        );

        foreach ($peminjaman->detail as $detail) {
            $detail->buku->increment('jumlah_tersedia');

            if ($detail->buku->jumlah_tersedia > 0) {
                $detail->buku->update(['status' => 'tersedia']);
            }
        }

        // Trigger notifikasi reservasi jika ada
        foreach ($peminjaman->detail as $detail) {
            ReservasiController::notifikasiReservasiTersedia($detail->buku_id);
        }

        $pesan = $hariTerlambat > 0
            ? "Buku berhasil dikembalikan. Denda: Rp " . number_format($totalDenda, 0, ',', '.')
            : 'Buku berhasil dikembalikan tepat waktu.';

        return redirect()->route('pengembalian.index')
            ->with('success', $pesan);
    }

    public function perpanjang(Peminjaman $peminjaman): RedirectResponse
    {
        if ($peminjaman->sudah_diperpanjang) {
            return back()->withErrors(['error' => 'Peminjaman hanya bisa diperpanjang 1 kali.']);
        }

        if ($peminjaman->status !== 'dipinjam') {
            return back()->withErrors(['error' => 'Hanya peminjaman aktif yang bisa diperpanjang.']);
        }

        if ($peminjaman->isTerlambat()) {
            return back()->withErrors(['error' => 'Peminjaman yang sudah terlambat tidak bisa diperpanjang.']);
        }

        $jatuhTempoBaru = $peminjaman->tanggal_jatuh_tempo->copy()->addDays(self::DURASI_PERPANJANGAN);

        $peminjaman->update([
            'tanggal_jatuh_tempo'  => $jatuhTempoBaru->toDateString(),
            'sudah_diperpanjang'   => true,
            'tanggal_perpanjangan' => now()->toDateString(),
        ]);

        return redirect()->route('pengembalian.index')
            ->with('success', 'Masa peminjaman diperpanjang hingga ' . $jatuhTempoBaru->format('d/m/Y'));
    }

    public function denda(Request $request): Response
    {
        $query = Denda::with(['peminjaman.anggota.user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $denda = $query->paginate(10)->withQueryString();

        return Inertia::render('Pengembalian/Denda', [
            'denda'   => $denda,
            'filters' => $request->only(['status']),
        ]);
    }

    public function bayarDenda(Denda $denda): RedirectResponse
    {
        if ($denda->status === 'sudah_bayar') {
            return back()->withErrors(['error' => 'Denda sudah dibayar.']);
        }

        $denda->update([
            'status'        => 'sudah_bayar',
            'tanggal_bayar' => now(),
        ]);

        return redirect()->route('pengembalian.denda')
            ->with('success', 'Denda berhasil dibayar.');
    }
}
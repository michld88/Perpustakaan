<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Reservasi;
use App\Notifications\ReservasiBukuTersedia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReservasiController extends Controller
{
    public function index(Request $request): Response
    {
        $user    = auth()->user();
        $anggota = $user->anggota;

        $query = Reservasi::with(['buku.kategori', 'anggota.user'])
            ->latest();

        // Anggota hanya lihat reservasi sendiri
        if ($user->role->name === 'anggota') {
            $query->where('anggota_id', $anggota->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reservasi = $query->paginate(10)->withQueryString();

        return Inertia::render('Reservasi/Index', [
            'reservasi' => $reservasi,
            'filters'   => $request->only(['status']),
        ]);
    }

    public function create(): Response
    {
        // Hanya tampilkan buku yang sedang dipinjam semua eksemplarnya
        $buku = Buku::where('jumlah_tersedia', 0)
            ->where('status', 'dipinjam')
            ->with(['kategori', 'penulis'])
            ->get();

        return Inertia::render('Reservasi/Create', [
            'buku' => $buku,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'buku_id' => 'required|exists:buku,id',
        ], [
            'buku_id.required' => 'Pilih buku yang ingin direservasi.',
        ]);

        $user    = auth()->user();
        $anggota = $user->anggota;

        if (!$anggota) {
            return back()->withErrors(['error' => 'Data anggota tidak ditemukan.']);
        }

        // Cek buku masih dipinjam
        $buku = Buku::findOrFail($validated['buku_id']);
        if ($buku->jumlah_tersedia > 0) {
            return back()->withErrors(['buku_id' => 'Buku sudah tersedia, langsung pinjam saja.']);
        }

        // Cek sudah pernah reservasi buku yang sama
        $existing = Reservasi::where('anggota_id', $anggota->id)
            ->where('buku_id', $validated['buku_id'])
            ->whereIn('status', ['menunggu', 'tersedia'])
            ->first();

        if ($existing) {
            return back()->withErrors(['buku_id' => 'Anda sudah mereservasi buku ini.']);
        }

        Reservasi::create([
            'anggota_id'        => $anggota->id,
            'buku_id'           => $validated['buku_id'],
            'status'            => 'menunggu',
            'tanggal_reservasi' => now()->toDateString(),
        ]);

        return redirect()->route('reservasi.index')
            ->with('success', 'Reservasi berhasil dibuat. Anda akan diberitahu saat buku tersedia.');
    }

    public function destroy(Reservasi $reservasi): RedirectResponse
    {
        $user = auth()->user();

        // Anggota hanya bisa batalkan reservasi sendiri
        if ($user->role->name === 'anggota' && $reservasi->anggota_id !== $user->anggota->id) {
            abort(403);
        }

        if ($reservasi->status === 'diambil') {
            return back()->withErrors(['error' => 'Reservasi yang sudah diambil tidak bisa dibatalkan.']);
        }

        $reservasi->update(['status' => 'dibatalkan']);

        return redirect()->route('reservasi.index')
            ->with('success', 'Reservasi berhasil dibatalkan.');
    }

    public function ambil(Reservasi $reservasi): RedirectResponse
    {
        if ($reservasi->status !== 'tersedia') {
            return back()->withErrors(['error' => 'Reservasi belum tersedia untuk diambil.']);
        }

        $reservasi->update(['status' => 'diambil']);

        return redirect()->route('reservasi.index')
            ->with('success', 'Reservasi berhasil diambil.');
    }

    // Dipanggil otomatis saat buku dikembalikan
    public static function notifikasiReservasiTersedia(int $bukuId): void
    {
        $reservasi = Reservasi::with(['anggota.user', 'buku'])
            ->where('buku_id', $bukuId)
            ->where('status', 'menunggu')
            ->orderBy('tanggal_reservasi')
            ->first();

        if (!$reservasi) return;

        $reservasi->update([
            'status'              => 'tersedia',
            'tanggal_notifikasi'  => now()->toDateString(),
            'tanggal_kadaluarsa'  => now()->addDays(2)->toDateString(),
        ]);

        // Kirim notifikasi email
        $reservasi->anggota->user->notify(new ReservasiBukuTersedia($reservasi));
    }

    public function kelola(Request $request): Response
    {
        $reservasi = Reservasi::with(['buku.kategori', 'anggota.user'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Reservasi/Kelola', [
            'reservasi' => $reservasi,
        ]);
    }

}
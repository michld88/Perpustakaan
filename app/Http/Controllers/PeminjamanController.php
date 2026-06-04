<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Buku;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PeminjamanController extends Controller
{
    // Maksimal buku yang bisa dipinjam per anggota
    const KUOTA_PEMINJAMAN = 3;
    // Durasi peminjaman dalam hari
    const DURASI_PEMINJAMAN = 7;

    public function index(Request $request): Response
    {
        $query = Peminjaman::with(['anggota.user', 'detail.buku', 'pustakawan'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('anggota.user', fn ($q) =>
                $q->where('name', 'like', "%{$search}%")
            )->orWhereHas('anggota', fn ($q) =>
                $q->where('nim_nip', 'like', "%{$search}%")
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $peminjaman = $query->paginate(10)->withQueryString();

        return Inertia::render('Peminjaman/Index', [
            'peminjaman' => $peminjaman,
            'filters'    => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Peminjaman/Create', [
            'anggota' => Anggota::with('user')
                ->where('status', 'aktif')
                ->get()
                ->map(fn ($a) => [
                    'id'      => $a->id,
                    'nama'    => $a->user->name,
                    'nim_nip' => $a->nim_nip,
                    'email'   => $a->user->email,
                ]),
            'buku' => Buku::where('jumlah_tersedia', '>', 0)
                ->where('status', 'tersedia')
                ->with(['kategori', 'penulis'])
                ->get(),
            'kuota'   => self::KUOTA_PEMINJAMAN,
            'durasi'  => self::DURASI_PEMINJAMAN,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'anggota_id' => 'required|exists:anggota,id',
            'buku_ids'   => 'required|array|min:1|max:' . self::KUOTA_PEMINJAMAN,
            'buku_ids.*' => 'exists:buku,id',
            'catatan'    => 'nullable|string',
        ], [
            'anggota_id.required' => 'Anggota wajib dipilih.',
            'buku_ids.required'   => 'Pilih minimal 1 buku.',
            'buku_ids.max'        => 'Maksimal ' . self::KUOTA_PEMINJAMAN . ' buku per peminjaman.',
        ]);

        $anggota = Anggota::with('user')->findOrFail($validated['anggota_id']);

        // Validasi status anggota
        if ($anggota->status !== 'aktif') {
            return back()->withErrors(['anggota_id' => 'Anggota tidak aktif, tidak bisa meminjam buku.']);
        }

        // Validasi kuota peminjaman aktif
        $peminjamanAktif = Peminjaman::where('anggota_id', $anggota->id)
            ->where('status', 'dipinjam')
            ->withCount('detail')
            ->get()
            ->sum('detail_count');

        $jumlahBukuBaru = count($validated['buku_ids']);

        if (($peminjamanAktif + $jumlahBukuBaru) > self::KUOTA_PEMINJAMAN) {
            $sisa = self::KUOTA_PEMINJAMAN - $peminjamanAktif;
            return back()->withErrors([
                'buku_ids' => "Anggota hanya bisa meminjam {$sisa} buku lagi (kuota: " . self::KUOTA_PEMINJAMAN . " buku).",
            ]);
        }

        // Validasi buku tidak sedang dipinjam oleh anggota yang sama
        foreach ($validated['buku_ids'] as $bukuId) {
            $sudahDipinjam = Peminjaman::where('anggota_id', $anggota->id)
                ->where('status', 'dipinjam')
                ->whereHas('detail', fn ($q) => $q->where('buku_id', $bukuId))
                ->exists();

            if ($sudahDipinjam) {
                $buku = Buku::findOrFail($bukuId);
                return back()->withErrors([
                    'buku_ids' => "Anda masih meminjam buku '{$buku->judul}', harap kembalikan terlebih dahulu.",
                ]);
            }
        }

        // Validasi ketersediaan buku
        foreach ($validated['buku_ids'] as $bukuId) {
            $buku = Buku::findOrFail($bukuId);
            if ($buku->jumlah_tersedia <= 0) {
                return back()->withErrors([
                    'buku_ids' => "Buku '{$buku->judul}' tidak tersedia.",
                ]);
            }
        }

        // Buat peminjaman
        $peminjaman = Peminjaman::create([
            'anggota_id'          => $anggota->id,
            'pustakawan_id'       => auth()->id(),
            'tanggal_pinjam'      => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(self::DURASI_PEMINJAMAN)->toDateString(),
            'status'              => 'dipinjam',
            'catatan'             => $validated['catatan'] ?? null,
        ]);

        ActivityLog::catat(
            'peminjaman.buat',
            "Peminjaman baru untuk anggota {$anggota->user->name}",
            'Peminjaman',
            $peminjaman->id
        );

        // Simpan detail & kurangi stok buku
        foreach ($validated['buku_ids'] as $bukuId) {
            DetailPeminjaman::create([
                'peminjaman_id' => $peminjaman->id,
                'buku_id'       => $bukuId,
                'jumlah'        => 1,
            ]);

            $buku = Buku::findOrFail($bukuId);
            $buku->decrement('jumlah_tersedia');
            if ($buku->jumlah_tersedia <= 0) {
                $buku->update(['status' => 'dipinjam']);
            }
        }

        return redirect()->route('peminjaman.index')
            ->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function show(Peminjaman $peminjaman): Response
    {
        $peminjaman->load(['anggota.user', 'detail.buku.kategori', 'pustakawan']);

        return Inertia::render('Peminjaman/Show', [
            'peminjaman' => $peminjaman,
        ]);
    }

    public function destroy(Peminjaman $peminjaman): RedirectResponse
    {
        if ($peminjaman->status !== 'dipinjam') {
            return back()->withErrors(['error' => 'Peminjaman yang sudah dikembalikan tidak bisa dihapus.']);
        }

        // Kembalikan stok buku
        foreach ($peminjaman->detail as $detail) {
            $detail->buku->increment('jumlah_tersedia');
            if ($detail->buku->jumlah_tersedia > 0) {
                $detail->buku->update(['status' => 'tersedia']);
            }
        }

        $peminjaman->delete();

        return redirect()->route('peminjaman.index')
            ->with('success', 'Data peminjaman berhasil dihapus.');
    }

    public function riwayat(Request $request): Response
    {
        $anggotaId = $request->get('anggota_id');

        $query = Peminjaman::with(['anggota.user', 'detail.buku', 'pustakawan'])
            ->latest();

        if ($anggotaId) {
            $query->where('anggota_id', $anggotaId);
        }

        $peminjaman = $query->paginate(15)->withQueryString();

        return Inertia::render('Peminjaman/Riwayat', [
            'peminjaman' => $peminjaman,
            'anggota'    => Anggota::with('user')->get()->map(fn ($a) => [
                'id'   => $a->id,
                'nama' => $a->user->name,
            ]),
            'filters' => $request->only(['anggota_id']),
        ]);
    }

    public function peminjamanSaya(Request $request): Response
    {
        $user    = auth()->user();
        $anggota = $user->anggota;

        $peminjaman = Peminjaman::with(['detail.buku', 'pustakawan'])
            ->where('anggota_id', $anggota->id)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Peminjaman/PeminjamanSaya', [
            'peminjaman' => $peminjaman,
        ]);
    }

    public function perpanjangMandiri(Peminjaman $peminjaman): RedirectResponse
    {
        $user = auth()->user();

        // Pastikan ini peminjaman milik anggota yang login
        if ($peminjaman->anggota_id !== $user->anggota->id) {
            abort(403);
        }

        if ($peminjaman->sudah_diperpanjang) {
            return back()->withErrors(['error' => 'Peminjaman hanya bisa diperpanjang 1 kali.']);
        }

        if ($peminjaman->status !== 'dipinjam') {
            return back()->withErrors(['error' => 'Hanya peminjaman aktif yang bisa diperpanjang.']);
        }

        if ($peminjaman->isTerlambat()) {
            return back()->withErrors(['error' => 'Peminjaman yang sudah terlambat tidak bisa diperpanjang.']);
        }

        $jatuhTempoBaru = $peminjaman->tanggal_jatuh_tempo->copy()->addDays(7);

        $peminjaman->update([
            'tanggal_jatuh_tempo'  => $jatuhTempoBaru->toDateString(),
            'sudah_diperpanjang'   => true,
            'tanggal_perpanjangan' => now()->toDateString(),
        ]);

        ActivityLog::catat(
            'peminjaman.perpanjang',
            "Anggota {$user->name} memperpanjang peminjaman ID {$peminjaman->id}",
            'Peminjaman',
            $peminjaman->id
        );

        return back()->with('success', 'Peminjaman berhasil diperpanjang hingga ' . $jatuhTempoBaru->format('d/m/Y'));
    }
}
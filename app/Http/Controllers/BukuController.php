<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Penerbit;
use App\Models\Penulis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BukuController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Buku::with(['kategori', 'penerbit', 'penulis']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%")
                  ->orWhereHas('penulis', fn ($q) => $q->where('nama', 'like', "%{$search}%"))
                  ->orWhereHas('kategori', fn ($q) => $q->where('nama', 'like', "%{$search}%"));
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter kategori
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        // Sorting
        $sortField = $request->get('sort', 'judul');
        $sortDir   = $request->get('dir', 'asc');
        $allowedSorts = ['judul', 'isbn', 'tahun_terbit', 'jumlah_tersedia', 'status'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir);
        }

        $buku = $query->paginate(10)->withQueryString();

        return Inertia::render('Buku/Index', [
            'buku'      => $buku,
            'kategori'  => Kategori::all(),
            'filters'   => $request->only(['search', 'status', 'kategori_id', 'sort', 'dir']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Buku/Create', [
            'kategori' => Kategori::all(),
            'penerbit' => Penerbit::all(),
            'penulis'  => Penulis::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul'            => 'required|string|max:255',
            'isbn'             => 'required|string|unique:buku,isbn',
            'kategori_id'      => 'required|exists:kategori,id',
            'penerbit_id'      => 'required|exists:penerbit,id',
            'tahun_terbit'     => 'required|integer|min:1900|max:' . date('Y'),
            'jumlah_eksemplar' => 'required|integer|min:1',
            'deskripsi'        => 'nullable|string',
            'status'           => 'required|in:tersedia,dipinjam,rusak,hilang',
            'penulis_id'       => 'required|array|min:1',
            'penulis_id.*'     => 'exists:penulis,id',
        ], [
            'judul.required'        => 'Judul buku wajib diisi.',
            'isbn.required'         => 'ISBN wajib diisi.',
            'isbn.unique'           => 'ISBN sudah terdaftar.',
            'kategori_id.required'  => 'Kategori wajib dipilih.',
            'penerbit_id.required'  => 'Penerbit wajib dipilih.',
            'tahun_terbit.required' => 'Tahun terbit wajib diisi.',
            'jumlah_eksemplar.min'  => 'Jumlah eksemplar minimal 1.',
            'penulis_id.required'   => 'Penulis wajib dipilih minimal 1.',
        ]);

        $validated['jumlah_tersedia'] = $validated['jumlah_eksemplar'];

        $buku = Buku::create($validated);
        $buku->penulis()->sync($request->penulis_id);

        return redirect()->route('buku.index')
            ->with('success', 'Buku berhasil ditambahkan.');
    }

    public function show(Buku $buku): Response
    {
        $buku->load(['kategori', 'penerbit', 'penulis']);
        return Inertia::render('Buku/Show', ['buku' => $buku]);
    }

    public function edit(Buku $buku): Response
    {
        $buku->load('penulis');
        return Inertia::render('Buku/Edit', [
            'buku'     => $buku,
            'kategori' => Kategori::all(),
            'penerbit' => Penerbit::all(),
            'penulis'  => Penulis::all(),
        ]);
    }

    public function update(Request $request, Buku $buku): RedirectResponse
    {
        $validated = $request->validate([
            'judul'            => 'required|string|max:255',
            'isbn'             => 'required|string|unique:buku,isbn,' . $buku->id,
            'kategori_id'      => 'required|exists:kategori,id',
            'penerbit_id'      => 'required|exists:penerbit,id',
            'tahun_terbit'     => 'required|integer|min:1900|max:' . date('Y'),
            'jumlah_eksemplar' => 'required|integer|min:1',
            'deskripsi'        => 'nullable|string',
            'status'           => 'required|in:tersedia,dipinjam,rusak,hilang',
            'penulis_id'       => 'required|array|min:1',
            'penulis_id.*'     => 'exists:penulis,id',
        ]);

        // Hitung ulang jumlah tersedia
        $selisih = $validated['jumlah_eksemplar'] - $buku->jumlah_eksemplar;
        $validated['jumlah_tersedia'] = max(0, $buku->jumlah_tersedia + $selisih);

        $buku->update($validated);
        $buku->penulis()->sync($request->penulis_id);

        return redirect()->route('buku.index')
            ->with('success', 'Data buku berhasil diperbarui.');
    }

    public function destroy(Buku $buku): RedirectResponse
    {
        // Hanya admin dan pustakawan yang bisa menghapus
        if (!in_array(auth()->user()->role->name, ['admin', 'pustakawan'])) {
            abort(403);
        }

        if ($buku->jumlah_tersedia < $buku->jumlah_eksemplar) {
            return back()->withErrors(['error' => 'Buku sedang dipinjam, tidak bisa dihapus.']);
        }

        $buku->delete();

        return redirect()->route('buku.index')
            ->with('success', 'Buku berhasil dihapus.');
    }
}
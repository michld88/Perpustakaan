<?php
$content = <<<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\KartuAnggota;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AnggotaController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Anggota::with('user')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nim_nip', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%")
                                                      ->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $anggota = $query->paginate(10)->withQueryString();

        return Inertia::render('Anggota/Index', [
            'anggota' => $anggota,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Anggota/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'nim_nip'  => 'nullable|string|unique:anggota,nim_nip',
            'alamat'   => 'nullable|string',
            'telepon'  => 'nullable|string',
            'foto'     => 'nullable|image|max:2048',
        ], [
            'name.required'  => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.unique'   => 'Email sudah terdaftar.',
            'password.min'   => 'Password minimal 8 karakter.',
        ]);

        $anggotaRole = Role::where('name', 'anggota')->first();

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'role_id'   => $anggotaRole->id,
            'is_active' => true,
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('foto-anggota', 'public');
        }

        Anggota::create([
            'user_id'        => $user->id,
            'nim_nip'        => $validated['nim_nip'] ?? null,
            'alamat'         => $validated['alamat'] ?? null,
            'telepon'        => $validated['telepon'] ?? null,
            'foto'           => $fotoPath,
            'tanggal_daftar' => now(),
            'status'         => 'aktif',
        ]);

        return redirect()->route('anggota.index')
            ->with('success', 'Anggota berhasil didaftarkan.');
    }

    public function show(Anggota $anggota): Response
    {
        $anggota->load(['user', 'kartu']);
        return Inertia::render('Anggota/Show', ['anggota' => $anggota]);
    }

    public function edit(Anggota $anggota): Response
    {
        $anggota->load('user');
        return Inertia::render('Anggota/Edit', ['anggota' => $anggota]);
    }

    public function update(Request $request, Anggota $anggota): RedirectResponse
    {
        $anggota->load('user');

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => ['required', 'email', Rule::unique('users', 'email')->ignore($anggota->user_id)],
            'nim_nip' => ['nullable', 'string', Rule::unique('anggota', 'nim_nip')->ignore($anggota->id)],
            'alamat'  => 'nullable|string',
            'telepon' => 'nullable|string',
            'foto'    => 'nullable|image|max:2048',
            'status'  => 'required|in:aktif,nonaktif',
        ]);

        $anggota->user->update([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'is_active' => $validated['status'] === 'aktif',
        ]);

        if ($request->hasFile('foto')) {
            if ($anggota->foto) {
                Storage::disk('public')->delete($anggota->foto);
            }
            $anggota->foto = $request->file('foto')->store('foto-anggota', 'public');
        }

        $anggota->update([
            'nim_nip' => $validated['nim_nip'] ?? null,
            'alamat'  => $validated['alamat'] ?? null,
            'telepon' => $validated['telepon'] ?? null,
            'status'  => $validated['status'],
        ]);

        return redirect()->route('anggota.index')
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function destroy(Anggota $anggota): RedirectResponse
    {
        $anggota->load('user');

        if ($anggota->foto) {
            Storage::disk('public')->delete($anggota->foto);
        }

        if ($anggota->user) {
            $anggota->user->delete();
        } else {
            $anggota->delete();
        }

        return redirect()->route('anggota.index')
            ->with('success', 'Anggota berhasil dihapus.');
    }

    public function cetakKartu(Anggota $anggota)
    {
        $anggota->load('user');

        if (!$anggota->kartu) {
            $nomorKartu = 'LIB-' . str_pad($anggota->id, 6, '0', STR_PAD_LEFT);
            $qrContent  = route('anggota.show', $anggota->id);
            $qrPath     = 'qr-codes/qr-' . $anggota->id . '.svg';

            Storage::disk('public')->makeDirectory('qr-codes');
            $qrSvg = QrCode::format('svg')->size(200)->generate($qrContent);
            Storage::disk('public')->put($qrPath, $qrSvg);

            $kartu = KartuAnggota::create([
                'anggota_id'    => $anggota->id,
                'nomor_kartu'   => $nomorKartu,
                'qr_code_path'  => $qrPath,
                'tanggal_cetak' => now(),
            ]);
        } else {
            $kartu = $anggota->kartu;
        }

        return Inertia::render('Anggota/CetakKartu', [
            'anggota' => $anggota,
            'kartu'   => $kartu,
        ]);
    }
}
PHP;

file_put_contents('app/Http/Controllers/AnggotaController.php', $content);
echo 'Berhasil: ' . strlen($content) . ' bytes' . PHP_EOL;
echo 'Rule::unique: ' . (strpos($content, 'Rule::unique') ? 'ADA' : 'TIDAK') . PHP_EOL;
echo 'SVG: ' . (strpos($content, "format('svg')") ? 'ADA' : 'TIDAK') . PHP_EOL;
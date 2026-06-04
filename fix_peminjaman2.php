<?php
$content = file_get_contents('app/Http/Controllers/PeminjamanController.php');

// Tambahkan accessor format tanggal di bagian index
$old = "        $peminjaman = $query->paginate(10)->withQueryString();

        return Inertia::render('Peminjaman/Index', [
            'peminjaman' => $peminjaman,";

$new = "        $peminjaman = $query->paginate(10)->withQueryString();

        // Format tanggal agar mudah dibaca
        $peminjaman->getCollection()->transform(function ($p) {
            $p->tanggal_pinjam      = $p->tanggal_pinjam?->format('d/m/Y');
            $p->tanggal_jatuh_tempo = $p->tanggal_jatuh_tempo?->format('d/m/Y');
            $p->tanggal_kembali     = $p->tanggal_kembali?->format('d/m/Y');
            return $p;
        });

        return Inertia::render('Peminjaman/Index', [
            'peminjaman' => $peminjaman,";

$result = str_replace($old, $new, $content);

if ($content === $result) {
    echo 'WARN: Tidak ada yang diganti - coba cara lain' . PHP_EOL;
} else {
    file_put_contents('app/Http/Controllers/PeminjamanController.php', $result);
    echo 'Berhasil fix format tanggal index!' . PHP_EOL;
}
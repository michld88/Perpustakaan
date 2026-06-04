<?php
$content = file_get_contents('app/Http/Controllers/AnggotaController.php');

$old = "        // Temporary debug
        throw new \\Exception('DEBUG: anggota_id='.\$anggota->id.' user_id='.\$anggota->user_id.' email='.(\$anggota->user?->email ?? 'NULL').' nim_nip='.\$anggota->nim_nip);

        ";

$new = "        ";

$result = str_replace($old, $new, $content);

if ($content === $result) {
    echo 'WARN: Tidak ada yang diganti - cari manual' . PHP_EOL;
} else {
    file_put_contents('app/Http/Controllers/AnggotaController.php', $result);
    echo 'Debug dihapus' . PHP_EOL;
}
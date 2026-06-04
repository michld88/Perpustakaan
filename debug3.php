<?php
$content = file_get_contents('app/Http/Controllers/AnggotaController.php');

// Ganti bagian update dengan dd() untuk debug
$old = '$anggota = Anggota::with(\'user\')->findOrFail($anggota->id);

        \Log::info(\'UPDATE DEBUG\'';

$new = '$anggota = Anggota::with(\'user\')->findOrFail($anggota->id);

        // Temporary debug
        throw new \Exception(\'DEBUG: anggota_id=\'.$anggota->id.\' user_id=\'.$anggota->user_id.\' email=\'.($anggota->user?->email ?? \'NULL\').\' nim_nip=\'.$anggota->nim_nip);

        \Log::info(\'UPDATE DEBUG\'';

file_put_contents('app/Http/Controllers/AnggotaController.php', str_replace($old, $new, $content));
echo 'Done' . PHP_EOL;
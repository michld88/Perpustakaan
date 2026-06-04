<?php
$content = file_get_contents('resources/js/Layouts/AuthenticatedLayout.jsx');

$old = "{ href: '#', label: 'Peminjaman', icon: '📋' },";
$new = "{ href: route('peminjaman.index'), label: 'Peminjaman', icon: '📋' },";

$result = str_replace($old, $new, $content);

if ($content === $result) {
    echo 'WARN: Tidak ada yang diganti' . PHP_EOL;
} else {
    file_put_contents('resources/js/Layouts/AuthenticatedLayout.jsx', $result);
    echo 'Sidebar berhasil diupdate!' . PHP_EOL;
}
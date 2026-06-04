<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Penerbit;
use App\Models\Penulis;
use Illuminate\Database\Seeder;

class BukuSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriData = [
            ['nama' => 'Teknologi', 'deskripsi' => 'Buku teknologi dan komputer'],
            ['nama' => 'Sains', 'deskripsi' => 'Buku ilmu pengetahuan alam'],
            ['nama' => 'Matematika', 'deskripsi' => 'Buku matematika'],
            ['nama' => 'Sejarah', 'deskripsi' => 'Buku sejarah'],
            ['nama' => 'Sastra', 'deskripsi' => 'Buku sastra dan fiksi'],
        ];
        foreach ($kategoriData as $k) {
            Kategori::firstOrCreate(['nama' => $k['nama']], $k);
        }

        $penerbitData = [
            ['nama' => 'Gramedia', 'kota' => 'Jakarta'],
            ['nama' => 'Erlangga', 'kota' => 'Jakarta'],
            ['nama' => 'Andi Publisher', 'kota' => 'Yogyakarta'],
        ];
        foreach ($penerbitData as $p) {
            Penerbit::firstOrCreate(['nama' => $p['nama']], $p);
        }

        $penulisData = [
            ['nama' => 'Rinaldi Munir'],
            ['nama' => 'Andi Sunyoto'],
            ['nama' => 'Abdul Kadir'],
            ['nama' => 'Budi Raharjo'],
        ];
        foreach ($penulisData as $p) {
            Penulis::firstOrCreate(['nama' => $p['nama']], $p);
        }

        $bukuData = [
            [
                'judul'            => 'Algoritma dan Pemrograman',
                'isbn'             => '978-602-1234-01-1',
                'kategori_id'      => Kategori::where('nama', 'Teknologi')->first()->id,
                'penerbit_id'      => Penerbit::where('nama', 'Erlangga')->first()->id,
                'tahun_terbit'     => 2020,
                'jumlah_eksemplar' => 5,
                'jumlah_tersedia'  => 5,
                'status'           => 'tersedia',
                'deskripsi'        => 'Buku tentang algoritma dan pemrograman dasar',
                'penulis'          => ['Rinaldi Munir'],
            ],
            [
                'judul'            => 'Pemrograman Web dengan Laravel',
                'isbn'             => '978-602-1234-02-2',
                'kategori_id'      => Kategori::where('nama', 'Teknologi')->first()->id,
                'penerbit_id'      => Penerbit::where('nama', 'Andi Publisher')->first()->id,
                'tahun_terbit'     => 2022,
                'jumlah_eksemplar' => 3,
                'jumlah_tersedia'  => 3,
                'status'           => 'tersedia',
                'deskripsi'        => 'Buku pemrograman web menggunakan framework Laravel',
                'penulis'          => ['Andi Sunyoto'],
            ],
            [
                'judul'            => 'Dasar-Dasar Pemrograman',
                'isbn'             => '978-602-1234-03-3',
                'kategori_id'      => Kategori::where('nama', 'Teknologi')->first()->id,
                'penerbit_id'      => Penerbit::where('nama', 'Erlangga')->first()->id,
                'tahun_terbit'     => 2021,
                'jumlah_eksemplar' => 4,
                'jumlah_tersedia'  => 4,
                'status'           => 'tersedia',
                'deskripsi'        => 'Pengantar pemrograman untuk pemula',
                'penulis'          => ['Abdul Kadir'],
            ],
            [
                'judul'            => 'Kalkulus Dasar',
                'isbn'             => '978-602-1234-04-4',
                'kategori_id'      => Kategori::where('nama', 'Matematika')->first()->id,
                'penerbit_id'      => Penerbit::where('nama', 'Gramedia')->first()->id,
                'tahun_terbit'     => 2019,
                'jumlah_eksemplar' => 6,
                'jumlah_tersedia'  => 6,
                'status'           => 'tersedia',
                'deskripsi'        => 'Buku kalkulus untuk mahasiswa',
                'penulis'          => ['Budi Raharjo'],
            ],
            [
                'judul'            => 'Sejarah Indonesia Modern',
                'isbn'             => '978-602-1234-05-5',
                'kategori_id'      => Kategori::where('nama', 'Sejarah')->first()->id,
                'penerbit_id'      => Penerbit::where('nama', 'Gramedia')->first()->id,
                'tahun_terbit'     => 2018,
                'jumlah_eksemplar' => 2,
                'jumlah_tersedia'  => 2,
                'status'           => 'tersedia',
                'deskripsi'        => 'Sejarah Indonesia dari masa kolonial hingga modern',
                'penulis'          => ['Abdul Kadir'],
            ],
        ];

        foreach ($bukuData as $b) {
            $penulisNama = $b['penulis'];
            unset($b['penulis']);

            $buku = Buku::firstOrCreate(['isbn' => $b['isbn']], $b);
            $penulisIds = Penulis::whereIn('nama', $penulisNama)->pluck('id');
            $buku->penulis()->sync($penulisIds);
        }
    }
}
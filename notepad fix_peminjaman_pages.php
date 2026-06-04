<?php

// ========== INDEX ==========
$index = <<<'JSX'
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function PeminjamanIndex({ peminjaman, filters }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('peminjaman.index'), { search, status }, { preserveState: true });
    };

    const handleDelete = (id) => {
        if (confirm('Yakin ingin menghapus data peminjaman ini?')) {
            router.delete(route('peminjaman.destroy', id));
        }
    };

    const statusBadge = (status) => {
        const map = {
            dipinjam:      'bg-yellow-100 text-yellow-700',
            dikembalikan:  'bg-green-100 text-green-700',
            terlambat:     'bg-red-100 text-red-700',
        };
        return map[status] || 'bg-gray-100 text-gray-700';
    };

    return (
        <AuthenticatedLayout title="Peminjaman Buku">
            <Head title="Peminjaman Buku" />

            <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-bold text-gray-800">Daftar Peminjaman</h2>
                <div className="flex gap-2">
                    <Link href={route('peminjaman.riwayat')}
                        className="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                        Riwayat
                    </Link>
                    <Link href={route('peminjaman.create')}
                        className="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        + Peminjaman Baru
                    </Link>
                </div>
            </div>

            <div className="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form onSubmit={handleSearch} className="flex flex-wrap gap-3">
                    <input type="text" value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Cari nama anggota atau NIM/NIP..."
                        className="flex-1 min-w-48 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <select value={status} onChange={(e) => setStatus(e.target.value)}
                        className="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="dipinjam">Dipinjam</option>
                        <option value="dikembalikan">Dikembalikan</option>
                        <option value="terlambat">Terlambat</option>
                    </select>
                    <button type="submit"
                        className="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                        Cari
                    </button>
                    <Link href={route('peminjaman.index')}
                        className="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                        Reset
                    </Link>
                </form>
            </div>

            <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">No</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Anggota</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Buku Dipinjam</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Tgl Pinjam</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Jatuh Tempo</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {peminjaman.data.length === 0 ? (
                            <tr>
                                <td colSpan={7} className="text-center py-8 text-gray-400">
                                    Tidak ada data peminjaman
                                </td>
                            </tr>
                        ) : peminjaman.data.map((p, i) => (
                            <tr key={p.id} className="hover:bg-gray-50 transition">
                                <td className="px-4 py-3 text-gray-500">
                                    {(peminjaman.current_page - 1) * peminjaman.per_page + i + 1}
                                </td>
                                <td className="px-4 py-3">
                                    <p className="font-medium text-gray-800">{p.anggota?.user?.name}</p>
                                    <p className="text-xs text-gray-400">{p.anggota?.nim_nip}</p>
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {p.detail?.map((d) => (
                                        <p key={d.id} className="text-xs">{d.buku?.judul}</p>
                                    ))}
                                </td>
                                <td className="px-4 py-3 text-gray-600">{p.tanggal_pinjam}</td>
                                <td className="px-4 py-3 text-gray-600">{p.tanggal_jatuh_tempo}</td>
                                <td className="px-4 py-3">
                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${statusBadge(p.status)}`}>
                                        {p.status}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex gap-2">
                                        <Link href={route('peminjaman.show', p.id)}
                                            className="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                            Detail
                                        </Link>
                                        {p.status === 'dipinjam' && (
                                            <button onClick={() => handleDelete(p.id)}
                                                className="text-red-600 hover:text-red-800 text-xs font-medium">
                                                Hapus
                                            </button>
                                        )}
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                {peminjaman.last_page > 1 && (
                    <div className="px-4 py-3 border-t flex items-center justify-between">
                        <p className="text-sm text-gray-500">
                            Menampilkan {peminjaman.from}–{peminjaman.to} dari {peminjaman.total} data
                        </p>
                        <div className="flex gap-2">
                            {peminjaman.links.map((link, i) => (
                                <button key={i}
                                    onClick={() => link.url && router.get(link.url)}
                                    disabled={!link.url}
                                    className={`px-3 py-1 rounded text-sm ${link.active ? 'bg-blue-700 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'} disabled:opacity-40`}
                                    dangerouslySetInnerHTML={{ __html: link.label }} />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
JSX;

// ========== CREATE ==========
$create = <<<'JSX'
import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function PeminjamanCreate({ anggota, buku, kuota, durasi }) {
    const { data, setData, post, processing, errors } = useForm({
        anggota_id: '',
        buku_ids: [],
        catatan: '',
    });

    const [selectedAnggota, setSelectedAnggota] = useState(null);

    const handleAnggotaChange = (id) => {
        setData('anggota_id', id);
        const found = anggota.find((a) => a.id == id);
        setSelectedAnggota(found || null);
    };

    const toggleBuku = (id) => {
        const ids = data.buku_ids.includes(id)
            ? data.buku_ids.filter((b) => b !== id)
            : [...data.buku_ids, id];
        setData('buku_ids', ids);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('peminjaman.store'));
    };

    const tanggalJatuhTempo = () => {
        const d = new Date();
        d.setDate(d.getDate() + durasi);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
    };

    return (
        <AuthenticatedLayout title="Peminjaman Baru">
            <Head title="Peminjaman Baru" />

            <div className="max-w-4xl">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('peminjaman.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                    <h2 className="text-xl font-bold text-gray-800">Form Peminjaman Buku</h2>
                </div>

                <div className="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 text-sm text-blue-700">
                    <p>📋 Kuota maksimal: <strong>{kuota} buku</strong> per anggota</p>
                    <p>📅 Durasi peminjaman: <strong>{durasi} hari</strong> | Jatuh tempo: <strong>{tanggalJatuhTempo()}</strong></p>
                </div>

                <div className="bg-white rounded-xl shadow-sm p-6">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Pilih Anggota <span className="text-red-500">*</span>
                            </label>
                            <select value={data.anggota_id}
                                onChange={(e) => handleAnggotaChange(e.target.value)}
                                className={`w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.anggota_id ? 'border-red-400' : 'border-gray-300'}`}>
                                <option value="">-- Pilih Anggota --</option>
                                {anggota.map((a) => (
                                    <option key={a.id} value={a.id}>
                                        {a.nama} {a.nim_nip ? `(${a.nim_nip})` : ''}
                                    </option>
                                ))}
                            </select>
                            {errors.anggota_id && <p className="mt-1 text-xs text-red-600">{errors.anggota_id}</p>}
                            {selectedAnggota && (
                                <p className="mt-1 text-xs text-green-600">✓ {selectedAnggota.email}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Pilih Buku <span className="text-red-500">*</span>
                                <span className="ml-2 text-xs text-gray-400">(Maks. {kuota} buku)</span>
                            </label>
                            {errors.buku_ids && <p className="mb-2 text-xs text-red-600">{errors.buku_ids}</p>}
                            <div className="border border-gray-200 rounded-lg divide-y max-h-72 overflow-y-auto">
                                {buku.length === 0 ? (
                                    <p className="p-4 text-sm text-gray-400 text-center">Tidak ada buku tersedia</p>
                                ) : buku.map((b) => (
                                    <label key={b.id}
                                        className={`flex items-center gap-3 p-3 cursor-pointer hover:bg-gray-50 transition ${data.buku_ids.includes(b.id) ? 'bg-blue-50' : ''}`}>
                                        <input type="checkbox"
                                            checked={data.buku_ids.includes(b.id)}
                                            onChange={() => toggleBuku(b.id)}
                                            disabled={!data.buku_ids.includes(b.id) && data.buku_ids.length >= kuota}
                                            className="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-800">{b.judul}</p>
                                            <p className="text-xs text-gray-400">
                                                {b.kategori?.nama} • Tersedia: {b.jumlah_tersedia}
                                            </p>
                                        </div>
                                    </label>
                                ))}
                            </div>
                            <p className="mt-1 text-xs text-gray-500">
                                Dipilih: {data.buku_ids.length} dari {kuota} buku
                            </p>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea value={data.catatan}
                                onChange={(e) => setData('catatan', e.target.value)}
                                rows={3}
                                className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Catatan tambahan (opsional)" />
                        </div>

                        <div className="flex gap-3 pt-2">
                            <button type="submit" disabled={processing}
                                className="bg-blue-700 hover:bg-blue-800 disabled:opacity-60 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                                {processing ? 'Memproses...' : 'Proses Peminjaman'}
                            </button>
                            <Link href={route('peminjaman.index')}
                                className="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                                Batal
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
JSX;

// ========== SHOW ==========
$show = <<<'JSX'
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function PeminjamanShow({ peminjaman }) {
    const statusBadge = (status) => {
        const map = {
            dipinjam:     'bg-yellow-100 text-yellow-700',
            dikembalikan: 'bg-green-100 text-green-700',
            terlambat:    'bg-red-100 text-red-700',
        };
        return map[status] || 'bg-gray-100 text-gray-700';
    };

    return (
        <AuthenticatedLayout title="Detail Peminjaman">
            <Head title="Detail Peminjaman" />

            <div className="max-w-3xl">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('peminjaman.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                    <h2 className="text-xl font-bold text-gray-800">Detail Peminjaman</h2>
                </div>

                <div className="bg-white rounded-xl shadow-sm p-6 space-y-6">
                    <div className="flex items-center justify-between">
                        <h3 className="font-semibold text-gray-700">ID Peminjaman #{peminjaman.id}</h3>
                        <span className={`px-3 py-1 rounded-full text-sm font-medium ${statusBadge(peminjaman.status)}`}>
                            {peminjaman.status}
                        </span>
                    </div>

                    <div className="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p className="text-gray-500">Nama Anggota</p>
                            <p className="font-medium text-gray-800">{peminjaman.anggota?.user?.name}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">NIM/NIP</p>
                            <p className="font-medium text-gray-800">{peminjaman.anggota?.nim_nip || '-'}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Tanggal Pinjam</p>
                            <p className="font-medium text-gray-800">{peminjaman.tanggal_pinjam}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Jatuh Tempo</p>
                            <p className="font-medium text-gray-800">{peminjaman.tanggal_jatuh_tempo}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Tanggal Kembali</p>
                            <p className="font-medium text-gray-800">{peminjaman.tanggal_kembali || 'Belum dikembalikan'}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Diproses oleh</p>
                            <p className="font-medium text-gray-800">{peminjaman.pustakawan?.name}</p>
                        </div>
                    </div>

                    <div>
                        <p className="text-gray-500 text-sm mb-2">Buku yang Dipinjam</p>
                        <div className="border border-gray-200 rounded-lg divide-y">
                            {peminjaman.detail?.map((d) => (
                                <div key={d.id} className="p-3 flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-medium text-gray-800">{d.buku?.judul}</p>
                                        <p className="text-xs text-gray-400">{d.buku?.kategori?.nama}</p>
                                    </div>
                                    <span className="text-xs text-gray-500">x{d.jumlah}</span>
                                </div>
                            ))}
                        </div>
                    </div>

                    {peminjaman.catatan && (
                        <div>
                            <p className="text-gray-500 text-sm mb-1">Catatan</p>
                            <p className="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{peminjaman.catatan}</p>
                        </div>
                    )}

                    <div className="pt-4 border-t">
                        <Link href={route('peminjaman.index')}
                            className="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                            Kembali ke Daftar
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
JSX;

// ========== RIWAYAT ==========
$riwayat = <<<'JSX'
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function PeminjamanRiwayat({ peminjaman, anggota, filters }) {
    const [anggotaId, setAnggotaId] = useState(filters.anggota_id || '');

    const handleFilter = (e) => {
        e.preventDefault();
        router.get(route('peminjaman.riwayat'), { anggota_id: anggotaId }, { preserveState: true });
    };

    const statusBadge = (status) => {
        const map = {
            dipinjam:     'bg-yellow-100 text-yellow-700',
            dikembalikan: 'bg-green-100 text-green-700',
            terlambat:    'bg-red-100 text-red-700',
        };
        return map[status] || 'bg-gray-100 text-gray-700';
    };

    return (
        <AuthenticatedLayout title="Riwayat Peminjaman">
            <Head title="Riwayat Peminjaman" />

            <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-bold text-gray-800">Riwayat Peminjaman</h2>
                <Link href={route('peminjaman.index')} className="text-gray-500 hover:text-gray-700 text-sm">
                    ← Kembali
                </Link>
            </div>

            <div className="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form onSubmit={handleFilter} className="flex gap-3">
                    <select value={anggotaId} onChange={(e) => setAnggotaId(e.target.value)}
                        className="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Anggota</option>
                        {anggota.map((a) => (
                            <option key={a.id} value={a.id}>{a.nama}</option>
                        ))}
                    </select>
                    <button type="submit"
                        className="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                        Filter
                    </button>
                    <Link href={route('peminjaman.riwayat')}
                        className="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                        Reset
                    </Link>
                </form>
            </div>

            <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">No</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Anggota</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Buku</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Tgl Pinjam</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Jatuh Tempo</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Tgl Kembali</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {peminjaman.data.length === 0 ? (
                            <tr>
                                <td colSpan={7} className="text-center py-8 text-gray-400">
                                    Tidak ada riwayat peminjaman
                                </td>
                            </tr>
                        ) : peminjaman.data.map((p, i) => (
                            <tr key={p.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3 text-gray-500">
                                    {(peminjaman.current_page - 1) * peminjaman.per_page + i + 1}
                                </td>
                                <td className="px-4 py-3">
                                    <p className="font-medium text-gray-800">{p.anggota?.user?.name}</p>
                                    <p className="text-xs text-gray-400">{p.anggota?.nim_nip}</p>
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {p.detail?.map((d) => (
                                        <p key={d.id} className="text-xs">{d.buku?.judul}</p>
                                    ))}
                                </td>
                                <td className="px-4 py-3 text-gray-600">{p.tanggal_pinjam}</td>
                                <td className="px-4 py-3 text-gray-600">{p.tanggal_jatuh_tempo}</td>
                                <td className="px-4 py-3 text-gray-600">{p.tanggal_kembali || '-'}</td>
                                <td className="px-4 py-3">
                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${statusBadge(p.status)}`}>
                                        {p.status}
                                    </span>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AuthenticatedLayout>
    );
}
JSX;

// Tulis semua file
file_put_contents('resources/js/Pages/Peminjaman/Index.jsx', $index);
file_put_contents('resources/js/Pages/Peminjaman/Create.jsx', $create);
file_put_contents('resources/js/Pages/Peminjaman/Show.jsx', $show);
file_put_contents('resources/js/Pages/Peminjaman/Riwayat.jsx', $riwayat);

echo 'Berhasil membuat ' . PHP_EOL;
echo 'Index.jsx: ' . strlen($index) . ' bytes' . PHP_EOL;
echo 'Create.jsx: ' . strlen($create) . ' bytes' . PHP_EOL;
echo 'Show.jsx: ' . strlen($show) . ' bytes' . PHP_EOL;
echo 'Riwayat.jsx: ' . strlen($riwayat) . ' bytes' . PHP_EOL;
import { Head, Link, router, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function BukuIndex({ buku, kategori, filters }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');
    const [kategoriId, setKategoriId] = useState(filters.kategori_id || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('buku.index'), {
            search, status, kategori_id: kategoriId,
        }, { preserveState: true });
    };

    const handleSort = (field) => {
        const newDir = filters.sort === field && filters.dir === 'asc' ? 'desc' : 'asc';
        router.get(route('buku.index'), {
            ...filters, sort: field, dir: newDir,
        }, { preserveState: true });
    };

    const handleDelete = (id) => {
        if (confirm('Yakin ingin menghapus buku ini?')) {
            router.delete(route('buku.destroy', id));
        }
    };

    const statusBadge = (status) => {
        const map = {
            tersedia: 'bg-green-100 text-green-700',
            dipinjam: 'bg-yellow-100 text-yellow-700',
            rusak:    'bg-red-100 text-red-700',
            hilang:   'bg-gray-100 text-gray-700',
        };
        return map[status] || 'bg-gray-100 text-gray-700';
    };

    const { auth } = usePage().props;
    const isAnggota = auth.user?.role === 'anggota';

    return (
        <AuthenticatedLayout title="Manajemen Buku">
            <Head title="Manajemen Buku" />

            {/* Header */}
            <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-bold text-gray-800">Daftar Buku</h2>
                    {!isAnggota && (
                        <Link href={route('buku.create')}
                            className="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                            + Tambah Buku
                        </Link>
                    )};
            </div>

            {/* Filter & Search */}
            <div className="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form onSubmit={handleSearch} className="flex flex-wrap gap-3">
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Cari judul, ISBN, penulis..."
                        className="flex-1 min-w-48 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <select value={kategoriId} onChange={(e) => setKategoriId(e.target.value)}
                        className="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Kategori</option>
                        {kategori.map((k) => (
                            <option key={k.id} value={k.id}>{k.nama}</option>
                        ))}
                    </select>
                    <select value={status} onChange={(e) => setStatus(e.target.value)}
                        className="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Status</option>
                        <option value="tersedia">Tersedia</option>
                        <option value="dipinjam">Dipinjam</option>
                        <option value="rusak">Rusak</option>
                        <option value="hilang">Hilang</option>
                    </select>
                    <button type="submit"
                        className="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                        Cari
                    </button>
                    <Link href={route('buku.index')}
                        className="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                        Reset
                    </Link>
                </form>
            </div>

            {/* Tabel */}
            <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">No</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600 cursor-pointer hover:text-blue-700"
                                onClick={() => handleSort('judul')}>
                                Judul {filters.sort === 'judul' ? (filters.dir === 'asc' ? '↑' : '↓') : ''}
                            </th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Penulis</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Kategori</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600 cursor-pointer hover:text-blue-700"
                                onClick={() => handleSort('tahun_terbit')}>
                                Tahun {filters.sort === 'tahun_terbit' ? (filters.dir === 'asc' ? '↑' : '↓') : ''}
                            </th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600 cursor-pointer hover:text-blue-700"
                                onClick={() => handleSort('jumlah_tersedia')}>
                                Tersedia {filters.sort === 'jumlah_tersedia' ? (filters.dir === 'asc' ? '↑' : '↓') : ''}
                            </th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {buku.data.length === 0 ? (
                            <tr>
                                <td colSpan={8} className="text-center py-8 text-gray-400">
                                    Tidak ada data buku
                                </td>
                            </tr>
                        ) : buku.data.map((b, i) => (
                            <tr key={b.id} className="hover:bg-gray-50 transition">
                                <td className="px-4 py-3 text-gray-500">
                                    {(buku.current_page - 1) * buku.per_page + i + 1}
                                </td>
                                <td className="px-4 py-3">
                                    <p className="font-medium text-gray-800">{b.judul}</p>
                                    <p className="text-xs text-gray-400">{b.isbn}</p>
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {b.penulis.map((p) => p.nama).join(', ')}
                                </td>
                                <td className="px-4 py-3 text-gray-600">{b.kategori?.nama}</td>
                                <td className="px-4 py-3 text-gray-600">{b.tahun_terbit}</td>
                                <td className="px-4 py-3 text-gray-600">
                                    {b.jumlah_tersedia}/{b.jumlah_eksemplar}
                                </td>
                                <td className="px-4 py-3">
                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${statusBadge(b.status)}`}>
                                        {b.status}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex gap-2">
                                        <Link href={route('buku.show', b.id)}
                                            className="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                            Detail
                                        </Link>
                                            {!isAnggota && (
                                                <>
                                                    <Link href={route('buku.edit', b.id)}
                                                        className="text-yellow-600 hover:text-yellow-800 text-xs font-medium">
                                                        Edit
                                                    </Link>
                                                    <button onClick={() => handleDelete(b.id)}
                                                        className="text-red-600 hover:text-red-800 text-xs font-medium">
                                                        Hapus
                                                    </button>
                                                </>    
                                            )}    
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>

                {/* Pagination */}
                {buku.last_page > 1 && (
                    <div className="px-4 py-3 border-t flex items-center justify-between">
                        <p className="text-sm text-gray-500">
                            Menampilkan {buku.from}–{buku.to} dari {buku.total} buku
                        </p>
                        <div className="flex gap-2">
                            {buku.links.map((link, i) => (
                                <button key={i}
                                    onClick={() => link.url && router.get(link.url)}
                                    disabled={!link.url}
                                    className={`px-3 py-1 rounded text-sm ${link.active
                                        ? 'bg-blue-700 text-white'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                    } disabled:opacity-40`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
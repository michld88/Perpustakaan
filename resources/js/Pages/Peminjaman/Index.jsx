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

    const formatTanggal = (tanggal) => {
        if (!tanggal) return '-';
        return new Date(tanggal).toLocaleDateString('id-ID', {
            day: '2-digit', month: '2-digit', year: 'numeric'
        });
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
                                <td className="px-4 py-3 text-gray-600">{formatTanggal(p.tanggal_pinjam)}</td>
                                <td className="px-4 py-3 text-gray-600">{formatTanggal(p.tanggal_jatuh_tempo)}</td>
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
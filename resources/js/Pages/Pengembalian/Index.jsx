import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function PengembalianIndex({ peminjaman, filters }) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('pengembalian.index'), { search }, { preserveState: true });
    };

    const statusBadge = (status) => {
        const map = {
            dipinjam:  'bg-yellow-100 text-yellow-700',
            terlambat: 'bg-red-100 text-red-700',
        };
        return map[status] || 'bg-gray-100 text-gray-700';
    };

    const formatTanggal = (tanggal) => {
        if (!tanggal) return "-";

        const date = new Date(tanggal);

        const hari = String(date.getDate()).padStart(2, "0");
        const bulan = String(date.getMonth() + 1).padStart(2, "0");
        const tahun = date.getFullYear();

        return `${hari}/${bulan}/${tahun}`;
    };

    return (
        <AuthenticatedLayout title="Pengembalian Buku">
            <Head title="Pengembalian Buku" />

            <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-bold text-gray-800">Daftar Peminjaman Aktif</h2>
                <Link href={route('pengembalian.denda')}
                    className="bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                    🔴 Kelola Denda
                </Link>
            </div>

            <div className="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form onSubmit={handleSearch} className="flex gap-3">
                    <input type="text" value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Cari nama anggota..."
                        className="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                    <button type="submit"
                        className="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                        Cari
                    </button>
                    <Link href={route('pengembalian.index')}
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
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Jatuh Tempo</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {peminjaman.data.length === 0 ? (
                            <tr>
                                <td colSpan={6} className="text-center py-8 text-gray-400">
                                    Tidak ada peminjaman aktif
                                </td>
                            </tr>
                        ) : peminjaman.data.map((p, i) => (
                            <tr key={p.id} className={`hover:bg-gray-50 transition ${p.status === 'terlambat' ? 'bg-red-50' : ''}`}>
                                <td className="px-4 py-3 text-gray-500">
                                    {(peminjaman.current_page - 1) * peminjaman.per_page + i + 1}
                                </td>
                                <td className="px-4 py-3">
                                    <p className="font-medium text-gray-800">{p.anggota?.user?.name}</p>
                                    <p className="text-xs text-gray-400">{p.anggota?.nim_nip || '-'}</p>
                                </td>
                                <td className="px-4 py-3 text-gray-600">
                                    {p.detail?.map((d) => (
                                        <p key={d.id} className="text-xs">{d.buku?.judul}</p>
                                    ))}
                                </td>
                                <td className="px-4 py-3 text-gray-600">{formatTanggal(p.tanggal_jatuh_tempo)}</td>
                                <td className="px-4 py-3">
                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${statusBadge(p.status)}`}>
                                        {p.status}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex gap-2">
                                        <Link href={route('pengembalian.show', p.id)}
                                            className="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-medium transition">
                                            Proses
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AuthenticatedLayout>
    );
}
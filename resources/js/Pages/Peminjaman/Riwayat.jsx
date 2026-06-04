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
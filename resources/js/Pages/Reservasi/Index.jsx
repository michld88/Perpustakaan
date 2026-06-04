import { Head, Link, router, usePage, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function ReservasiIndex({ reservasi, filters }) {
    const { auth } = usePage().props;
    const [status, setStatus] = useState(filters.status || '');

    const handleFilter = (e) => {
        e.preventDefault();
        router.get(route('reservasi.index'), { status }, { preserveState: true });
    };

    const handleBatalkan = (id) => {
        if (confirm('Yakin ingin membatalkan reservasi ini?')) {
            router.delete(route('reservasi.destroy', id));
        }
    };

    const handleAmbil = (id) => {
        if (confirm('Konfirmasi pengambilan reservasi ini?')) {
            router.post(route('reservasi.ambil', id));
        }
    };

    const statusBadge = (status) => {
        const map = {
            menunggu:   'bg-yellow-100 text-yellow-700',
            tersedia:   'bg-green-100 text-green-700',
            diambil:    'bg-blue-100 text-blue-700',
            dibatalkan: 'bg-gray-100 text-gray-500',
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
        <AuthenticatedLayout title="Reservasi Buku">
            <Head title="Reservasi Buku" />

            <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-bold text-gray-800">Daftar Reservasi</h2>
                <Link href={route('reservasi.create')}
                    className="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    + Reservasi Buku
                </Link>
            </div>

            <div className="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form onSubmit={handleFilter} className="flex gap-3">
                    <select value={status} onChange={(e) => setStatus(e.target.value)}
                        className="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="menunggu">Menunggu</option>
                        <option value="tersedia">Tersedia</option>
                        <option value="diambil">Diambil</option>
                        <option value="dibatalkan">Dibatalkan</option>
                    </select>
                    <button type="submit"
                        className="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                        Filter
                    </button>
                    <Link href={route('reservasi.index')}
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
                            {auth.user?.role !== 'anggota' && (
                                <th className="text-left px-4 py-3 font-semibold text-gray-600">Anggota</th>
                            )}
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Buku</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Tgl Reservasi</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Kadaluarsa</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {reservasi.data.length === 0 ? (
                            <tr>
                                <td colSpan={7} className="text-center py-8 text-gray-400">
                                    Tidak ada data reservasi
                                </td>
                            </tr>
                        ) : reservasi.data.map((r, i) => (
                            <tr key={r.id} className="hover:bg-gray-50 transition">
                                <td className="px-4 py-3 text-gray-500">
                                    {(reservasi.current_page - 1) * reservasi.per_page + i + 1}
                                </td>
                                {auth.user?.role !== 'anggota' && (
                                    <td className="px-4 py-3 font-medium text-gray-800">
                                        {r.anggota?.user?.name}
                                    </td>
                                )}
                                <td className="px-4 py-3">
                                    <p className="font-medium text-gray-800">{r.buku?.judul}</p>
                                    <p className="text-xs text-gray-400">{r.buku?.kategori?.nama}</p>
                                </td>
                                <td className="px-4 py-3 text-gray-600">{formatTanggal(r.tanggal_reservasi)}</td>
                                <td className="px-4 py-3 text-gray-600">{formatTanggal(r.tanggal_kadaluarsa)}</td>
                                <td className="px-4 py-3">
                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${statusBadge(r.status)}`}>
                                        {r.status}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex gap-2">
                                        {r.status === 'tersedia' && (
                                            <button onClick={() => handleAmbil(r.id)}
                                                className="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-medium transition">
                                                Ambil
                                            </button>
                                        )}
                                        {(r.status === 'menunggu' || r.status === 'tersedia') && (
                                            <button onClick={() => handleBatalkan(r.id)}
                                                className="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1 rounded text-xs font-medium transition">
                                                Batalkan
                                            </button>
                                        )}
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
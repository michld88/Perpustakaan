import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function ReservasiKelola({ reservasi }) {
    const formatTanggal = (tanggal) => {
        if (!tanggal) return '-';
        return new Date(tanggal).toLocaleDateString('id-ID', {
            day: '2-digit', month: '2-digit', year: 'numeric'
        });
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

    const handleBatalkan = (id) => {
        if (confirm('Batalkan reservasi ini?')) {
            router.delete(route('reservasi.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout title="Kelola Reservasi">
            <Head title="Kelola Reservasi" />

            <div className="mb-6">
                <h2 className="text-xl font-bold text-gray-800">Kelola Reservasi</h2>
                <p className="text-sm text-gray-500 mt-1">Daftar reservasi dari semua anggota</p>
            </div>

            <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">No</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Anggota</th>
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
                            <tr key={r.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3 text-gray-500">
                                    {(reservasi.current_page - 1) * reservasi.per_page + i + 1}
                                </td>
                                <td className="px-4 py-3">
                                    <p className="font-medium text-gray-800">{r.anggota?.user?.name}</p>
                                    <p className="text-xs text-gray-400">{r.anggota?.nim_nip}</p>
                                </td>
                                <td className="px-4 py-3 font-medium text-gray-800">{r.buku?.judul}</td>
                                <td className="px-4 py-3 text-gray-600">{formatTanggal(r.tanggal_reservasi)}</td>
                                <td className="px-4 py-3 text-gray-600">{formatTanggal(r.tanggal_kadaluarsa)}</td>
                                <td className="px-4 py-3">
                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${statusBadge(r.status)}`}>
                                        {r.status}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    {(r.status === 'menunggu' || r.status === 'tersedia') && (
                                        <button onClick={() => handleBatalkan(r.id)}
                                            className="bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1 rounded text-xs font-medium transition">
                                            Batalkan
                                        </button>
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AuthenticatedLayout>
    );
}
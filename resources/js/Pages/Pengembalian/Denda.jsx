import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function DendaIndex({ denda, filters }) {
    const [status, setStatus] = useState(filters.status || '');

    const handleFilter = (e) => {
        e.preventDefault();
        router.get(route('pengembalian.denda'), { status }, { preserveState: true });
    };

    const handleBayar = (id) => {
        if (confirm('Konfirmasi pembayaran denda ini?')) {
            router.post(route('pengembalian.bayar-denda', id));
        }
    };

    const formatRupiah = (angka) =>
        'Rp ' + new Intl.NumberFormat('id-ID').format(angka);

    return (
        <AuthenticatedLayout title="Kelola Denda">
            <Head title="Kelola Denda" />

            <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-bold text-gray-800">Daftar Denda</h2>
                <Link href={route('pengembalian.index')} className="text-gray-500 hover:text-gray-700 text-sm">
                    ← Kembali
                </Link>
            </div>

            <div className="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form onSubmit={handleFilter} className="flex gap-3">
                    <select value={status} onChange={(e) => setStatus(e.target.value)}
                        className="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="belum_bayar">Belum Bayar</option>
                        <option value="sudah_bayar">Sudah Bayar</option>
                    </select>
                    <button type="submit"
                        className="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                        Filter
                    </button>
                </form>
            </div>

            <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">No</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Anggota</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Hari Terlambat</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Total Denda</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {denda.data.length === 0 ? (
                            <tr>
                                <td colSpan={6} className="text-center py-8 text-gray-400">
                                    Tidak ada data denda
                                </td>
                            </tr>
                        ) : denda.data.map((d, i) => (
                            <tr key={d.id} className="hover:bg-gray-50 transition">
                                <td className="px-4 py-3 text-gray-500">{i + 1}</td>
                                <td className="px-4 py-3 font-medium text-gray-800">
                                    {d.peminjaman?.anggota?.user?.name}
                                </td>
                                <td className="px-4 py-3 text-gray-600">{d.hari_terlambat} hari</td>
                                <td className="px-4 py-3 font-semibold text-red-600">
                                    {formatRupiah(d.total_denda)}
                                </td>
                                <td className="px-4 py-3">
                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                        d.status === 'sudah_bayar'
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-red-100 text-red-700'
                                    }`}>
                                        {d.status === 'sudah_bayar' ? 'Sudah Bayar' : 'Belum Bayar'}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    {d.status === 'belum_bayar' && (
                                        <button onClick={() => handleBayar(d.id)}
                                            className="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs font-medium transition">
                                            Bayar
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
import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function BukuTerpopuler({ buku, filters, tahunList }) {
    const [tahun, setTahun] = useState(filters.tahun || new Date().getFullYear());

    const handleFilter = (e) => {
        e.preventDefault();
        router.get(route('laporan.buku-terpopuler'), { tahun }, { preserveState: true });
    };

    return (
        <AuthenticatedLayout title="Buku Terpopuler">
            <Head title="Buku Terpopuler" />

            <div className="flex items-center gap-4 mb-6">
                <Link href={route('laporan.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                <h2 className="text-xl font-bold text-gray-800">Buku Terpopuler</h2>
            </div>

            <div className="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form onSubmit={handleFilter} className="flex gap-3">
                    <select value={tahun} onChange={(e) => setTahun(e.target.value)}
                        className="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {tahunList.map((t) => <option key={t} value={t}>{t}</option>)}
                    </select>
                    <button type="submit"
                        className="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                        Tampilkan
                    </button>
                </form>
            </div>

            <div className="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Rank</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Judul Buku</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Kategori</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Penulis</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Total Dipinjam</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {buku.length === 0 ? (
                            <tr><td colSpan={5} className="text-center py-8 text-gray-400">Tidak ada data</td></tr>
                        ) : buku.map((b, i) => (
                            <tr key={b.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3">
                                    <span className={`w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white
                                        ${i === 0 ? 'bg-yellow-500' : i === 1 ? 'bg-gray-400' : i === 2 ? 'bg-orange-400' : 'bg-blue-200 text-blue-800'}`}>
                                        {i + 1}
                                    </span>
                                </td>
                                <td className="px-4 py-3 font-medium text-gray-800">{b.judul}</td>
                                <td className="px-4 py-3 text-gray-600">{b.kategori?.nama}</td>
                                <td className="px-4 py-3 text-gray-600">{b.penulis?.map((p) => p.nama).join(', ')}</td>
                                <td className="px-4 py-3">
                                    <span className="bg-blue-100 text-blue-700 px-2 py-1 rounded-full text-xs font-medium">
                                        {b.total_dipinjam}x
                                    </span>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <div className="flex gap-3">
                <a href={route('laporan.ekspor-pdf', { jenis: 'buku_terpopuler', tahun })}
                    className="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    📄 Ekspor PDF
                </a>
                <a href={route('laporan.ekspor-excel', { jenis: 'buku_terpopuler', tahun })}
                    className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    📊 Ekspor Excel
                </a>
            </div>
        </AuthenticatedLayout>
    );
}
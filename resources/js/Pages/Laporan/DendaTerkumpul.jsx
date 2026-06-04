import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function DendaTerkumpul({ denda, totalBelumBayar, totalSudahBayar, filters, tahunList }) {
    const [tahun, setTahun] = useState(filters.tahun || new Date().getFullYear());
    const [bulan, setBulan] = useState(filters.bulan || '');

    const handleFilter = (e) => {
        e.preventDefault();
        router.get(route('laporan.denda-terkumpul'), { tahun, bulan }, { preserveState: true });
    };

    const formatRupiah = (angka) => 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);

    const formatTanggal = (tanggal) => {
        if (!tanggal) return '-';
        return new Date(tanggal).toLocaleDateString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric' });
    };

    return (
        <AuthenticatedLayout title="Denda Terkumpul">
            <Head title="Denda Terkumpul" />

            <div className="flex items-center gap-4 mb-6">
                <Link href={route('laporan.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                <h2 className="text-xl font-bold text-gray-800">Laporan Denda Terkumpul</h2>
            </div>

            {/* Summary */}
            <div className="grid grid-cols-2 gap-4 mb-6">
                <div className="bg-red-50 border border-red-200 rounded-xl p-4">
                    <p className="text-sm text-red-600">Belum Dibayar</p>
                    <p className="text-2xl font-bold text-red-700">{formatRupiah(totalBelumBayar)}</p>
                </div>
                <div className="bg-green-50 border border-green-200 rounded-xl p-4">
                    <p className="text-sm text-green-600">Sudah Dibayar</p>
                    <p className="text-2xl font-bold text-green-700">{formatRupiah(totalSudahBayar)}</p>
                </div>
            </div>

            {/* Filter */}
            <div className="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form onSubmit={handleFilter} className="flex gap-3">
                    <select value={tahun} onChange={(e) => setTahun(e.target.value)}
                        className="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {tahunList.map((t) => <option key={t} value={t}>{t}</option>)}
                    </select>
                    <select value={bulan} onChange={(e) => setBulan(e.target.value)}
                        className="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Bulan</option>
                        {['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'].map((b, i) => (
                            <option key={i+1} value={i+1}>{b}</option>
                        ))}
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
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">No</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Anggota</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Hari Terlambat</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Total Denda</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Tgl Bayar</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {denda.data.length === 0 ? (
                            <tr><td colSpan={6} className="text-center py-8 text-gray-400">Tidak ada data denda</td></tr>
                        ) : denda.data.map((d, i) => (
                            <tr key={d.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3 text-gray-500">{(denda.current_page - 1) * denda.per_page + i + 1}</td>
                                <td className="px-4 py-3 font-medium text-gray-800">{d.peminjaman?.anggota?.user?.name}</td>
                                <td className="px-4 py-3 text-gray-600">{d.hari_terlambat} hari</td>
                                <td className="px-4 py-3 font-semibold text-red-600">{formatRupiah(d.total_denda)}</td>
                                <td className="px-4 py-3">
                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                        d.status === 'sudah_bayar' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'
                                    }`}>
                                        {d.status === 'sudah_bayar' ? 'Sudah Bayar' : 'Belum Bayar'}
                                    </span>
                                </td>
                                <td className="px-4 py-3 text-gray-600">{formatTanggal(d.tanggal_bayar)}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <div className="flex gap-3">
                <a href={route('laporan.ekspor-pdf', { jenis: 'denda', tahun })}
                    className="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    📄 Ekspor PDF
                </a>
                <a href={route('laporan.ekspor-excel', { jenis: 'denda', tahun })}
                    className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    📊 Ekspor Excel
                </a>
            </div>
        </AuthenticatedLayout>
    );
}
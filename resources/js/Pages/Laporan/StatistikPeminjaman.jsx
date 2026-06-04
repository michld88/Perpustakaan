import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function StatistikPeminjaman({ data, summary, filters, tahunList }) {
    const [periode, setPeriode] = useState(filters.periode || 'bulanan');
    const [tahun, setTahun] = useState(filters.tahun || new Date().getFullYear());
    const [bulan, setBulan] = useState(filters.bulan || new Date().getMonth() + 1);

    const handleFilter = (e) => {
        e.preventDefault();
        router.get(route('laporan.statistik-peminjaman'), { periode, tahun, bulan }, { preserveState: true });
    };

    const maxJumlah = Math.max(...data.map((d) => d.jumlah), 1);

    return (
        <AuthenticatedLayout title="Statistik Peminjaman">
            <Head title="Statistik Peminjaman" />

            <div className="flex items-center gap-4 mb-6">
                <Link href={route('laporan.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                <h2 className="text-xl font-bold text-gray-800">Statistik Peminjaman</h2>
            </div>

            {/* Summary Cards */}
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                {[
                    { label: 'Total Peminjaman', value: summary.total_peminjaman, color: 'bg-blue-500' },
                    { label: 'Dikembalikan', value: summary.total_dikembalikan, color: 'bg-green-500' },
                    { label: 'Terlambat', value: summary.total_terlambat, color: 'bg-red-500' },
                    { label: 'Masih Aktif', value: summary.total_aktif, color: 'bg-yellow-500' },
                ].map((s) => (
                    <div key={s.label} className="bg-white rounded-xl shadow-sm p-4">
                        <div className={`${s.color} w-10 h-10 rounded-lg flex items-center justify-center text-white text-lg mb-2`}>
                            📊
                        </div>
                        <p className="text-2xl font-bold text-gray-800">{s.value}</p>
                        <p className="text-sm text-gray-500">{s.label}</p>
                    </div>
                ))}
            </div>

            {/* Filter */}
            <div className="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form onSubmit={handleFilter} className="flex flex-wrap gap-3">
                    <select value={periode} onChange={(e) => setPeriode(e.target.value)}
                        className="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="harian">Harian</option>
                        <option value="mingguan">Mingguan</option>
                        <option value="bulanan">Bulanan</option>
                        <option value="tahunan">Tahunan</option>
                    </select>
                    <select value={tahun} onChange={(e) => setTahun(e.target.value)}
                        className="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        {tahunList.map((t) => <option key={t} value={t}>{t}</option>)}
                    </select>
                    {(periode === 'harian' || periode === 'mingguan') && (
                        <select value={bulan} onChange={(e) => setBulan(e.target.value)}
                            className="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            {Array.from({length: 12}, (_, i) => (
                                <option key={i+1} value={i+1}>
                                    {['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][i]}
                                </option>
                            ))}
                        </select>
                    )}
                    <button type="submit"
                        className="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                        Tampilkan
                    </button>
                </form>
            </div>

            {/* Bar Chart */}
            <div className="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 className="font-semibold text-gray-700 mb-4">
                    Grafik Peminjaman {periode.charAt(0).toUpperCase() + periode.slice(1)} {tahun}
                </h3>
                <div className="flex items-end gap-2 h-48 overflow-x-auto">
                    {data.map((d, i) => (
                        <div key={i} className="flex flex-col items-center gap-1 min-w-8">
                            <span className="text-xs text-gray-600">{d.jumlah}</span>
                            <div
                                className="bg-blue-500 rounded-t w-8 transition-all"
                                style={{ height: `${(d.jumlah / maxJumlah) * 160}px`, minHeight: d.jumlah > 0 ? '4px' : '0' }}
                            />
                            <span className="text-xs text-gray-500 text-center" style={{ fontSize: '10px' }}>{d.label}</span>
                        </div>
                    ))}
                </div>
            </div>

            {/* Ekspor */}
            <div className="flex gap-3">
                <a href={route('laporan.ekspor-pdf', { jenis: 'peminjaman', tahun })}
                    className="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    📄 Ekspor PDF
                </a>
                <a href={route('laporan.ekspor-excel', { jenis: 'peminjaman', tahun })}
                    className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    📊 Ekspor Excel
                </a>
            </div>
        </AuthenticatedLayout>
    );
}
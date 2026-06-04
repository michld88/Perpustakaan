import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function AdminDashboard({ stats, peminjamanTerbaru, bukuPopuler, grafikBulanan, terlambat }) {
    const formatRupiah = (angka) =>
        'Rp ' + new Intl.NumberFormat('id-ID').format(angka || 0);

    const cards = [
        { label: 'Total Buku', value: stats?.total_buku || 0, icon: '📚', color: 'bg-blue-500' },
        { label: 'Total Anggota', value: stats?.total_anggota || 0, icon: '👥', color: 'bg-green-500' },
        { label: 'Peminjaman Aktif', value: stats?.peminjaman_aktif || 0, icon: '📋', color: 'bg-yellow-500' },
        { label: 'Terlambat', value: stats?.buku_terlambat || 0, icon: '⚠️', color: 'bg-red-500' },
        { label: 'Denda Belum Bayar', value: formatRupiah(stats?.denda_belum_bayar), icon: '💰', color: 'bg-orange-500' },
        { label: 'Reservasi Menunggu', value: stats?.reservasi_menunggu || 0, icon: '🔖', color: 'bg-purple-500' },
        { label: 'Pinjam Hari Ini', value: stats?.peminjaman_hari_ini || 0, icon: '📅', color: 'bg-teal-500' },
        { label: 'Pinjam Bulan Ini', value: stats?.total_peminjaman_bulan || 0, icon: '📆', color: 'bg-indigo-500' },
    ];

    const maxGrafik = Math.max(...(grafikBulanan?.map((g) => g.jumlah) || [1]), 1);

    return (
        <AuthenticatedLayout title="Dashboard Admin">
            <Head title="Dashboard Admin" />

            <div className="mb-6">
                <h2 className="text-xl font-bold text-gray-800">Dashboard Administrator</h2>
                <p className="text-sm text-gray-500 mt-1">Ringkasan sistem perpustakaan hari ini</p>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                {cards.map((card) => (
                    <div key={card.label} className="bg-white rounded-xl shadow-sm p-5">
                        <div className={`${card.color} w-10 h-10 rounded-lg flex items-center justify-center text-xl mb-3`}>
                            {card.icon}
                        </div>
                        <p className="text-2xl font-bold text-gray-800">{card.value}</p>
                        <p className="text-sm text-gray-500 mt-1">{card.label}</p>
                    </div>
                ))}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {/* Grafik Bulanan */}
                <div className="bg-white rounded-xl shadow-sm p-6">
                    <h3 className="font-semibold text-gray-700 mb-4">📊 Grafik Peminjaman 6 Bulan Terakhir</h3>
                    <div className="flex items-end gap-2 h-40">
                        {grafikBulanan?.map((g, i) => (
                            <div key={i} className="flex flex-col items-center gap-1 flex-1">
                                <span className="text-xs text-gray-600">{g.jumlah}</span>
                                <div
                                    className="bg-blue-500 rounded-t w-full transition-all"
                                    style={{ height: `${(g.jumlah / maxGrafik) * 120}px`, minHeight: g.jumlah > 0 ? '4px' : '0' }}
                                />
                                <span className="text-xs text-gray-500 text-center">{g.label}</span>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Buku Terpopuler */}
                <div className="bg-white rounded-xl shadow-sm p-6">
                    <h3 className="font-semibold text-gray-700 mb-4">📚 Buku Terpopuler Bulan Ini</h3>
                    <div className="space-y-3">
                        {bukuPopuler?.length === 0 ? (
                            <p className="text-sm text-gray-400 text-center py-4">Belum ada data</p>
                        ) : bukuPopuler?.map((b, i) => (
                            <div key={b.id} className="flex items-center gap-3">
                                <span className={`w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0
                                    ${i === 0 ? 'bg-yellow-500' : i === 1 ? 'bg-gray-400' : i === 2 ? 'bg-orange-400' : 'bg-blue-300'}`}>
                                    {i + 1}
                                </span>
                                <div className="flex-1 min-w-0">
                                    <p className="text-sm font-medium text-gray-800 truncate">{b.judul}</p>
                                    <p className="text-xs text-gray-400">{b.kategori?.nama}</p>
                                </div>
                                <span className="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">
                                    {b.total_dipinjam}x
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {/* Peminjaman Terbaru */}
                <div className="bg-white rounded-xl shadow-sm p-6">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="font-semibold text-gray-700">📋 Peminjaman Terbaru</h3>
                        <Link href={route('peminjaman.index')} className="text-xs text-blue-600 hover:text-blue-800">
                            Lihat semua →
                        </Link>
                    </div>
                    <div className="space-y-3">
                        {peminjamanTerbaru?.length === 0 ? (
                            <p className="text-sm text-gray-400 text-center py-4">Belum ada data</p>
                        ) : peminjamanTerbaru?.map((p) => (
                            <div key={p.id} className="flex items-center justify-between text-sm">
                                <div>
                                    <p className="font-medium text-gray-800">{p.anggota}</p>
                                    <p className="text-xs text-gray-400 truncate max-w-40">{p.buku}</p>
                                </div>
                                <div className="text-right">
                                    <p className="text-xs text-gray-500">{p.tanggal_pinjam}</p>
                                    <span className={`text-xs px-2 py-0.5 rounded-full font-medium
                                        ${p.status === 'terlambat' ? 'bg-red-100 text-red-700' :
                                          p.status === 'dikembalikan' ? 'bg-green-100 text-green-700' :
                                          'bg-yellow-100 text-yellow-700'}`}>
                                        {p.status}
                                    </span>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Peminjaman Terlambat */}
                <div className="bg-white rounded-xl shadow-sm p-6">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="font-semibold text-gray-700">⚠️ Peminjaman Terlambat</h3>
                        <Link href={route('pengembalian.index')} className="text-xs text-blue-600 hover:text-blue-800">
                            Proses →
                        </Link>
                    </div>
                    <div className="space-y-3">
                        {terlambat?.length === 0 ? (
                            <p className="text-sm text-gray-400 text-center py-4">Tidak ada peminjaman terlambat</p>
                        ) : terlambat?.map((p) => (
                            <div key={p.id} className="flex items-center justify-between text-sm bg-red-50 p-2 rounded-lg">
                                <div>
                                    <p className="font-medium text-gray-800">{p.anggota?.user?.name}</p>
                                    <p className="text-xs text-gray-400">
                                        {p.detail?.map((d) => d.buku?.judul).join(', ')}
                                    </p>
                                </div>
                                <div className="text-right">
                                    <p className="text-xs text-red-600 font-medium">
                                        JT: {p.tanggal_jatuh_tempo}
                                    </p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
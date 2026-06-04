import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function PustakawanDashboard({ stats, peminjamanTerbaru, terlambat }) {
    const statusBadge = (status) => {
        const map = {
            dipinjam:     'bg-yellow-100 text-yellow-700',
            terlambat:    'bg-red-100 text-red-700',
            dikembalikan: 'bg-green-100 text-green-700',
        };
        return map[status] || 'bg-gray-100 text-gray-700';
    };

    return (
        <AuthenticatedLayout title="Dashboard Pustakawan">
            <Head title="Dashboard Pustakawan" />

            <div className="mb-6">
                <h2 className="text-xl font-bold text-gray-800">Dashboard Pustakawan</h2>
                <p className="text-sm text-gray-500 mt-1">Ringkasan aktivitas perpustakaan hari ini</p>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                {[
                    { label: 'Total Buku', value: stats?.total_buku || 0, icon: '📚', color: 'bg-blue-500', href: route('buku.index') },
                    { label: 'Anggota Aktif', value: stats?.total_anggota || 0, icon: '👥', color: 'bg-green-500', href: route('anggota.index') },
                    { label: 'Peminjaman Hari Ini', value: stats?.peminjaman_hari_ini || 0, icon: '📋', color: 'bg-purple-500', href: route('peminjaman.index') },
                    { label: 'Peminjaman Aktif', value: stats?.peminjaman_aktif || 0, icon: '🔄', color: 'bg-yellow-500', href: route('peminjaman.index') },
                    { label: 'Buku Terlambat', value: stats?.buku_terlambat || 0, icon: '⚠️', color: 'bg-red-500', href: route('pengembalian.index') },
                    { label: 'Reservasi Menunggu', value: stats?.reservasi_menunggu || 0, icon: '🔖', color: 'bg-indigo-500', href: route('reservasi.kelola') },
                ].map((s) => (
                    <Link key={s.label} href={s.href}
                        className="bg-white rounded-xl shadow-sm p-4 flex items-center gap-3 hover:shadow-md transition">
                        <div className={`${s.color} w-10 h-10 rounded-lg flex items-center justify-center text-lg flex-shrink-0`}>
                            {s.icon}
                        </div>
                        <div>
                            <p className="text-lg font-bold text-gray-800">{s.value}</p>
                            <p className="text-xs text-gray-500">{s.label}</p>
                        </div>
                    </Link>
                ))}
            </div>

            {/* Aksi Cepat */}
            <div className="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                <p className="text-sm font-semibold text-blue-700 mb-3">⚡ Aksi Cepat</p>
                <div className="flex flex-wrap gap-3">
                    <Link href={route('peminjaman.create')}
                        className="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        + Peminjaman Baru
                    </Link>
                    <Link href={route('pengembalian.index')}
                        className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        ↩️ Proses Pengembalian
                    </Link>
                    <Link href={route('reservasi.kelola')}
                        className="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        🔖 Kelola Reservasi
                    </Link>
                    <Link href={route('pengembalian.denda')}
                        className="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        💰 Kelola Denda
                    </Link>
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
                            <p className="text-sm text-gray-400 text-center py-4">Belum ada peminjaman</p>
                        ) : peminjamanTerbaru?.map((p) => (
                            <div key={p.id} className="flex items-center justify-between">
                                <div className="min-w-0">
                                    <p className="text-sm font-medium text-gray-800 truncate">{p.anggota}</p>
                                    <p className="text-xs text-gray-400 truncate">{p.buku}</p>
                                    <p className="text-xs text-gray-400">{p.tanggal_pinjam} → {p.tanggal_jatuh_tempo}</p>
                                </div>
                                <span className={`px-2 py-0.5 rounded-full text-xs font-medium flex-shrink-0 ml-2 ${statusBadge(p.status)}`}>
                                    {p.status}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Buku Terlambat */}
                <div className="bg-white rounded-xl shadow-sm p-6">
                    <div className="flex items-center justify-between mb-4">
                        <h3 className="font-semibold text-gray-700">⚠️ Buku Terlambat</h3>
                        <Link href={route('pengembalian.index')} className="text-xs text-blue-600 hover:text-blue-800">
                            Proses →
                        </Link>
                    </div>
                    <div className="space-y-3">
                        {terlambat?.length === 0 ? (
                            <p className="text-sm text-gray-400 text-center py-4">Tidak ada buku terlambat 🎉</p>
                        ) : terlambat?.map((p) => (
                            <div key={p.id} className="flex items-center justify-between bg-red-50 p-2 rounded-lg">
                                <div className="min-w-0">
                                    <p className="text-sm font-medium text-gray-800 truncate">{p.anggota?.user?.name}</p>
                                    <p className="text-xs text-gray-400 truncate">
                                        {p.detail?.map((d) => d.buku?.judul).join(', ')}
                                    </p>
                                </div>
                                <span className="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 flex-shrink-0 ml-2">
                                    Terlambat
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
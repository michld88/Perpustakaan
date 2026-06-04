import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function AnggotaDashboard({ stats, peminjaman_aktif }) {
    const formatRupiah = (angka) =>
        'Rp ' + new Intl.NumberFormat('id-ID').format(angka || 0);

    const formatTanggal = (tanggal) => {
        if (!tanggal) return '-';
        return new Date(tanggal).toLocaleDateString('id-ID', {
            day: '2-digit', month: '2-digit', year: 'numeric'
        });
    };

    const cards = [
        { label: 'Sedang Dipinjam', value: stats?.peminjaman_aktif || 0, icon: '📖', color: 'bg-blue-500' },
        { label: 'Total Peminjaman', value: stats?.total_peminjaman || 0, icon: '📋', color: 'bg-green-500' },
        { label: 'Reservasi Aktif', value: stats?.reservasi_aktif || 0, icon: '🔖', color: 'bg-purple-500' },
        { label: 'Denda Belum Bayar', value: formatRupiah(stats?.denda_belum_bayar), icon: '💰', color: 'bg-red-500' },
    ];

    return (
        <AuthenticatedLayout title="Dashboard">
            <Head title="Dashboard" />

            <div className="mb-6">
                <h2 className="text-xl font-bold text-gray-800">Dashboard Anggota</h2>
                <p className="text-sm text-gray-500 mt-1">Ringkasan aktivitas perpustakaan Anda</p>
            </div>

            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
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

            {peminjaman_aktif?.length > 0 && (
                <div className="bg-white rounded-xl shadow-sm p-6">
                    <h3 className="font-semibold text-gray-700 mb-4">📖 Buku Sedang Dipinjam</h3>
                    <div className="space-y-3">
                        {peminjaman_aktif.map((p) => (
                            <div key={p.id} className={`p-3 rounded-lg border ${p.status === 'terlambat' ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-200'}`}>
                                {p.detail?.map((d) => (
                                    <p key={d.id} className="font-medium text-gray-800 text-sm">{d.buku?.judul}</p>
                                ))}
                                <p className={`text-xs mt-1 ${p.status === 'terlambat' ? 'text-red-600 font-semibold' : 'text-gray-500'}`}>
                                    Jatuh tempo: {formatTanggal(p.tanggal_jatuh_tempo)}
                                    {p.status === 'terlambat' && ' ⚠️ TERLAMBAT'}
                                </p>
                            </div>
                        ))}
                    </div>
                    <Link href={route('peminjaman.saya')}
                        className="mt-4 inline-block text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Lihat semua →
                    </Link>
                </div>
            )}
        </AuthenticatedLayout>
    );
}
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function LaporanIndex() {
    const menus = [
        { href: route('laporan.statistik-peminjaman'), label: 'Statistik Peminjaman', icon: '📊', desc: 'Lihat grafik peminjaman harian, mingguan, bulanan, tahunan' },
        { href: route('laporan.buku-terpopuler'), label: 'Buku Terpopuler', icon: '📚', desc: 'Daftar buku yang paling sering dipinjam' },
        { href: route('laporan.anggota-teraktif'), label: 'Anggota Teraktif', icon: '👥', desc: 'Daftar anggota dengan peminjaman terbanyak' },
        { href: route('laporan.denda-terkumpul'), label: 'Denda Terkumpul', icon: '💰', desc: 'Laporan denda keterlambatan pengembalian' },
    ];

    return (
        <AuthenticatedLayout title="Laporan">
            <Head title="Laporan" />

            <div className="mb-6">
                <h2 className="text-xl font-bold text-gray-800">Laporan & Statistik</h2>
                <p className="text-sm text-gray-500 mt-1">Pilih jenis laporan yang ingin dilihat</p>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                {menus.map((menu) => (
                    <Link key={menu.href} href={menu.href}
                        className="bg-white rounded-xl shadow-sm p-6 hover:shadow-md transition flex items-start gap-4">
                        <div className="text-4xl">{menu.icon}</div>
                        <div>
                            <h3 className="font-semibold text-gray-800">{menu.label}</h3>
                            <p className="text-sm text-gray-500 mt-1">{menu.desc}</p>
                        </div>
                    </Link>
                ))}
            </div>
        </AuthenticatedLayout>
    );
}
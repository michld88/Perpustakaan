import { Head, Link, router, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function PeminjamanSaya({ peminjaman }) {
    const { flash } = usePage().props;

    const formatTanggal = (tanggal) => {
        if (!tanggal) return '-';
        return new Date(tanggal).toLocaleDateString('id-ID', {
            day: '2-digit', month: '2-digit', year: 'numeric'
        });
    };

    const statusBadge = (status) => {
        const map = {
            dipinjam:     'bg-yellow-100 text-yellow-700',
            dikembalikan: 'bg-green-100 text-green-700',
            terlambat:    'bg-red-100 text-red-700',
        };
        return map[status] || 'bg-gray-100 text-gray-700';
    };

    const handlePerpanjang = (id) => {
        if (confirm('Perpanjang masa peminjaman 7 hari?')) {
            router.post(route('peminjaman.perpanjang-mandiri', id));
        }
    };

    return (
        <AuthenticatedLayout title="Peminjaman Saya">
            <Head title="Peminjaman Saya" />

            <div className="mb-6">
                <h2 className="text-xl font-bold text-gray-800">Peminjaman Saya</h2>
                <p className="text-sm text-gray-500 mt-1">Riwayat peminjaman buku Anda</p>
            </div>

            {flash?.success && (
                <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 text-sm">
                    {flash.success}
                </div>
            )}

            {flash?.errors?.error && (
                <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
                    {flash.errors.error}
                </div>
            )}

            <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">No</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Buku</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Tgl Pinjam</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Jatuh Tempo</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Tgl Kembali</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {peminjaman.data.length === 0 ? (
                            <tr>
                                <td colSpan={7} className="text-center py-8 text-gray-400">
                                    Belum ada riwayat peminjaman
                                </td>
                            </tr>
                        ) : peminjaman.data.map((p, i) => (
                            <tr key={p.id} className={`hover:bg-gray-50 ${p.status === 'terlambat' ? 'bg-red-50' : ''}`}>
                                <td className="px-4 py-3 text-gray-500">
                                    {(peminjaman.current_page - 1) * peminjaman.per_page + i + 1}
                                </td>
                                <td className="px-4 py-3">
                                    {p.detail?.map((d) => (
                                        <p key={d.id} className="font-medium text-gray-800">{d.buku?.judul}</p>
                                    ))}
                                </td>
                                <td className="px-4 py-3 text-gray-600">{formatTanggal(p.tanggal_pinjam)}</td>
                                <td className="px-4 py-3 text-gray-600">
                                    {formatTanggal(p.tanggal_jatuh_tempo)}
                                    {p.sudah_diperpanjang && (
                                        <span className="ml-1 text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded-full">+7hr</span>
                                    )}
                                </td>
                                <td className="px-4 py-3 text-gray-600">{formatTanggal(p.tanggal_kembali)}</td>
                                <td className="px-4 py-3">
                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${statusBadge(p.status)}`}>
                                        {p.status}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    {p.status === 'dipinjam' && !p.sudah_diperpanjang && (
                                        <button onClick={() => handlePerpanjang(p.id)}
                                            className="bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1 rounded text-xs font-medium transition">
                                            Perpanjang
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
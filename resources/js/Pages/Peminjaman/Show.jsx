import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function PeminjamanShow({ peminjaman }) {
    const statusBadge = (status) => {
        const map = {
            dipinjam:     'bg-yellow-100 text-yellow-700',
            dikembalikan: 'bg-green-100 text-green-700',
            terlambat:    'bg-red-100 text-red-700',
        };
        return map[status] || 'bg-gray-100 text-gray-700';
    };

    const formatTanggal = (tanggal) => {
        if (!tanggal) return '-';
        return new Date(tanggal).toLocaleDateString('id-ID', {
            day: '2-digit', month: '2-digit', year: 'numeric'
        });
    };

    return (
        <AuthenticatedLayout title="Detail Peminjaman">
            <Head title="Detail Peminjaman" />

            <div className="max-w-3xl">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('peminjaman.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                    <h2 className="text-xl font-bold text-gray-800">Detail Peminjaman</h2>
                </div>

                <div className="bg-white rounded-xl shadow-sm p-6 space-y-6">
                    <div className="flex items-center justify-between">
                        <h3 className="font-semibold text-gray-700">ID Peminjaman #{peminjaman.id}</h3>
                        <span className={`px-3 py-1 rounded-full text-sm font-medium ${statusBadge(peminjaman.status)}`}>
                            {peminjaman.status}
                        </span>
                    </div>

                    <div className="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p className="text-gray-500">Nama Anggota</p>
                            <p className="font-medium text-gray-800">{peminjaman.anggota?.user?.name}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">NIM/NIP</p>
                            <p className="font-medium text-gray-800">{peminjaman.anggota?.nim_nip || '-'}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Tanggal Pinjam</p>
                            <p className="font-medium text-gray-800">{formatTanggal(peminjaman.tanggal_pinjam)}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Jatuh Tempo</p>
                            <p className="font-medium text-gray-800">{formatTanggal(peminjaman.tanggal_jatuh_tempo)}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Tanggal Kembali</p>
                            <p className="font-medium text-gray-800">{formatTanggal(peminjaman.tanggal_kembali) || 'Belum dikembalikan'}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Diproses oleh</p>
                            <p className="font-medium text-gray-800">{peminjaman.pustakawan?.name}</p>
                        </div>
                    </div>

                    <div>
                        <p className="text-gray-500 text-sm mb-2">Buku yang Dipinjam</p>
                        <div className="border border-gray-200 rounded-lg divide-y">
                            {peminjaman.detail?.map((d) => (
                                <div key={d.id} className="p-3 flex items-center justify-between">
                                    <div>
                                        <p className="text-sm font-medium text-gray-800">{d.buku?.judul}</p>
                                        <p className="text-xs text-gray-400">{d.buku?.kategori?.nama}</p>
                                    </div>
                                    <span className="text-xs text-gray-500">x{d.jumlah}</span>
                                </div>
                            ))}
                        </div>
                    </div>

                    {peminjaman.catatan && (
                        <div>
                            <p className="text-gray-500 text-sm mb-1">Catatan</p>
                            <p className="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{peminjaman.catatan}</p>
                        </div>
                    )}

                    <div className="pt-4 border-t">
                        <Link href={route('peminjaman.index')}
                            className="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                            Kembali ke Daftar
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
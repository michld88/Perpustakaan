import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function PengembalianShow({ peminjaman, hariTerlambat, totalDenda, tarifPerHari }) {
    const formatTanggal = (tanggal) => {
        if (!tanggal) return '-';
        return new Date(tanggal).toLocaleDateString('id-ID', {
            day: '2-digit', month: '2-digit', year: 'numeric'
        });
    };

    const handleProses = () => {
        if (confirm('Konfirmasi pengembalian buku ini?')) {
            router.post(route('pengembalian.proses', peminjaman.id));
        }
    };

    const handlePerpanjang = () => {
        if (confirm('Perpanjang masa peminjaman 7 hari?')) {
            router.post(route('pengembalian.perpanjang', peminjaman.id));
        }
    };

    const formatRupiah = (angka) =>
        'Rp ' + new Intl.NumberFormat('id-ID').format(angka);

    return (
        <AuthenticatedLayout title="Proses Pengembalian">
            <Head title="Proses Pengembalian" />

            <div className="max-w-3xl">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('pengembalian.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                    <h2 className="text-xl font-bold text-gray-800">Proses Pengembalian Buku</h2>
                </div>

                {hariTerlambat > 0 && (
                    <div className="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
                        <p className="text-red-700 font-semibold">⚠️ Terlambat {hariTerlambat} hari</p>
                        <p className="text-red-600 text-sm mt-1">
                            Denda: {formatRupiah(tarifPerHari)}/hari × {hariTerlambat} hari = <strong>{formatRupiah(totalDenda)}</strong>
                        </p>
                    </div>
                )}

                {!peminjaman.sudah_diperpanjang && peminjaman.status === 'dipinjam' && hariTerlambat === 0 && (
                    <div className="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                        <p className="text-blue-700 text-sm">
                            💡 Belum pernah diperpanjang. Bisa diperpanjang 1 kali (+7 hari).
                        </p>
                    </div>
                )}

                <div className="bg-white rounded-xl shadow-sm p-6 space-y-5">
                    <div className="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p className="text-gray-500">Anggota</p>
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
                            <p className={`font-medium ${hariTerlambat > 0 ? 'text-red-600' : 'text-gray-800'}`}>
                                {formatTanggal(peminjaman.tanggal_jatuh_tempo)}
                                {peminjaman.sudah_diperpanjang && (
                                    <span className="ml-2 text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full">
                                        Diperpanjang
                                    </span>
                                )}
                            </p>
                        </div>
                    </div>

                    <div>
                        <p className="text-gray-500 text-sm mb-2">Buku yang Dipinjam</p>
                        <div className="border border-gray-200 rounded-lg divide-y">
                            {peminjaman.detail?.map((d) => (
                                <div key={d.id} className="p-3">
                                    <p className="text-sm font-medium text-gray-800">{d.buku?.judul}</p>
                                    <p className="text-xs text-gray-400">{d.buku?.kategori?.nama}</p>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="flex gap-3 pt-4 border-t">
                        <button onClick={handleProses}
                            className="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                            ✓ Proses Pengembalian
                        </button>
                        {!peminjaman.sudah_diperpanjang && peminjaman.status === 'dipinjam' && hariTerlambat === 0 && (
                            <button onClick={handlePerpanjang}
                                className="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                                ↺ Perpanjang 7 Hari
                            </button>
                        )}
                        <Link href={route('pengembalian.index')}
                            className="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                            Batal
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
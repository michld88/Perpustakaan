import { Head, Link, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function BukuShow({ buku }) {
    const statusBadge = (status) => {
        const map = {
            tersedia: 'bg-green-100 text-green-700',
            dipinjam: 'bg-yellow-100 text-yellow-700',
            rusak:    'bg-red-100 text-red-700',
            hilang:   'bg-gray-100 text-gray-700',
        };
        return map[status] || 'bg-gray-100 text-gray-700';
    };

    const { auth } = usePage().props;
    const isAnggota = auth.user?.role === 'anggota';

    return (
        <AuthenticatedLayout title="Detail Buku">
            <Head title="Detail Buku" />
            <div className="max-w-3xl">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('buku.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                    <h2 className="text-xl font-bold text-gray-800">Detail Buku</h2>
                </div>
                <div className="bg-white rounded-xl shadow-sm p-6 space-y-4">
                    <div className="flex items-start justify-between">
                        <h3 className="text-2xl font-bold text-gray-800">{buku.judul}</h3>
                        <span className={`px-3 py-1 rounded-full text-sm font-medium ${statusBadge(buku.status)}`}>
                            {buku.status}
                        </span>
                    </div>
                    <div className="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p className="text-gray-500">ISBN</p>
                            <p className="font-medium text-gray-800">{buku.isbn}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Kategori</p>
                            <p className="font-medium text-gray-800">{buku.kategori?.nama}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Penerbit</p>
                            <p className="font-medium text-gray-800">{buku.penerbit?.nama}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Tahun Terbit</p>
                            <p className="font-medium text-gray-800">{buku.tahun_terbit}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Penulis</p>
                            <p className="font-medium text-gray-800">
                                {buku.penulis.map((p) => p.nama).join(', ')}
                            </p>
                        </div>
                        <div>
                            <p className="text-gray-500">Ketersediaan</p>
                            <p className="font-medium text-gray-800">
                                {buku.jumlah_tersedia} / {buku.jumlah_eksemplar} eksemplar
                            </p>
                        </div>
                    </div>
                    {buku.deskripsi && (
                        <div>
                            <p className="text-gray-500 text-sm mb-1">Deskripsi</p>
                            <p className="text-gray-700 text-sm">{buku.deskripsi}</p>
                        </div>
                    )}
                    <div className="flex gap-3 pt-4 border-t">
                        {!isAnggota && (
                            <Link href={route('buku.edit', buku.id)}
                                className="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                Edit Buku
                            </Link>
                        )}
                        <Link href={route('buku.index')}
                            className="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                            Kembali ke Daftar
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
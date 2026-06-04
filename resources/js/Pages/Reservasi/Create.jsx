import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function ReservasiCreate({ buku }) {
    const { data, setData, post, processing, errors } = useForm({
        buku_id: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('reservasi.store'));
    };

    return (
        <AuthenticatedLayout title="Reservasi Buku">
            <Head title="Reservasi Buku" />

            <div className="max-w-2xl">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('reservasi.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                    <h2 className="text-xl font-bold text-gray-800">Form Reservasi Buku</h2>
                </div>

                <div className="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 text-sm text-blue-700">
                    <p>📌 Reservasi hanya untuk buku yang <strong>sedang habis dipinjam</strong>.</p>
                    <p>📧 Anda akan mendapat notifikasi email saat buku tersedia.</p>
                    <p>⏰ Setelah notifikasi, buku hanya tersedia <strong>2 hari</strong> sebelum reservasi kadaluarsa.</p>
                </div>

                <div className="bg-white rounded-xl shadow-sm p-6">
                    {buku.length === 0 ? (
                        <div className="text-center py-8">
                            <p className="text-gray-500">Tidak ada buku yang bisa direservasi saat ini.</p>
                            <p className="text-sm text-gray-400 mt-1">Semua buku sedang tersedia untuk dipinjam langsung.</p>
                            <Link href={route('buku.index')}
                                className="mt-4 inline-block bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                                Lihat Katalog Buku
                            </Link>
                        </div>
                    ) : (
                        <form onSubmit={handleSubmit} className="space-y-5">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-2">
                                    Pilih Buku yang Ingin Direservasi <span className="text-red-500">*</span>
                                </label>
                                {errors.buku_id && <p className="mb-2 text-xs text-red-600">{errors.buku_id}</p>}
                                <div className="border border-gray-200 rounded-lg divide-y max-h-72 overflow-y-auto">
                                    {buku.map((b) => (
                                        <label key={b.id}
                                            className={`flex items-center gap-3 p-3 cursor-pointer hover:bg-gray-50 transition ${data.buku_id == b.id ? 'bg-blue-50' : ''}`}>
                                            <input type="radio"
                                                name="buku_id"
                                                value={b.id}
                                                checked={data.buku_id == b.id}
                                                onChange={(e) => setData('buku_id', e.target.value)}
                                                className="text-blue-600 focus:ring-blue-500" />
                                            <div className="flex-1">
                                                <p className="text-sm font-medium text-gray-800">{b.judul}</p>
                                                <p className="text-xs text-gray-400">
                                                    {b.kategori?.nama} •
                                                    {b.penulis?.map((p) => p.nama).join(', ')}
                                                </p>
                                            </div>
                                            <span className="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">
                                                Sedang Dipinjam
                                            </span>
                                        </label>
                                    ))}
                                </div>
                            </div>

                            <div className="flex gap-3 pt-2">
                                <button type="submit" disabled={processing || !data.buku_id}
                                    className="bg-blue-700 hover:bg-blue-800 disabled:opacity-60 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                                    {processing ? 'Memproses...' : 'Buat Reservasi'}
                                </button>
                                <Link href={route('reservasi.index')}
                                    className="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                                    Batal
                                </Link>
                            </div>
                        </form>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function BukuEdit({ buku, kategori, penerbit, penulis }) {
    const { data, setData, put, processing, errors } = useForm({
        judul:            buku.judul,
        isbn:             buku.isbn,
        kategori_id:      buku.kategori_id,
        penerbit_id:      buku.penerbit_id,
        tahun_terbit:     buku.tahun_terbit,
        jumlah_eksemplar: buku.jumlah_eksemplar,
        deskripsi:        buku.deskripsi || '',
        status:           buku.status,
        penulis_id:       buku.penulis.map((p) => p.id),
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('buku.update', buku.id));
    };

    const togglePenulis = (id) => {
        const ids = data.penulis_id.includes(id)
            ? data.penulis_id.filter((p) => p !== id)
            : [...data.penulis_id, id];
        setData('penulis_id', ids);
    };

    return (
        <AuthenticatedLayout title="Edit Buku">
            <Head title="Edit Buku" />
            <div className="max-w-3xl">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('buku.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                    <h2 className="text-xl font-bold text-gray-800">Edit Buku</h2>
                </div>
                <div className="bg-white rounded-xl shadow-sm p-6">
                    <form onSubmit={handleSubmit} className="space-y-5">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Judul Buku</label>
                            <input type="text" value={data.judul}
                                onChange={(e) => setData('judul', e.target.value)}
                                className={`w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.judul ? 'border-red-400' : 'border-gray-300'}`} />
                            {errors.judul && <p className="mt-1 text-xs text-red-600">{errors.judul}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">ISBN</label>
                            <input type="text" value={data.isbn}
                                onChange={(e) => setData('isbn', e.target.value)}
                                className={`w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.isbn ? 'border-red-400' : 'border-gray-300'}`} />
                            {errors.isbn && <p className="mt-1 text-xs text-red-600">{errors.isbn}</p>}
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                                <select value={data.kategori_id}
                                    onChange={(e) => setData('kategori_id', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    {kategori.map((k) => <option key={k.id} value={k.id}>{k.nama}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Penerbit</label>
                                <select value={data.penerbit_id}
                                    onChange={(e) => setData('penerbit_id', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    {penerbit.map((p) => <option key={p.id} value={p.id}>{p.nama}</option>)}
                                </select>
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Tahun Terbit</label>
                                <input type="number" value={data.tahun_terbit}
                                    onChange={(e) => setData('tahun_terbit', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    min="1900" max={new Date().getFullYear()} />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Jumlah Eksemplar</label>
                                <input type="number" value={data.jumlah_eksemplar}
                                    onChange={(e) => setData('jumlah_eksemplar', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    min="1" />
                            </div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select value={data.status}
                                onChange={(e) => setData('status', e.target.value)}
                                className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="tersedia">Tersedia</option>
                                <option value="dipinjam">Dipinjam</option>
                                <option value="rusak">Rusak</option>
                                <option value="hilang">Hilang</option>
                            </select>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">Penulis</label>
                            <div className="grid grid-cols-2 gap-2">
                                {penulis.map((p) => (
                                    <label key={p.id} className="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox"
                                            checked={data.penulis_id.includes(p.id)}
                                            onChange={() => togglePenulis(p.id)}
                                            className="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                        <span className="text-sm text-gray-700">{p.nama}</span>
                                    </label>
                                ))}
                            </div>
                            {errors.penulis_id && <p className="mt-1 text-xs text-red-600">{errors.penulis_id}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                            <textarea value={data.deskripsi}
                                onChange={(e) => setData('deskripsi', e.target.value)}
                                rows={4}
                                className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                        <div className="flex gap-3 pt-2">
                            <button type="submit" disabled={processing}
                                className="bg-blue-700 hover:bg-blue-800 disabled:opacity-60 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                                {processing ? 'Menyimpan...' : 'Update Buku'}
                            </button>
                            <Link href={route('buku.index')}
                                className="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                                Batal
                            </Link>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
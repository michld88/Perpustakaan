import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function PeminjamanCreate({ anggota, buku, kuota, durasi }) {
    const { data, setData, post, processing, errors } = useForm({
        anggota_id: '',
        buku_ids: [],
        catatan: '',
    });

    const [selectedAnggota, setSelectedAnggota] = useState(null);

    const handleAnggotaChange = (id) => {
        setData('anggota_id', id);
        const found = anggota.find((a) => a.id == id);
        setSelectedAnggota(found || null);
    };

    const toggleBuku = (id) => {
        const ids = data.buku_ids.includes(id)
            ? data.buku_ids.filter((b) => b !== id)
            : [...data.buku_ids, id];
        setData('buku_ids', ids);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('peminjaman.store'));
    };

    const tanggalJatuhTempo = () => {
        const d = new Date();
        d.setDate(d.getDate() + durasi);
        return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
    };

    return (
        <AuthenticatedLayout title="Peminjaman Baru">
            <Head title="Peminjaman Baru" />

            <div className="max-w-4xl">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('peminjaman.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                    <h2 className="text-xl font-bold text-gray-800">Form Peminjaman Buku</h2>
                </div>

                <div className="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6 text-sm text-blue-700">
                    <p>📋 Kuota maksimal: <strong>{kuota} buku</strong> per anggota</p>
                    <p>📅 Durasi peminjaman: <strong>{durasi} hari</strong> | Jatuh tempo: <strong>{tanggalJatuhTempo()}</strong></p>
                </div>

                <div className="bg-white rounded-xl shadow-sm p-6">
                    <form onSubmit={handleSubmit} className="space-y-6">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Pilih Anggota <span className="text-red-500">*</span>
                            </label>
                            <select value={data.anggota_id}
                                onChange={(e) => handleAnggotaChange(e.target.value)}
                                className={`w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.anggota_id ? 'border-red-400' : 'border-gray-300'}`}>
                                <option value="">-- Pilih Anggota --</option>
                                {anggota.map((a) => (
                                    <option key={a.id} value={a.id}>
                                        {a.nama} {a.nim_nip ? `(${a.nim_nip})` : ''}
                                    </option>
                                ))}
                            </select>
                            {errors.anggota_id && <p className="mt-1 text-xs text-red-600">{errors.anggota_id}</p>}
                            {selectedAnggota && (
                                <p className="mt-1 text-xs text-green-600">✓ {selectedAnggota.email}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                Pilih Buku <span className="text-red-500">*</span>
                                <span className="ml-2 text-xs text-gray-400">(Maks. {kuota} buku)</span>
                            </label>
                            {errors.buku_ids && <p className="mb-2 text-xs text-red-600">{errors.buku_ids}</p>}
                            <div className="border border-gray-200 rounded-lg divide-y max-h-72 overflow-y-auto">
                                {buku.length === 0 ? (
                                    <p className="p-4 text-sm text-gray-400 text-center">Tidak ada buku tersedia</p>
                                ) : buku.map((b) => (
                                    <label key={b.id}
                                        className={`flex items-center gap-3 p-3 cursor-pointer hover:bg-gray-50 transition ${data.buku_ids.includes(b.id) ? 'bg-blue-50' : ''}`}>
                                        <input type="checkbox"
                                            checked={data.buku_ids.includes(b.id)}
                                            onChange={() => toggleBuku(b.id)}
                                            disabled={!data.buku_ids.includes(b.id) && data.buku_ids.length >= kuota}
                                            className="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                                        <div className="flex-1">
                                            <p className="text-sm font-medium text-gray-800">{b.judul}</p>
                                            <p className="text-xs text-gray-400">
                                                {b.kategori?.nama} • Tersedia: {b.jumlah_tersedia}
                                            </p>
                                        </div>
                                    </label>
                                ))}
                            </div>
                            <p className="mt-1 text-xs text-gray-500">
                                Dipilih: {data.buku_ids.length} dari {kuota} buku
                            </p>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                            <textarea value={data.catatan}
                                onChange={(e) => setData('catatan', e.target.value)}
                                rows={3}
                                className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Catatan tambahan (opsional)" />
                        </div>

                        <div className="flex gap-3 pt-2">
                            <button type="submit" disabled={processing}
                                className="bg-blue-700 hover:bg-blue-800 disabled:opacity-60 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                                {processing ? 'Memproses...' : 'Proses Peminjaman'}
                            </button>
                            <Link href={route('peminjaman.index')}
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
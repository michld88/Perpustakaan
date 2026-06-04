import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function AnggotaEdit({ anggota }) {
    const { data, setData, put, processing, errors } = useForm({
        name: anggota.user.name,
        email: anggota.user.email,
        nim_nip: anggota.nim_nip || '',
        alamat: anggota.alamat || '',
        telepon: anggota.telepon || '',
        status: anggota.status,
        foto: null,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('anggota.update', anggota.id));
    };

    return (
        <AuthenticatedLayout title="Edit Anggota">
            <Head title="Edit Anggota" />
            
            <div className="max-w-3xl">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('anggota.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                    <h2 className="text-xl font-bold text-gray-800">Edit Data Anggota</h2>
                </div>

                <div className="bg-white rounded-xl shadow-sm p-6">
                    <form onSubmit={handleSubmit} className="space-y-5">
                        <div className="flex items-center gap-4 mb-4">
                            {anggota.foto ? (
                                <img src={`/storage/${anggota.foto}`} alt="" className="w-16 h-16 rounded-full object-cover" />
                            ) : (
                                <div className="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center text-2xl text-gray-500">
                                    {data.name.charAt(0)}
                                </div>
                            )}
                            <div>
                                <p className="text-sm text-gray-500">Foto Saat Ini</p>
                                <p className="text-xs text-gray-400">Upload baru untuk mengganti</p>
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)}
                                className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">NIM/NIP</label>
                                <input type="text" value={data.nim_nip} onChange={(e) => setData('nim_nip', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                                <input type="text" value={data.telepon} onChange={(e) => setData('telepon', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select value={data.status} onChange={(e) => setData('status', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                            <textarea value={data.alamat} onChange={(e) => setData('alamat', e.target.value)} rows={3}
                                className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Foto Baru (Opsional)</label>
                            <input type="file" accept="image/*" onChange={(e) => setData('foto', e.target.files[0])}
                                className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div className="flex gap-3 pt-2">
                            <button type="submit" disabled={processing}
                                className="bg-blue-700 hover:bg-blue-800 disabled:opacity-60 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                                {processing ? 'Menyimpan...' : 'Update Data'}
                            </button>
                            <Link href={route('anggota.index')}
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
import { Head, useForm, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function AnggotaCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        nim_nip: '',
        alamat: '',
        telepon: '',
        foto: null,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('anggota.store'));
    };

    const handleFotoChange = (e) => {
        setData('foto', e.target.files[0]);
    };

    return (
        <AuthenticatedLayout title="Tambah Anggota">
            <Head title="Tambah Anggota" />
            
            <div className="max-w-3xl">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('anggota.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                    <h2 className="text-xl font-bold text-gray-800">Tambah Anggota Baru</h2>
                </div>

                <div className="bg-white rounded-xl shadow-sm p-6">
                    <form onSubmit={handleSubmit} className="space-y-5" encType="multipart/form-data">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)}
                                className={`w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.name ? 'border-red-400' : 'border-gray-300'}`} />
                            {errors.name && <p className="mt-1 text-xs text-red-600">{errors.name}</p>}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)}
                                    className={`w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.email ? 'border-red-400' : 'border-gray-300'}`} />
                                {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)}
                                    className={`w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.password ? 'border-red-400' : 'border-gray-300'}`} />
                                {errors.password && <p className="mt-1 text-xs text-red-600">{errors.password}</p>}
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">NIM/NIP (Opsional)</label>
                                <input type="text" value={data.nim_nip} onChange={(e) => setData('nim_nip', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                                <input type="text" value={data.telepon} onChange={(e) => setData('telepon', e.target.value)}
                                    className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            </div>
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                            <textarea value={data.alamat} onChange={(e) => setData('alamat', e.target.value)} rows={3}
                                className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Foto (Max 2MB)</label>
                            <input type="file" accept="image/*" onChange={handleFotoChange}
                                className="w-full px-4 py-2.5 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" />
                            {errors.foto && <p className="mt-1 text-xs text-red-600">{errors.foto}</p>}
                        </div>

                        <div className="flex gap-3 pt-2">
                            <button type="submit" disabled={processing}
                                className="bg-blue-700 hover:bg-blue-800 disabled:opacity-60 text-white font-semibold px-6 py-2.5 rounded-lg text-sm transition">
                                {processing ? 'Menyimpan...' : 'Simpan Anggota'}
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
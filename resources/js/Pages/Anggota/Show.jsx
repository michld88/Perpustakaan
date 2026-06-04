import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function AnggotaShow({ anggota }) {
    return (
        <AuthenticatedLayout title="Detail Anggota">
            <Head title="Detail Anggota" />
            
            <div className="max-w-3xl">
                <div className="flex items-center gap-4 mb-6">
                    <Link href={route('anggota.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                    <h2 className="text-xl font-bold text-gray-800">Detail Anggota</h2>
                </div>

                <div className="bg-white rounded-xl shadow-sm p-6">
                    <div className="flex items-start gap-6 mb-6">
                        {anggota.foto ? (
                            <img src={`/storage/${anggota.foto}`} alt="" className="w-24 h-24 rounded-full object-cover" />
                        ) : (
                            <div className="w-24 h-24 rounded-full bg-gray-200 flex items-center justify-center text-3xl text-gray-500">
                                {anggota.user.name.charAt(0)}
                            </div>
                        )}
                        <div>
                            <h3 className="text-2xl font-bold text-gray-800">{anggota.user.name}</h3>
                            <p className="text-gray-500">{anggota.user.email}</p>
                            <span className={`inline-block mt-2 px-3 py-1 rounded-full text-xs font-medium ${anggota.status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`}>
                                {anggota.status.toUpperCase()}
                            </span>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 gap-4 text-sm border-t pt-4">
                        <div>
                            <p className="text-gray-500">NIM/NIP</p>
                            <p className="font-medium text-gray-800">{anggota.nim_nip || '-'}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Telepon</p>
                            <p className="font-medium text-gray-800">{anggota.telepon || '-'}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Tanggal Daftar</p>
                            <p className="font-medium text-gray-800">{new Date(anggota.tanggal_daftar).toLocaleDateString('id-ID')}</p>
                        </div>
                        <div>
                            <p className="text-gray-500">Nomor Kartu</p>
                            <p className="font-medium text-gray-800">{anggota.kartu?.nomor_kartu || 'Belum dicetak'}</p>
                        </div>
                        <div className="col-span-2">
                            <p className="text-gray-500">Alamat</p>
                            <p className="font-medium text-gray-800">{anggota.alamat || '-'}</p>
                        </div>
                    </div>

                    <div className="flex gap-3 pt-6 border-t mt-6">
                        <Link href={route('anggota.edit', anggota.id)}
                            className="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                            Edit Data
                        </Link>
                        <Link href={route('anggota.cetak-kartu', anggota.id)}
                            className="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                            Cetak Kartu
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
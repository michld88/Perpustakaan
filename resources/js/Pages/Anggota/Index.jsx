import { Head, Link, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useState } from 'react';

export default function AnggotaIndex({ anggota, filters }) {
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || '');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('anggota.index'), { search, status }, { preserveState: true });
    };

    const handleDelete = (id) => {
        if (confirm('Yakin ingin menghapus anggota ini?')) {
            router.delete(route('anggota.destroy', id));
        }
    };

    const statusBadge = (status) => {
        return status === 'aktif' 
            ? 'bg-green-100 text-green-700' 
            : 'bg-red-100 text-red-700';
    };

    return (
        <AuthenticatedLayout title="Manajemen Anggota">
            <Head title="Manajemen Anggota" />

            <div className="flex items-center justify-between mb-6">
                <h2 className="text-xl font-bold text-gray-800">Daftar Anggota</h2>
                <Link href={route('anggota.create')}
                    className="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                    + Tambah Anggota
                </Link>
            </div>

            {/* Filter */}
            <div className="bg-white rounded-xl shadow-sm p-4 mb-6">
                <form onSubmit={handleSearch} className="flex flex-wrap gap-3">
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Cari nama, email, NIM/NIP..."
                        className="flex-1 min-w-48 px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <select value={status} onChange={(e) => setStatus(e.target.value)}
                        className="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Status</option>
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                    <button type="submit"
                        className="bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-800 transition">
                        Cari
                    </button>
                    <Link href={route('anggota.index')}
                        className="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                        Reset
                    </Link>
                </form>
            </div>

            {/* Tabel */}
            <div className="bg-white rounded-xl shadow-sm overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b">
                        <tr>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">No</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Foto</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Nama</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">NIM/NIP</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Email</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Telepon</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Status</th>
                            <th className="text-left px-4 py-3 font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {anggota.data.length === 0 ? (
                            <tr>
                                <td colSpan={8} className="text-center py-8 text-gray-400">
                                    Tidak ada data anggota
                                </td>
                            </tr>
                        ) : anggota.data.map((a, i) => (
                            <tr key={a.id} className="hover:bg-gray-50 transition">
                                <td className="px-4 py-3 text-gray-500">
                                    {(anggota.current_page - 1) * anggota.per_page + i + 1}
                                </td>
                                <td className="px-4 py-3">
                                    {a.foto ? (
                                        <img src={`/storage/${a.foto}`} alt="" className="w-10 h-10 rounded-full object-cover" />
                                    ) : (
                                        <div className="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs">
                                            {a.user.name.charAt(0)}
                                        </div>
                                    )}
                                </td>
                                <td className="px-4 py-3 font-medium text-gray-800">{a.user.name}</td>
                                <td className="px-4 py-3 text-gray-600">{a.nim_nip || '-'}</td>
                                <td className="px-4 py-3 text-gray-600">{a.user.email}</td>
                                <td className="px-4 py-3 text-gray-600">{a.telepon || '-'}</td>
                                <td className="px-4 py-3">
                                    <span className={`px-2 py-1 rounded-full text-xs font-medium ${statusBadge(a.status)}`}>
                                        {a.status}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    <div className="flex gap-2 flex-wrap">
                                        <Link href={route('anggota.show', a.id)}
                                            className="text-blue-600 hover:text-blue-800 text-xs font-medium">Detail</Link>
                                        <Link href={route('anggota.edit', a.id)}
                                            className="text-yellow-600 hover:text-yellow-800 text-xs font-medium">Edit</Link>
                                        <Link href={route('anggota.cetak-kartu', a.id)}
                                            className="text-green-600 hover:text-green-800 text-xs font-medium">Cetak Kartu</Link>
                                        <button onClick={() => handleDelete(a.id)}
                                            className="text-red-600 hover:text-red-800 text-xs font-medium">Hapus</button>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AuthenticatedLayout>
    );
}
import { Head, Link } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function CetakKartu({ anggota, kartu }) {
    const handlePrint = () => {
        window.print();
    };

    return (
        <AuthenticatedLayout title="Cetak Kartu Anggota">
            <Head title="Cetak Kartu" />
            
            <div className="max-w-2xl mx-auto">
                <div className="flex items-center justify-between mb-6 no-print">
                    <Link href={route('anggota.index')} className="text-gray-500 hover:text-gray-700">← Kembali</Link>
                    <button onClick={handlePrint}
                        className="bg-blue-700 hover:bg-blue-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                        🖨️ Cetak / Simpan PDF
                    </button>
                </div>

                {/* Kartu Anggota */}
                <div className="bg-white border-2 border-blue-900 rounded-xl p-8 shadow-lg" id="kartu-anggota">
                    <div className="flex items-center gap-4 mb-6 border-b-2 border-blue-900 pb-4">
                        <div className="w-16 h-16 bg-blue-900 rounded-full flex items-center justify-center text-white text-2xl">
                            📚
                        </div>
                        <div>
                            <h2 className="text-2xl font-bold text-blue-900">KARTU ANGGOTA</h2>
                            <p className="text-sm text-blue-700">Perpustakaan Sekolah</p>
                        </div>
                    </div>

                    <div className="flex gap-6">
                        <div className="flex-shrink-0">
                            {anggota.foto ? (
                                <img src={`/storage/${anggota.foto}`} alt="" className="w-32 h-40 object-cover rounded-lg border-2 border-gray-300" />
                            ) : (
                                <div className="w-32 h-40 bg-gray-200 rounded-lg flex items-center justify-center text-4xl text-gray-400">
                                    {anggota.user.name.charAt(0)}
                                </div>
                            )}
                        </div>
                        
                        <div className="flex-1 space-y-3">
                            <div>
                                <p className="text-xs text-gray-500">Nomor Kartu</p>
                                <p className="text-xl font-bold text-blue-900">{kartu.nomor_kartu}</p>
                            </div>
                            <div>
                                <p className="text-xs text-gray-500">Nama</p>
                                <p className="font-semibold text-gray-800">{anggota.user.name}</p>
                            </div>
                            <div>
                                <p className="text-xs text-gray-500">NIM/NIP</p>
                                <p className="font-semibold text-gray-800">{anggota.nim_nip || '-'}</p>
                            </div>
                            <div>
                                <p className="text-xs text-gray-500">Berlaku Hingga</p>
                                <p className="font-semibold text-gray-800">31 Desember {new Date().getFullYear() + 1}</p>
                            </div>
                        </div>

                        <div className="flex-shrink-0 flex flex-col items-center justify-center">
                            {kartu.qr_code_path ? (
                                <img src={`/storage/${kartu.qr_code_path}`} alt="QR Code" className="w-24 h-24" />
                            ) : (
                                <div className="w-24 h-24 bg-gray-100 flex items-center justify-center text-xs text-gray-400">
                                    QR Code
                                </div>
                            )}
                            <p className="text-xs text-gray-500 mt-2">Scan untuk detail</p>
                        </div>
                    </div>

                    <div className="mt-6 pt-4 border-t border-gray-200 text-xs text-gray-500 text-center">
                        <p>Kartu ini adalah bukti keanggotaan resmi perpustakaan.</p>
                        <p>Harap dikembalikan kepada petugas jika ditemukan.</p>
                    </div>
                </div>
            </div>

            <style>{`
                @media print {
                    .no-print { display: none !important; }
                    body { background: white; }
                    #kartu-anggota { box-shadow: none; border: 2px solid #1e3a8a; }
                }
            `}</style>
        </AuthenticatedLayout>
    );
}
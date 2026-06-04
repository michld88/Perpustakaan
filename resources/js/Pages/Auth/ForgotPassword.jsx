import { useForm, Head, Link } from '@inertiajs/react';

export default function ForgotPassword({ status }) {
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <>
            <Head title="Lupa Password" />
            <div className="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700 flex items-center justify-center p-4">
                <div className="w-full max-w-md">
                    <div className="bg-white rounded-2xl shadow-2xl p-8">
                        <h2 className="text-xl font-semibold text-gray-800 mb-2">Lupa Password</h2>
                        <p className="text-sm text-gray-500 mb-6">
                            Masukkan email Anda dan kami akan mengirimkan link untuk mereset password.
                        </p>
                        {status && (
                            <div className="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                                {status}
                            </div>
                        )}
                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className={`w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 ${errors.email ? 'border-red-400' : 'border-gray-300'}`}
                                    placeholder="email@contoh.com"
                                />
                                {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email}</p>}
                            </div>
                            <button type="submit" disabled={processing}
                                className="w-full bg-blue-700 hover:bg-blue-800 disabled:opacity-60 text-white font-semibold py-2.5 rounded-lg text-sm transition">
                                {processing ? 'Mengirim...' : 'Kirim Link Reset'}
                            </button>
                        </form>
                        <div className="mt-4 text-center">
                            <Link href={route('login')} className="text-sm text-blue-600 hover:underline">
                                ← Kembali ke Login
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
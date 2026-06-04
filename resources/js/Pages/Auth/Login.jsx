import { useForm, Head } from '@inertiajs/react';

export default function Login() {
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <>
            <Head title="Login" />
            <div className="min-h-screen bg-gradient-to-br from-blue-900 via-blue-800 to-blue-700 flex items-center justify-center p-4">
                <div className="w-full max-w-md">
                    <div className="text-center mb-8">
                        <div className="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-lg mb-4">
                            <svg className="w-9 h-9 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h1 className="text-2xl font-bold text-white">Sistem Informasi Perpustakaan</h1>
                        <p className="text-blue-200 mt-1 text-sm">Masuk untuk mengakses sistem</p>
                    </div>

                    <div className="bg-white rounded-2xl shadow-2xl p-8">
                        <h2 className="text-xl font-semibold text-gray-800 mb-6">Masuk ke Akun</h2>
                        <form onSubmit={handleSubmit} className="space-y-5">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(e) => setData('email', e.target.value)}
                                    className={`w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition ${errors.email ? 'border-red-400 bg-red-50' : 'border-gray-300'}`}
                                    placeholder="contoh@email.com"
                                    autoComplete="email"
                                />
                                {errors.email && <p className="mt-1 text-xs text-red-600">{errors.email}</p>}
                            </div>

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <input
                                    type="password"
                                    value={data.password}
                                    onChange={(e) => setData('password', e.target.value)}
                                    className={`w-full px-4 py-2.5 rounded-lg border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition ${errors.password ? 'border-red-400 bg-red-50' : 'border-gray-300'}`}
                                    placeholder="••••••••"
                                    autoComplete="current-password"
                                />
                                {errors.password && <p className="mt-1 text-xs text-red-600">{errors.password}</p>}
                            </div>

                            <div className="flex items-center justify-between">
                                <label className="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={data.remember}
                                        onChange={(e) => setData('remember', e.target.checked)}
                                        className="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                    Ingat saya
                                </label>
                                <a href={route('password.request')} className="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                    Lupa password?
                                </a>
                            </div>

                            <button
                                type="submit"
                                disabled={processing}
                                className="w-full bg-blue-700 hover:bg-blue-800 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold py-2.5 rounded-lg transition text-sm"
                            >
                                {processing ? 'Memproses...' : 'Masuk'}
                            </button>
                        </form>
                    </div>

                    <p className="text-center text-blue-200 text-xs mt-6">
                        © {new Date().getFullYear()} Sistem Informasi Perpustakaan
                    </p>
                </div>
            </div>
        </>
    );
}
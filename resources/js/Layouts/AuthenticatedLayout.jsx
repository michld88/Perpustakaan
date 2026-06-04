import { useState } from 'react';
import { Link, usePage } from '@inertiajs/react';

export default function AuthenticatedLayout({ children, title }) {
    const { auth } = usePage().props;
    const [sidebarOpen, setSidebarOpen] = useState(true);

    const navItems = getNavItems(auth.user?.role);

    return (
        <div className="min-h-screen bg-gray-100 flex">
            {/* Sidebar */}
            <aside className={`${sidebarOpen ? 'w-64' : 'w-16'} bg-blue-900 text-white flex flex-col transition-all duration-300`}>
                <div className="p-4 flex items-center gap-3 border-b border-blue-800">
                    <div className="w-8 h-8 bg-white rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg className="w-5 h-5 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    {sidebarOpen && <span className="font-semibold text-sm leading-tight">Sistem Perpustakaan</span>}
                </div>

                <nav className="flex-1 py-4">
                    {navItems.map((item) => (
                        <Link key={item.href} href={item.href}
                            className={`flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-blue-800 transition
                                ${route().current(item.active) ? 'bg-blue-800 border-r-2 border-white' : ''}`}>
                            <span className="flex-shrink-0">{item.icon}</span>
                            {sidebarOpen && <span>{item.label}</span>}
                        </Link>
                    ))}
                </nav>

                <div className="p-4 border-t border-blue-800">
                    {sidebarOpen && (
                        <div className="mb-3">
                            <p className="text-xs font-semibold text-blue-300 truncate">{auth.user?.name}</p>
                            <p className="text-xs text-blue-400">{auth.user?.role_display}</p>
                        </div>
                    )}
                    <Link href={route('logout')} method="post" as="button"
                        className="flex items-center gap-2 text-sm text-blue-300 hover:text-white transition w-full">
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        {sidebarOpen && 'Logout'}
                    </Link>
                </div>
            </aside>

            {/* Main Content */}
            <div className="flex-1 flex flex-col overflow-hidden">
                {/* Top Bar */}
                <header className="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <button onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="text-gray-500 hover:text-gray-700">
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                        <h1 className="text-lg font-semibold text-gray-800">{title}</h1>
                    </div>
                    <span className="text-sm text-gray-500">{new Date().toLocaleDateString('id-ID', {weekday:'long', year:'numeric', month:'long', day:'numeric'})}</span>
                </header>

                <main className="flex-1 overflow-auto p-6">
                    {children}
                </main>
            </div>
        </div>
    );
}

function getNavItems(role) {
    if (role === 'admin' || role === 'pustakawan') {
        return [
            { href: route(`${role}.dashboard`), label: 'Dashboard', icon: '🏠' },
            { href: route('buku.index'), label: 'Manajemen Buku', icon: '📚' },
            { href: route('anggota.index'), label: 'Manajemen Anggota', icon: '👥' },
            { href: route('peminjaman.index'), label: 'Peminjaman', icon: '📋' },
            { href: route('pengembalian.index'), label: 'Pengembalian', icon: '↩️' },
            { href: route('reservasi.kelola'), label: 'Reservasi', icon: '🔖' },
            { href: route('laporan.index'), label: 'Laporan', icon: '📊' },
        ];
    }
    return [
        { href: route('anggota.dashboard'), label: 'Dashboard', icon: '🏠' },
        { href: route('buku.index'), label: 'Katalog Buku', icon: '🔍' },
        { href: route('reservasi.index'), label: 'Reservasi Saya', icon: '🔖' },
        { href: route('peminjaman.saya'), label: 'Peminjaman Saya', icon: '📖' },
    ];
}
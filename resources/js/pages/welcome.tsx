import { dashboard, login, register } from '@/routes';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { ChevronLeft, ChevronRight, FileText, Sparkles } from 'lucide-react';

interface Upload {
    id: number;
    title: string;
    image_path: string | null;
    pdf_path: string;
}

export default function Welcome({
    canRegister = true,
    uploads = [],
}: {
    canRegister?: boolean;
    uploads?: Upload[];
}) {
    const { auth } = usePage<SharedData>().props;
    const [currentSlide, setCurrentSlide] = useState(0);

    useEffect(() => {
        if (uploads.length === 0) return;

        const timer = setInterval(() => {
            setCurrentSlide((prev) => (prev + 1) % uploads.length);
        }, 5000);

        return () => clearInterval(timer);
    }, [uploads.length]);

    const nextSlide = () => {
        setCurrentSlide((prev) => (prev + 1) % uploads.length);
    };

    const prevSlide = () => {
        setCurrentSlide((prev) => (prev - 1 + uploads.length) % uploads.length);
    };

    return (
        <>
            <Head title="Welcome" />
            <div className="relative min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
                <div className="absolute inset-0 bg-grid-slate-100 [mask-image:linear-gradient(0deg,white,rgba(255,255,255,0.6))] dark:bg-grid-slate-700/25"></div>
                
                <nav className="relative border-b border-slate-200/60 bg-white/60 backdrop-blur-xl dark:border-slate-800/60 dark:bg-slate-950/60">
                    <div className="container mx-auto flex items-center justify-between px-6 py-4">
                        <div className="flex items-center gap-3">
                            {/* <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 shadow-lg shadow-blue-600/20">
                                <Sparkles className="h-5 w-5 text-white" />
                            </div> */}
                            <div>
                                <div className="text-xl font-bold bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent dark:from-white dark:to-slate-300">
                                    KFA Highlight
                                </div>
                                <div className="text-xs text-slate-500 dark:text-slate-400">
                                    Featured Content
                                </div>
                            </div>
                        </div>
                        <div className="flex items-center gap-3">
                            {auth.user ? (
                                <Link
                                    href={dashboard()}
                                    className="group relative overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/25 transition-all hover:shadow-xl hover:shadow-blue-600/40 hover:scale-105"
                                >
                                    <span className="relative z-10">Dashboard</span>
                                    <div className="absolute inset-0 -z-0 bg-gradient-to-r from-blue-700 to-indigo-700 opacity-0 transition-opacity group-hover:opacity-100"></div>
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={login()}
                                        className="rounded-xl px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
                                    >
                                        Log in
                                    </Link>
                                    {canRegister && (
                                        <Link
                                            href={register()}
                                            className="group relative overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-blue-600/25 transition-all hover:shadow-xl hover:shadow-blue-600/40 hover:scale-105"
                                        >
                                            <span className="relative z-10">Get Started</span>
                                            <div className="absolute inset-0 -z-0 bg-gradient-to-r from-blue-700 to-indigo-700 opacity-0 transition-opacity group-hover:opacity-100"></div>
                                        </Link>
                                    )}
                                </>
                            )}
                        </div>
                    </div>
                </nav>

                <main className="relative container mx-auto px-6 py-16">
                    {/* <div className="mb-12 text-center"> */}
                        {/* <div className="mb-4 inline-flex items-center gap-2 rounded-full bg-blue-100 px-4 py-1.5 text-sm font-medium text-blue-700 dark:bg-blue-950/50 dark:text-blue-400">
                            <Sparkles className="h-4 w-4" />
                            <span>Featured Highlights</span>
                        </div>
                        <h1 className="mb-4 bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 bg-clip-text text-5xl font-extrabold text-transparent dark:from-white dark:via-slate-100 dark:to-white">
                            Welcome to KFA Highlight
                        </h1>
                        <p className="mx-auto max-w-2xl text-lg text-slate-600 dark:text-slate-400">
                            Explore our curated collection of featured documents and presentations
                        </p>
                    </div> */}

                    {uploads.length > 0 ? (
                        <div className="mx-auto max-w-6xl">
                            <div className="group relative overflow-hidden rounded-3xl bg-white shadow-2xl shadow-slate-900/10 ring-1 ring-slate-200/50 dark:bg-slate-900 dark:shadow-slate-950/50 dark:ring-slate-800/50">
                                <div className="relative aspect-[16/9]">
                                    {uploads.map((upload, index) => (
                                        <div
                                            key={upload.id}
                                            className={`absolute inset-0 transition-all duration-700 ${
                                                index === currentSlide
                                                    ? 'opacity-100 scale-100'
                                                    : 'opacity-0 scale-105'
                                            }`}
                                        >
                                            {upload.image_path ? (
                                                <img
                                                    src={`/storage/${upload.image_path}`}
                                                    alt={upload.title}
                                                    className="h-full w-full object-cover"
                                                />
                                            ) : (
                                                <div className="flex h-full items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900">
                                                    <div className="text-center">
                                                        <FileText className="mx-auto mb-3 h-16 w-16 text-slate-400 dark:text-slate-600" />
                                                        <p className="text-sm text-slate-500 dark:text-slate-500">
                                                            No preview available
                                                        </p>
                                                    </div>
                                                </div>
                                            )}
                                            <div className="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/40 to-transparent"></div>
                                            <div className="absolute inset-x-0 bottom-0 p-8">
                                                <div className="mx-auto max-w-4xl">
                                                    <h2 className="mb-3 text-3xl font-bold text-white drop-shadow-lg">
                                                        {upload.title}
                                                    </h2>
                                                    <a
                                                        href={`/storage/${upload.pdf_path}`}
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                        className="group/button inline-flex items-center gap-2 rounded-xl bg-white px-6 py-3 text-sm font-semibold text-slate-900 shadow-xl transition-all hover:scale-105 hover:shadow-2xl"
                                                    >
                                                        <FileText className="h-4 w-4 transition-transform group-hover/button:rotate-12" />
                                                        <span>View Document</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                </div>

                                {uploads.length > 1 && (
                                    <>
                                        <button
                                            onClick={prevSlide}
                                            className="absolute left-6 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-3 shadow-xl backdrop-blur-sm transition-all hover:scale-110 hover:bg-white hover:shadow-2xl dark:bg-slate-800/90 dark:hover:bg-slate-800"
                                        >
                                            <ChevronLeft className="h-6 w-6 text-slate-900 dark:text-white" />
                                        </button>
                                        <button
                                            onClick={nextSlide}
                                            className="absolute right-6 top-1/2 -translate-y-1/2 rounded-full bg-white/90 p-3 shadow-xl backdrop-blur-sm transition-all hover:scale-110 hover:bg-white hover:shadow-2xl dark:bg-slate-800/90 dark:hover:bg-slate-800"
                                        >
                                            <ChevronRight className="h-6 w-6 text-slate-900 dark:text-white" />
                                        </button>

                                        <div className="absolute inset-x-0 bottom-24 flex justify-center gap-2">
                                            {uploads.map((_, index) => (
                                                <button
                                                    key={index}
                                                    onClick={() =>
                                                        setCurrentSlide(index)
                                                    }
                                                    className={`h-2 rounded-full transition-all duration-300 ${
                                                        index === currentSlide
                                                            ? 'w-8 bg-white shadow-lg shadow-white/50'
                                                            : 'w-2 bg-white/50 hover:bg-white/70'
                                                    }`}
                                                />
                                            ))}
                                        </div>
                                    </>
                                )}
                            </div>
                        </div>
                    ) : (
                        <div className="mx-auto max-w-2xl">
                            <div className="rounded-3xl border border-slate-200 bg-white p-16 text-center shadow-xl dark:border-slate-800 dark:bg-slate-900">
                                <div className="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-950 dark:to-indigo-950">
                                    <FileText className="h-10 w-10 text-blue-600 dark:text-blue-400" />
                                </div>
                                <h3 className="mb-2 text-2xl font-bold text-slate-900 dark:text-white">
                                    No Content Yet
                                </h3>
                                <p className="text-slate-600 dark:text-slate-400">
                                    Check back soon for featured highlights and documents
                                </p>
                            </div>
                        </div>
                    )}
                </main>

                <footer className="relative border-t border-slate-200/60 bg-white/40 backdrop-blur-xl dark:border-slate-800/60 dark:bg-slate-950/40">
                    <div className="container mx-auto px-6 py-8">
                        <div className="text-center text-sm text-slate-600 dark:text-slate-400">
                            © 2025 KFA Highlight. All rights reserved.
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}

import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Link, router } from '@inertiajs/react';
import { FileText, Plus, Trash2, Edit, ExternalLink, Eye, EyeOff } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface Upload {
    id: number;
    title: string;
    original_filename: string;
    pdf_path: string;
    image_path: string | null;
    is_active: boolean;
    order: number;
    created_at: string;
    user: {
        name: string;
    };
}

export default function UploadIndex({ uploads }: { uploads: Upload[] }) {
    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this PDF?')) {
            router.delete(`/uploads/${id}`);
        }
    };

    return (
        <AppLayout breadcrumbs={[{ label: 'PDF Uploads' }]}>
            <div className="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/30 p-6 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
                <div className="container mx-auto">
                    <div className="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h1 className="mb-2 bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-4xl font-extrabold text-transparent dark:from-white dark:to-slate-300">
                                PDF Uploads
                            </h1>
                            <p className="text-lg text-slate-600 dark:text-slate-400">
                                Manage your PDF uploads and presentations
                            </p>
                        </div>
                        <Link href="/uploads/create">
                            <Button className="bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-600/25 transition-all hover:scale-105 hover:shadow-xl hover:shadow-blue-600/40">
                                <Plus className="mr-2 h-4 w-4" />
                                Upload PDF
                            </Button>
                        </Link>
                    </div>

                    <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                        {uploads.map((upload) => (
                            <Card key={upload.id} className="group overflow-hidden border-slate-200/60 bg-gradient-to-br from-white to-slate-50/50 shadow-lg shadow-slate-900/5 transition-all hover:shadow-xl hover:shadow-slate-900/10 dark:border-slate-800/60 dark:from-slate-900 dark:to-slate-900/50">
                                <div className="relative aspect-video overflow-hidden bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900">
                                    {upload.image_path ? (
                                        <img
                                            src={`/storage/${upload.image_path}`}
                                            alt={upload.title}
                                            className="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                        />
                                    ) : (
                                        <div className="flex h-full items-center justify-center">
                                            <FileText className="h-16 w-16 text-slate-400 dark:text-slate-600" />
                                        </div>
                                    )}
                                    <div className="absolute inset-0 bg-gradient-to-t from-slate-900/60 to-transparent opacity-0 transition-opacity group-hover:opacity-100"></div>
                                    <div className="absolute right-3 top-3 flex gap-2 opacity-0 transition-opacity group-hover:opacity-100">
                                        <Link href={`/uploads/${upload.id}/edit`}>
                                            <Button variant="secondary" size="sm" className="h-8 w-8 rounded-full p-0 shadow-lg">
                                                <Edit className="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="secondary"
                                            size="sm"
                                            onClick={() => handleDelete(upload.id)}
                                            className="h-8 w-8 rounded-full p-0 shadow-lg hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/30"
                                        >
                                            <Trash2 className="h-4 w-4" />
                                        </Button>
                                    </div>
                                </div>
                                <CardHeader>
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="flex-1">
                                            <CardTitle className="line-clamp-1 text-lg text-slate-900 dark:text-white">
                                                {upload.title}
                                            </CardTitle>
                                            <CardDescription className="mt-1 line-clamp-1 text-sm">
                                                {upload.original_filename}
                                            </CardDescription>
                                        </div>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            {upload.is_active ? (
                                                <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400">
                                                    <Eye className="h-3 w-3" />
                                                    Active
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-400">
                                                    <EyeOff className="h-3 w-3" />
                                                    Inactive
                                                </span>
                                            )}
                                        </div>
                                        <a
                                            href={`/storage/${upload.pdf_path}`}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="group/link inline-flex items-center gap-1 text-sm font-medium text-blue-600 transition-colors hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            View
                                            <ExternalLink className="h-3 w-3 transition-transform group-hover/link:translate-x-0.5 group-hover/link:-translate-y-0.5" />
                                        </a>
                                    </div>
                                </CardContent>
                            </Card>
                        ))}
                    </div>

                    {uploads.length === 0 && (
                        <Card className="border-slate-200/60 bg-gradient-to-br from-white to-slate-50/50 shadow-xl dark:border-slate-800/60 dark:from-slate-900 dark:to-slate-900/50">
                            <CardContent className="flex flex-col items-center justify-center py-16">
                                <div className="mb-6 flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-950 dark:to-indigo-950">
                                    <FileText className="h-12 w-12 text-blue-600 dark:text-blue-400" />
                                </div>
                                <h3 className="mb-2 text-2xl font-bold text-slate-900 dark:text-white">
                                    No uploads yet
                                </h3>
                                <p className="mb-6 text-center text-slate-600 dark:text-slate-400">
                                    Upload your first PDF to get started
                                </p>
                                <Link href="/uploads/create">
                                    <Button className="bg-gradient-to-r from-blue-600 to-indigo-600 shadow-lg shadow-blue-600/25 transition-all hover:scale-105 hover:shadow-xl hover:shadow-blue-600/40">
                                        <Plus className="mr-2 h-4 w-4" />
                                        Upload PDF
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}

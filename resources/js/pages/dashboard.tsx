import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { FileText, Upload, Users, ArrowUpRight, TrendingUp, CheckCircle2 } from 'lucide-react';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface Stats {
    totalUploads: number;
    activeUploads: number;
    totalUsers: number;
}

export default function Dashboard({ stats }: { stats?: Stats }) {
    const statsData = stats || { totalUploads: 0, activeUploads: 0, totalUsers: 0 };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="min-h-screen bg-gray-50 dark:bg-gray-900">
                <div className="container mx-auto p-6 space-y-6">
                    <div className="mb-6">
                        <h1 className="mb-2 text-3xl font-bold text-gray-900 dark:text-white">
                            Dashboard
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400">
                            Welcome back! Here's an overview of your content.
                        </p>
                    </div>

                    <div className="grid gap-4 md:grid-cols-3">
                        <Card className="border-gray-200 bg-white hover:shadow-md transition-shadow dark:border-gray-800 dark:bg-gray-950">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Total Uploads
                                </CardTitle>
                                <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-950">
                                    <FileText className="h-4 w-4 text-blue-600 dark:text-blue-400" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-gray-900 dark:text-white">
                                    {statsData.totalUploads}
                                </div>
                                <p className="text-xs text-gray-600 dark:text-gray-400">
                                    PDF files uploaded
                                </p>
                            </CardContent>
                        </Card>

                        <Card className="border-gray-200 bg-white hover:shadow-md transition-shadow dark:border-gray-800 dark:bg-gray-950">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Active Uploads
                                </CardTitle>
                                <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-green-100 dark:bg-green-950">
                                    <CheckCircle2 className="h-4 w-4 text-green-600 dark:text-green-400" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-gray-900 dark:text-white">
                                    {statsData.activeUploads}
                                </div>
                                <p className="text-xs text-gray-600 dark:text-gray-400">
                                    Displayed on homepage
                                </p>
                            </CardContent>
                        </Card>

                        <Card className="border-gray-200 bg-white hover:shadow-md transition-shadow dark:border-gray-800 dark:bg-gray-950">
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Total Users
                                </CardTitle>
                                <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-950">
                                    <Users className="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                </div>
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-gray-900 dark:text-white">
                                    {statsData.totalUsers}
                                </div>
                                <p className="text-xs text-gray-600 dark:text-gray-400">
                                    Registered users
                                </p>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2">
                        <Card className="border-gray-200 bg-white hover:shadow-md transition-shadow dark:border-gray-800 dark:bg-gray-950">
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <CardTitle className="text-lg font-semibold text-gray-900 dark:text-white">
                                        PDF Management
                                    </CardTitle>
                                    <FileText className="h-5 w-5 text-gray-400" />
                                </div>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    View and manage all your uploaded PDFs
                                </p>
                            </CardHeader>
                            <CardContent>
                                <Link href="/uploads">
                                    <Button variant="outline" className="w-full">
                                        View All Uploads
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>

                        <Card className="border-gray-200 bg-white hover:shadow-md transition-shadow dark:border-gray-800 dark:bg-gray-950">
                            <CardHeader>
                                <div className="flex items-center justify-between">
                                    <CardTitle className="text-lg font-semibold text-gray-900 dark:text-white">
                                        User Management
                                    </CardTitle>
                                    <Users className="h-5 w-5 text-gray-400" />
                                </div>
                                <p className="text-sm text-gray-600 dark:text-gray-400">
                                    Manage user accounts and permissions
                                </p>
                            </CardHeader>
                            <CardContent>
                                <Link href="/users">
                                    <Button variant="outline" className="w-full">
                                        Manage Users
                                    </Button>
                                </Link>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

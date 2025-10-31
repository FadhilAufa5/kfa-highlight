import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Link, router } from '@inertiajs/react';
import { Plus, Trash2, Edit, User, UserCheck, UserX } from 'lucide-react';
import { Switch } from '@/components/ui/switch';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

interface User {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    created_at: string;
}

interface PaginatedUsers {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export default function UsersIndex({ users }: { users: PaginatedUsers }) {
    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this user?')) {
            router.delete(`/users/${id}`);
        }
    };

    const handleToggleActive = (userId: number, currentStatus: boolean) => {
        router.put(`/users/${userId}`, {
            is_active: !currentStatus,
        }, {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={[{ label: 'Users' }]}>
            <div className="min-h-screen bg-gray-50 p-6 dark:bg-gray-900">
                <div className="container mx-auto">
                    <div className="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h1 className="mb-2 text-3xl font-bold text-gray-900 dark:text-white">User Management</h1>
                            <p className="text-gray-600 dark:text-gray-400">
                                Manage user accounts and permissions
                            </p>
                        </div>
                        <Link href="/users/create">
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                Add User
                            </Button>
                        </Link>
                    </div>

                    <Card className="border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
                        <CardHeader>
                            <CardTitle className="text-gray-900 dark:text-white">Users</CardTitle>
                            <CardDescription>
                                Total: {users.total} users
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Email</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Joined</TableHead>
                                        <TableHead className="text-right">
                                            Actions
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {users.data.map((user) => (
                                        <TableRow key={user.id}>
                                            <TableCell className="font-medium">
                                                <div className="flex items-center gap-2">
                                                    {user.is_active ? (
                                                        <UserCheck className="h-4 w-4 text-green-600 dark:text-green-400" />
                                                    ) : (
                                                        <UserX className="h-4 w-4 text-gray-400 dark:text-gray-600" />
                                                    )}
                                                    {user.name}
                                                </div>
                                            </TableCell>
                                            <TableCell>{user.email}</TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <Switch
                                                        checked={user.is_active}
                                                        onCheckedChange={() => handleToggleActive(user.id, user.is_active)}
                                                    />
                                                    <span className="text-sm text-gray-600 dark:text-gray-400">
                                                        {user.is_active ? 'Active' : 'Inactive'}
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                {new Date(
                                                    user.created_at,
                                                ).toLocaleDateString()}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end gap-2">
                                                    <Link
                                                        href={`/users/${user.id}/edit`}
                                                    >
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                        >
                                                            <Edit className="h-4 w-4" />
                                                        </Button>
                                                    </Link>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() =>
                                                            handleDelete(user.id)
                                                        }
                                                    >
                                                        <Trash2 className="h-4 w-4 text-red-600 dark:text-red-400" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}

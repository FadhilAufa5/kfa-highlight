import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Link, router, usePage } from '@inertiajs/react';
import { Plus, Trash2, Edit, UserCheck, UserX } from 'lucide-react';
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
import { EditUserDialog } from '@/components/edit-user-dialog';
import { useState, useEffect } from 'react';
import { toast } from 'sonner';

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
    const [selectedUser, setSelectedUser] = useState<User | null>(null);
    const [dialogOpen, setDialogOpen] = useState(false);
    const { props } = usePage();

    useEffect(() => {
        if (props.flash?.success) {
            toast.success(props.flash.success as string);
        }
        if (props.flash?.error) {
            toast.error(props.flash.error as string);
        }
    }, [props.flash]);

    const handleDelete = (id: number, name: string) => {
        if (confirm(`Are you sure you want to delete user "${name}"?`)) {
            router.delete(`/users/${id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    toast.success('User deleted successfully!');
                },
                onError: (errors) => {
                    const errorMessages = Object.values(errors).flat();
                    errorMessages.forEach((error) => {
                        toast.error(error as string);
                    });
                },
            });
        }
    };

    const handleEdit = (user: User) => {
        setSelectedUser(user);
        setDialogOpen(true);
    };

    const handleToggleActive = (user: User) => {
        router.put(
            `/users/${user.id}`,
            {
                name: user.name,
                email: user.email,
                is_active: !user.is_active,
            },
            {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    toast.success(
                        `User ${user.is_active ? 'deactivated' : 'activated'} successfully!`,
                    );
                },
                onError: (errors) => {
                    const errorMessages = Object.values(errors).flat();
                    errorMessages.forEach((error) => {
                        toast.error(error as string);
                    });
                },
            },
        );
    };

    return (
        <AppLayout breadcrumbs={[{ label: 'Users' }]}>
            <EditUserDialog
                user={selectedUser}
                open={dialogOpen}
                onOpenChange={setDialogOpen}
            />
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
                                                <div className="flex items-center gap-3">
                                                    <Switch
                                                        checked={user.is_active}
                                                        onCheckedChange={() => handleToggleActive(user)}
                                                        className="data-[state=checked]:bg-green-500"
                                                    />
                                                    <div className="flex items-center gap-2">
                                                        {user.is_active ? (
                                                            <>
                                                                <div className="h-2 w-2 rounded-full bg-green-500 animate-pulse shadow-lg shadow-green-500/50"></div>
                                                                <span className="text-sm font-medium text-green-600 dark:text-green-400">
                                                                    Active
                                                                </span>
                                                            </>
                                                        ) : (
                                                            <>
                                                                <div className="h-2 w-2 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                                                                <span className="text-sm text-gray-500 dark:text-gray-500">
                                                                    Inactive
                                                                </span>
                                                            </>
                                                        )}
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                {new Date(
                                                    user.created_at,
                                                ).toLocaleDateString()}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end gap-2">
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() =>
                                                            handleEdit(user)
                                                        }
                                                    >
                                                        <Edit className="h-4 w-4" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() =>
                                                            handleDelete(
                                                                user.id,
                                                                user.name,
                                                            )
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

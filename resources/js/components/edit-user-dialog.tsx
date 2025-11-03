import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/react';
import { FormEvent, useEffect } from 'react';
import { toast } from 'sonner';

interface User {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
}

interface EditUserDialogProps {
    user: User | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

export function EditUserDialog({
    user,
    open,
    onOpenChange,
}: EditUserDialogProps) {
    const { data, setData, put, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        is_active: true,
    });

    useEffect(() => {
        if (user) {
            setData({
                name: user.name,
                email: user.email,
                password: '',
                password_confirmation: '',
                is_active: user.is_active,
            });
        }
    }, [user]);

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (!user) return;

        put(`/users/${user.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('User updated successfully!');
                onOpenChange(false);
                reset();
            },
            onError: (errors) => {
                const errorMessages = Object.values(errors).flat();
                errorMessages.forEach((error) => {
                    toast.error(error as string);
                });
            },
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle>Edit User</DialogTitle>
                    <DialogDescription>
                        Update user information and settings
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={handleSubmit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="name">Name</Label>
                        <Input
                            id="name"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="Enter name"
                        />
                        {errors.name && (
                            <p className="text-sm text-red-600 dark:text-red-400">
                                {errors.name}
                            </p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            placeholder="Enter email"
                        />
                        {errors.email && (
                            <p className="text-sm text-red-600 dark:text-red-400">
                                {errors.email}
                            </p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="password">
                            New Password (leave blank to keep current)
                        </Label>
                        <Input
                            id="password"
                            type="password"
                            value={data.password}
                            onChange={(e) =>
                                setData('password', e.target.value)
                            }
                            placeholder="Enter new password"
                        />
                        {errors.password && (
                            <p className="text-sm text-red-600 dark:text-red-400">
                                {errors.password}
                            </p>
                        )}
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="password_confirmation">
                            Confirm New Password
                        </Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            value={data.password_confirmation}
                            onChange={(e) =>
                                setData('password_confirmation', e.target.value)
                            }
                            placeholder="Confirm new password"
                        />
                    </div>

                    <div className="flex items-center space-x-2">
                        <Checkbox
                            id="is_active"
                            checked={data.is_active}
                            onCheckedChange={(checked) =>
                                setData('is_active', checked as boolean)
                            }
                        />
                        <Label
                            htmlFor="is_active"
                            className="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                        >
                            Active user
                        </Label>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => {
                                onOpenChange(false);
                                reset();
                            }}
                            disabled={processing}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing ? 'Saving...' : 'Save Changes'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

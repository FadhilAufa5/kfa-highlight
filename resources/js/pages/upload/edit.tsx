import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface Upload {
    id: number;
    title: string;
    original_filename: string;
    is_active: boolean;
    order: number;
}

export default function UploadEdit({ upload }: { upload: Upload }) {
    const { data, setData, put, processing, errors } = useForm({
        title: upload.title,
        is_active: upload.is_active,
        order: upload.order,
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        put(`/uploads/${upload.id}`);
    };

    return (
        <AppLayout
            breadcrumbs={[
                { label: 'PDF Uploads', href: '/uploads' },
                { label: 'Edit' },
            ]}
        >
            <div className="container mx-auto max-w-2xl py-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Edit PDF</CardTitle>
                        <CardDescription>
                            Update PDF information and settings
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={handleSubmit} className="space-y-6">
                            <div className="space-y-2">
                                <Label htmlFor="title">Title</Label>
                                <Input
                                    id="title"
                                    value={data.title}
                                    onChange={(e) =>
                                        setData('title', e.target.value)
                                    }
                                />
                                {errors.title && (
                                    <p className="text-sm text-destructive">
                                        {errors.title}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="order">Display Order</Label>
                                <Input
                                    id="order"
                                    type="number"
                                    value={data.order}
                                    onChange={(e) =>
                                        setData('order', parseInt(e.target.value))
                                    }
                                />
                                {errors.order && (
                                    <p className="text-sm text-destructive">
                                        {errors.order}
                                    </p>
                                )}
                            </div>

                            <div className="flex items-center space-x-2">
                                <Switch
                                    id="is_active"
                                    checked={data.is_active}
                                    onCheckedChange={(checked) =>
                                        setData('is_active', checked)
                                    }
                                />
                                <Label htmlFor="is_active">
                                    Display on homepage
                                </Label>
                            </div>

                            <div className="flex gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Saving...' : 'Save Changes'}
                                </Button>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={() => window.history.back()}
                                >
                                    Cancel
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}

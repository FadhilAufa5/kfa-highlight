import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { toast } from 'sonner';

export default function UploadCreate() {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        pdf_file: null as File | null,
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        post('/uploads', {
            onSuccess: () => {
                toast.success('PDF uploaded successfully!');
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
        <AppLayout
            breadcrumbs={[
                { label: 'PDF Uploads', href: '/uploads' },
                { label: 'Upload New' },
            ]}
        >
            <div className="container mx-auto max-w-2xl py-8">
                <Card>
                    <CardHeader>
                        <CardTitle>Upload New PDF</CardTitle>
                        <CardDescription>
                            Upload a PDF file to display on the homepage
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
                                    placeholder="Enter PDF title"
                                />
                                {errors.title && (
                                    <p className="text-sm text-destructive">
                                        {errors.title}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="pdf_file">PDF File</Label>
                                <Input
                                    id="pdf_file"
                                    type="file"
                                    accept="application/pdf"
                                    onChange={(e) =>
                                        setData(
                                            'pdf_file',
                                            e.target.files?.[0] || null,
                                        )
                                    }
                                />
                                {errors.pdf_file && (
                                    <p className="text-sm text-destructive">
                                        {errors.pdf_file}
                                    </p>
                                )}
                                <p className="text-sm text-muted-foreground">
                                    Maximum file size: 10MB
                                </p>
                            </div>

                            <div className="flex gap-4">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Uploading...' : 'Upload PDF'}
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

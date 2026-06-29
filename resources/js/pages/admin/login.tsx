import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

import { MobileLayout } from '@/components/layouts/mobile-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

export default function AdminLogin() {
    const { data, setData, post, processing, errors } = useForm({
        password: '',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post('/admin/login');
    };

    return (
        <>
            <Head title="Admin login" />
            <MobileLayout
                title="Admin console"
                subtitle="Enter the admin password to manage users, XP, and levels."
            >
                <form
                    onSubmit={submit}
                    className="flex flex-1 flex-col gap-5"
                >
                    <Input
                        name="password"
                        type="password"
                        label="Admin password"
                        autoComplete="current-password"
                        value={data.password}
                        onChange={(event) =>
                            setData('password', event.target.value)
                        }
                        error={errors.password}
                    />

                    <div className="mt-auto flex flex-col gap-3 pt-6">
                        <Button type="submit" fullWidth disabled={processing}>
                            {processing ? 'Signing in…' : 'Enter admin console'}
                        </Button>
                    </div>
                </form>
            </MobileLayout>
        </>
    );
}

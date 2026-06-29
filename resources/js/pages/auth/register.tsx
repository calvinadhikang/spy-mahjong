import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

import { MobileLayout } from '@/components/layouts/mobile-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({
        username: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post('/register');
    };

    return (
        <>
            <Head title="Register" />
            <MobileLayout
                title="Create account"
                subtitle="Pick a username and join the table."
            >
                <form
                    onSubmit={submit}
                    className="flex flex-1 flex-col gap-5"
                >
                    <Input
                        name="username"
                        label="Username"
                        autoComplete="username"
                        autoCapitalize="none"
                        autoCorrect="off"
                        spellCheck={false}
                        inputMode="text"
                        value={data.username}
                        onChange={(event) =>
                            setData(
                                'username',
                                event.target.value.toLowerCase(),
                            )
                        }
                        error={errors.username}
                    />

                    <Input
                        name="password"
                        type="password"
                        label="Password"
                        autoComplete="new-password"
                        value={data.password}
                        onChange={(event) =>
                            setData('password', event.target.value)
                        }
                        error={errors.password}
                    />

                    <Input
                        name="password_confirmation"
                        type="password"
                        label="Confirm password"
                        autoComplete="new-password"
                        value={data.password_confirmation}
                        onChange={(event) =>
                            setData(
                                'password_confirmation',
                                event.target.value,
                            )
                        }
                        error={errors.password_confirmation}
                    />

                    <div className="mt-auto flex flex-col gap-3 pt-6">
                        <Button type="submit" fullWidth disabled={processing}>
                            {processing ? 'Creating account…' : 'Create account'}
                        </Button>

                        <p className="text-center text-sm text-emerald-100/70">
                            Already have an account?{' '}
                            <Link
                                href="/login"
                                className="font-medium text-emerald-300 hover:text-emerald-200"
                            >
                                Log in
                            </Link>
                        </p>
                    </div>
                </form>
            </MobileLayout>
        </>
    );
}

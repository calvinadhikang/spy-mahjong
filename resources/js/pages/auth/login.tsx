import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

import { MobileLayout } from '@/components/layouts/mobile-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({
        username: '',
        password: '',
        remember: false,
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post('/login');
    };

    return (
        <>
            <Head title="Log in" />
            <MobileLayout
                title="Welcome back"
                subtitle="Sign in to join the table."
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
                            setData('username', event.target.value)
                        }
                        error={errors.username}
                    />

                    <Input
                        name="password"
                        type="password"
                        label="Password"
                        autoComplete="current-password"
                        value={data.password}
                        onChange={(event) =>
                            setData('password', event.target.value)
                        }
                        error={errors.password}
                    />

                    <label className="flex min-h-11 items-center gap-3 text-sm text-emerald-100/80">
                        <input
                            type="checkbox"
                            checked={data.remember}
                            onChange={(event) =>
                                setData('remember', event.target.checked)
                            }
                            className="size-5 rounded border-white/20 bg-white/5 text-emerald-500 focus:ring-emerald-400/30"
                        />
                        Keep me signed in
                    </label>

                    <div className="mt-auto flex flex-col gap-3 pt-6">
                        <Button type="submit" fullWidth disabled={processing}>
                            {processing ? 'Signing in…' : 'Sign in'}
                        </Button>

                        <p className="text-center text-sm text-emerald-100/70">
                            No account yet?{' '}
                            <Link
                                href="/register"
                                className="font-medium text-emerald-300 hover:text-emerald-200"
                            >
                                Register
                            </Link>
                        </p>
                    </div>
                </form>
            </MobileLayout>
        </>
    );
}

import { Head, Link, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';

import { MobileLayout } from '@/components/layouts/mobile-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useActiveSessionBlock } from '@/hooks/use-active-session-block';

type CreateSessionProps = {
    showActiveSessionBlock?: boolean;
};

export default function CreateSession({
    showActiveSessionBlock = false,
}: CreateSessionProps) {
    const { modal } = useActiveSessionBlock({
        initiallyOpen: showActiveSessionBlock,
    });

    const { data, setData, post, processing, errors } = useForm({
        name: '',
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        post('/sessions');
    };

    return (
        <>
            <Head title="Create session" />
            {modal}
            <MobileLayout
                title="New session"
                subtitle="Name your table, then invite up to 3 players."
            >
                <form
                    onSubmit={submit}
                    className="flex flex-1 flex-col gap-5"
                >
                    <Input
                        name="name"
                        label="Session name"
                        placeholder="Friday night spy game"
                        value={data.name}
                        onChange={(event) =>
                            setData('name', event.target.value)
                        }
                        error={errors.name}
                    />

                    <p className="rounded-xl border border-white/10 bg-white/5 p-4 text-sm text-emerald-100/70">
                        You will be the room master. After creating the
                        session, you can search and add players before
                        starting the game.
                    </p>

                    <div className="mt-auto flex flex-col gap-3 pt-6">
                        <Button type="submit" fullWidth disabled={processing}>
                            {processing ? 'Creating…' : 'Create session'}
                        </Button>

                        <Link
                            href="/user"
                            className="inline-flex min-h-11 items-center justify-center text-sm font-medium text-emerald-200/80"
                        >
                            Back to my table
                        </Link>
                    </div>
                </form>
            </MobileLayout>
        </>
    );
}

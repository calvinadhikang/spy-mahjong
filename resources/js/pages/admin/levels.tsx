import { Head, router, useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { useState } from 'react';

import { AdminLayout } from '@/components/layouts/admin-layout';
import { Button } from '@/components/ui/button';
import { ConfirmModal } from '@/components/ui/confirm-modal';
import { Input } from '@/components/ui/input';
import type { Level } from '@/types';

type LevelsProps = {
    levels: Level[];
};

export default function AdminLevels({ levels }: LevelsProps) {
    const { flash } = usePage<{
        flash?: { level_saved?: boolean; level_deleted?: boolean };
    }>().props;

    const [editingId, setEditingId] = useState<number | null>(null);
    const [levelToDelete, setLevelToDelete] = useState<Level | null>(null);

    const createForm = useForm({
        name: '',
        min_xp: 0,
    });

    const submitCreate = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        createForm.post('/admin/levels', {
            onSuccess: () => {
                createForm.reset();
            },
        });
    };

    return (
        <>
            <Head title="Admin · Levels" />
            <ConfirmModal
                open={levelToDelete !== null}
                title="Delete level?"
                description={
                    levelToDelete ? (
                        <>
                            Delete level{' '}
                            <span className="font-semibold text-white">
                                {levelToDelete.name}
                            </span>
                            ? This cannot be undone.
                        </>
                    ) : (
                        ''
                    )
                }
                confirmLabel="Delete level"
                destructive
                onConfirm={() => {
                    if (!levelToDelete) {
                        return;
                    }

                    router.delete(`/admin/levels/${levelToDelete.id}`, {
                        onFinish: () => setLevelToDelete(null),
                    });
                }}
                onClose={() => setLevelToDelete(null)}
            />
            <AdminLayout
                title="Levels"
                subtitle="Define level names and the minimum XP required to reach each."
            >
                <div className="flex flex-1 flex-col gap-6">
                    {(flash?.level_saved || flash?.level_deleted) && (
                        <p className="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">
                            {flash.level_deleted
                                ? 'Level deleted.'
                                : 'Levels updated.'}
                        </p>
                    )}

                    <form
                        onSubmit={submitCreate}
                        className="space-y-4 rounded-2xl border border-white/10 bg-white/5 p-4"
                    >
                        <h2 className="text-sm font-semibold text-white">
                            Add level
                        </h2>
                        <Input
                            name="name"
                            label="Level name"
                            value={createForm.data.name}
                            onChange={(event) =>
                                createForm.setData('name', event.target.value)
                            }
                            error={createForm.errors.name}
                        />
                        <Input
                            name="min_xp"
                            type="number"
                            min={0}
                            label="Minimum XP"
                            value={createForm.data.min_xp}
                            onChange={(event) =>
                                createForm.setData(
                                    'min_xp',
                                    Number(event.target.value),
                                )
                            }
                            error={createForm.errors.min_xp}
                        />
                        <Button
                            type="submit"
                            fullWidth
                            disabled={createForm.processing}
                        >
                            {createForm.processing
                                ? 'Adding…'
                                : 'Add level'}
                        </Button>
                    </form>

                    {levels.length === 0 ? (
                        <p className="rounded-2xl border border-dashed border-white/15 bg-white/5 p-6 text-center text-sm text-emerald-100/60">
                            No levels yet. Add your first level above.
                        </p>
                    ) : (
                        <ul className="space-y-3">
                            {levels.map((level) =>
                                editingId === level.id ? (
                                    <LevelEditForm
                                        key={level.id}
                                        level={level}
                                        onCancel={() => setEditingId(null)}
                                        onSaved={() => setEditingId(null)}
                                    />
                                ) : (
                                    <li
                                        key={level.id}
                                        className="rounded-2xl border border-white/10 bg-white/5 p-4"
                                    >
                                        <div className="flex items-start justify-between gap-3">
                                            <div>
                                                <h3 className="font-semibold text-white">
                                                    {level.name}
                                                </h3>
                                                <p className="mt-1 text-sm text-emerald-100/60">
                                                    {level.min_xp} XP minimum
                                                </p>
                                            </div>
                                            <div className="flex shrink-0 gap-2">
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setEditingId(level.id)
                                                    }
                                                    className="text-sm font-medium text-emerald-300 hover:text-emerald-200"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        setLevelToDelete(level)
                                                    }
                                                    className="text-sm font-medium text-red-300 hover:text-red-200"
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                ),
                            )}
                        </ul>
                    )}
                </div>
            </AdminLayout>
        </>
    );
}

function LevelEditForm({
    level,
    onCancel,
    onSaved,
}: {
    level: Level;
    onCancel: () => void;
    onSaved: () => void;
}) {
    const form = useForm({
        name: level.name,
        min_xp: level.min_xp,
    });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        form.put(`/admin/levels/${level.id}`, {
            onSuccess: onSaved,
        });
    };

    return (
        <li className="rounded-2xl border border-emerald-400/20 bg-emerald-400/5 p-4">
            <form onSubmit={submit} className="space-y-4">
                <Input
                    name="name"
                    label="Level name"
                    value={form.data.name}
                    onChange={(event) =>
                        form.setData('name', event.target.value)
                    }
                    error={form.errors.name}
                />
                <Input
                    name="min_xp"
                    type="number"
                    min={0}
                    label="Minimum XP"
                    value={form.data.min_xp}
                    onChange={(event) =>
                        form.setData('min_xp', Number(event.target.value))
                    }
                    error={form.errors.min_xp}
                />
                <div className="flex gap-2">
                    <Button type="submit" disabled={form.processing}>
                        {form.processing ? 'Saving…' : 'Save'}
                    </Button>
                    <Button
                        type="button"
                        variant="secondary"
                        onClick={onCancel}
                    >
                        Cancel
                    </Button>
                </div>
            </form>
        </li>
    );
}

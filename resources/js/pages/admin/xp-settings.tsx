import { Head, useForm, usePage } from '@inertiajs/react';
import type { FormEvent } from 'react';

import { AdminLayout } from '@/components/layouts/admin-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import type { XpRewardSettings } from '@/types';

type XpSettingsProps = {
    settings: XpRewardSettings;
};

export default function AdminXpSettings({ settings }: XpSettingsProps) {
    const { flash } = usePage<{
        flash?: { xp_settings_updated?: boolean };
    }>().props;

    const { data, setData, put, processing, errors, recentlySuccessful } =
        useForm({
            first_place_xp: settings.first_place_xp,
            second_place_xp: settings.second_place_xp,
            third_place_xp: settings.third_place_xp,
            fourth_place_xp: settings.fourth_place_xp,
            loss_xp: settings.loss_xp,
        });

    const submit = (event: FormEvent<HTMLFormElement>) => {
        event.preventDefault();
        put('/admin/xp-settings');
    };

    return (
        <>
            <Head title="Admin · XP settings" />
            <AdminLayout
                title="XP settings"
                subtitle="Configure how much XP players earn by placement or on a loss."
            >
                <form onSubmit={submit} className="flex flex-1 flex-col gap-5">
                    {(recentlySuccessful || flash?.xp_settings_updated) && (
                        <p className="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">
                            XP settings saved.
                        </p>
                    )}

                    <Input
                        name="first_place_xp"
                        type="number"
                        label="1st place XP"
                        value={data.first_place_xp}
                        onChange={(event) =>
                            setData(
                                'first_place_xp',
                                Number(event.target.value),
                            )
                        }
                        error={errors.first_place_xp}
                    />

                    <Input
                        name="second_place_xp"
                        type="number"
                        label="2nd place XP"
                        value={data.second_place_xp}
                        onChange={(event) =>
                            setData(
                                'second_place_xp',
                                Number(event.target.value),
                            )
                        }
                        error={errors.second_place_xp}
                    />

                    <Input
                        name="third_place_xp"
                        type="number"
                        label="3rd place XP"
                        value={data.third_place_xp}
                        onChange={(event) =>
                            setData(
                                'third_place_xp',
                                Number(event.target.value),
                            )
                        }
                        error={errors.third_place_xp}
                    />

                    <Input
                        name="fourth_place_xp"
                        type="number"
                        label="4th place XP"
                        value={data.fourth_place_xp}
                        onChange={(event) =>
                            setData(
                                'fourth_place_xp',
                                Number(event.target.value),
                            )
                        }
                        error={errors.fourth_place_xp}
                    />

                    <div className="space-y-2">
                        <Input
                            name="loss_xp"
                            type="number"
                            label="Loss XP"
                            value={data.loss_xp}
                            onChange={(event) =>
                                setData('loss_xp', Number(event.target.value))
                            }
                            error={errors.loss_xp}
                        />
                        <p className="text-xs text-emerald-100/50">
                            Exact XP applied on a loss outcome. Use{' '}
                            <span className="font-medium text-emerald-100/70">
                                0
                            </span>{' '}
                            for no change, a negative number to deduct, or a
                            positive number to still reward.
                        </p>
                    </div>

                    <div className="mt-auto pt-4">
                        <Button type="submit" fullWidth disabled={processing}>
                            {processing ? 'Saving…' : 'Save XP settings'}
                        </Button>
                    </div>
                </form>
            </AdminLayout>
        </>
    );
}

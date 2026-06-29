import { Head, router, usePage } from '@inertiajs/react';

import { AdminLayout } from '@/components/layouts/admin-layout';
import { Button } from '@/components/ui/button';
import type { AdminUser, SharedData } from '@/types';

type AdminUsersProps = {
    users: AdminUser[];
};

export default function AdminUsers({ users }: AdminUsersProps) {
    const { flash } = usePage<
        SharedData & { flash?: { admin_user_updated?: boolean } }
    >().props;

    const toggleAdmin = (user: AdminUser) => {
        router.put(`/admin/users/${user.id}`, {
            is_admin: !user.is_admin,
        });
    };

    return (
        <>
            <Head title="Admin · Users" />
            <AdminLayout
                title="Users"
                subtitle="Grant or revoke admin access for registered players."
            >
                <div className="flex flex-1 flex-col gap-5">
                    {flash?.admin_user_updated ? (
                        <p className="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">
                            User admin status updated.
                        </p>
                    ) : null}

                    <ul className="overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                        {users.map((user) => (
                            <li
                                key={user.id}
                                className="flex min-h-14 items-center justify-between gap-3 border-b border-white/5 px-4 last:border-b-0"
                            >
                                <div>
                                    <p className="font-medium text-white">
                                        {user.username}
                                    </p>
                                    <p className="text-xs text-emerald-100/60">
                                        {user.is_admin ? 'Admin' : 'Player'}
                                    </p>
                                </div>
                                <Button
                                    variant={
                                        user.is_admin ? 'secondary' : 'primary'
                                    }
                                    onClick={() => toggleAdmin(user)}
                                >
                                    {user.is_admin
                                        ? 'Remove admin'
                                        : 'Make admin'}
                                </Button>
                            </li>
                        ))}
                    </ul>

                    {users.length === 0 ? (
                        <p className="text-center text-sm text-emerald-100/60">
                            No users registered yet.
                        </p>
                    ) : null}
                </div>
            </AdminLayout>
        </>
    );
}

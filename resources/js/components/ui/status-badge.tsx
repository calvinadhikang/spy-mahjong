import { cn } from '@/lib/utils';
import type { GameSession } from '@/types';

type StatusBadgeProps = {
    status: GameSession['status'];
    label: string;
    className?: string;
};

export function StatusBadge({ status, label, className }: StatusBadgeProps) {
    return (
        <span
            className={cn(
                'inline-flex min-h-8 items-center rounded-full px-3 text-xs font-semibold uppercase tracking-wide',
                status === 'waiting' &&
                    'bg-amber-400/15 text-amber-200 ring-1 ring-amber-300/30',
                status === 'in_progress' &&
                    'bg-emerald-400/15 text-emerald-200 ring-1 ring-emerald-300/30',
                status === 'finishing' &&
                    'bg-sky-400/15 text-sky-200 ring-1 ring-sky-300/30',
                status === 'completed' &&
                    'bg-white/10 text-emerald-100/70 ring-1 ring-white/15',
                className,
            )}
        >
            {label}
        </span>
    );
}

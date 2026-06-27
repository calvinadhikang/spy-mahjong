import type { ButtonHTMLAttributes, ReactNode } from 'react';

import { cn } from '@/lib/utils';

type ButtonProps = ButtonHTMLAttributes<HTMLButtonElement> & {
    children: ReactNode;
    variant?: 'primary' | 'secondary' | 'ghost';
    fullWidth?: boolean;
};

export function Button({
    children,
    className,
    variant = 'primary',
    fullWidth = false,
    type = 'button',
    ...props
}: ButtonProps) {
    return (
        <button
            type={type}
            className={cn(
                'inline-flex min-h-12 items-center justify-center rounded-xl px-5 text-base font-semibold transition active:scale-[0.98] disabled:pointer-events-none disabled:opacity-60',
                fullWidth && 'w-full',
                variant === 'primary' &&
                    'bg-emerald-500 text-white shadow-lg shadow-emerald-950/30 hover:bg-emerald-400',
                variant === 'secondary' &&
                    'border border-white/15 bg-white/10 text-white hover:bg-white/15',
                variant === 'ghost' &&
                    'text-emerald-200 hover:bg-white/5',
                className,
            )}
            {...props}
        >
            {children}
        </button>
    );
}

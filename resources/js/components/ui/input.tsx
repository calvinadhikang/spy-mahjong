import type { InputHTMLAttributes } from 'react';

import { cn } from '@/lib/utils';

type InputProps = InputHTMLAttributes<HTMLInputElement> & {
    label: string;
    error?: string;
};

export function Input({ label, error, className, id, ...props }: InputProps) {
    const inputId = id ?? props.name;

    return (
        <div className="space-y-2">
            <label
                htmlFor={inputId}
                className="block text-sm font-medium text-emerald-100/90"
            >
                {label}
            </label>
            <input
                id={inputId}
                className={cn(
                    'min-h-12 w-full rounded-xl border border-white/10 bg-white/5 px-4 text-base text-white placeholder:text-white/35 outline-none transition focus:border-emerald-400/60 focus:ring-2 focus:ring-emerald-400/20',
                    error && 'border-red-400/70 focus:border-red-400 focus:ring-red-400/20',
                    className,
                )}
                {...props}
            />
            {error ? (
                <p className="text-sm text-red-300">{error}</p>
            ) : null}
        </div>
    );
}

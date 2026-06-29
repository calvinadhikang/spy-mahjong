import type { ReactNode } from 'react';

import { Button } from '@/components/ui/button';

type ConfirmModalProps = {
    open: boolean;
    title: string;
    description: ReactNode;
    confirmLabel?: string;
    cancelLabel?: string;
    destructive?: boolean;
    loading?: boolean;
    onConfirm: () => void;
    onClose: () => void;
};

export function ConfirmModal({
    open,
    title,
    description,
    confirmLabel = 'Confirm',
    cancelLabel = 'Cancel',
    destructive = false,
    loading = false,
    onConfirm,
    onClose,
}: ConfirmModalProps) {
    if (!open) {
        return null;
    }

    return (
        <div className="fixed inset-0 z-50 flex items-end justify-center p-5 sm:items-center">
            <button
                type="button"
                aria-label="Close dialog"
                className="absolute inset-0 animate-modal-backdrop-in bg-emerald-950/80 backdrop-blur-sm"
                onClick={onClose}
            />

            <div
                role="dialog"
                aria-modal="true"
                aria-labelledby="confirm-modal-title"
                className="relative w-full max-w-md animate-modal-panel-in rounded-2xl border border-white/10 bg-emerald-900 p-6 shadow-2xl shadow-emerald-950/50"
            >
                <h2
                    id="confirm-modal-title"
                    className="text-xl font-bold text-white"
                >
                    {title}
                </h2>

                <div className="mt-3 text-sm leading-relaxed text-emerald-100/70">
                    {description}
                </div>

                <div className="mt-6 flex flex-col gap-3">
                    <Button
                        fullWidth
                        disabled={loading}
                        className={
                            destructive
                                ? 'bg-red-500 shadow-lg shadow-red-950/30 hover:bg-red-400'
                                : undefined
                        }
                        onClick={onConfirm}
                    >
                        {loading ? 'Please wait…' : confirmLabel}
                    </Button>
                    <Button
                        fullWidth
                        variant="secondary"
                        disabled={loading}
                        onClick={onClose}
                    >
                        {cancelLabel}
                    </Button>
                </div>
            </div>
        </div>
    );
}

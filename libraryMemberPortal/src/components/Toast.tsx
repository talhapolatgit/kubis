import { AlertCircle, CheckCircle2, X } from 'lucide-react'
import { useEffect, useRef } from 'react'

const TOAST_DURATION_MS = 4000

interface ToastProps {
  message: string
  type?: 'success' | 'error'
  onClose: () => void
}

export function Toast({ message, type = 'success', onClose }: ToastProps) {
  const onCloseRef = useRef(onClose)
  onCloseRef.current = onClose

  useEffect(() => {
    const timer = setTimeout(() => onCloseRef.current(), TOAST_DURATION_MS)
    return () => clearTimeout(timer)
  }, [message, type])

  const isSuccess = type === 'success'

  return (
    <div
      className="pointer-events-none fixed inset-x-0 top-5 z-[100] flex justify-center px-4 safe-top md:top-6"
      role="alert"
      aria-live="polite"
    >
      <div className="pointer-events-auto w-full max-w-sm animate-toast-in">
        <div className="overflow-hidden rounded-2xl border border-slate-200/80 bg-white/95 shadow-2xl shadow-slate-900/10 backdrop-blur-md">
          <div className="flex items-start gap-3 px-4 py-3.5">
            <div
              className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${
                isSuccess
                  ? 'bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100'
                  : 'bg-red-50 text-red-600 ring-1 ring-red-100'
              }`}
            >
              {isSuccess ? (
                <CheckCircle2 className="h-5 w-5" strokeWidth={2.25} />
              ) : (
                <AlertCircle className="h-5 w-5" strokeWidth={2.25} />
              )}
            </div>

            <div className="min-w-0 flex-1 pt-0.5">
              <p className="text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                {isSuccess ? 'İşlem başarılı' : 'Bir sorun oluştu'}
              </p>
              <p className="mt-0.5 text-sm font-medium leading-snug text-slate-800">
                {message}
              </p>
            </div>

            <button
              type="button"
              onClick={onClose}
              className="shrink-0 rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600"
              aria-label="Kapat"
            >
              <X className="h-4 w-4" />
            </button>
          </div>

          <div className="h-1 bg-slate-100">
            <div
              className={`h-full origin-left animate-toast-progress ${
                isSuccess ? 'bg-emerald-500' : 'bg-red-500'
              }`}
              style={{ animationDuration: `${TOAST_DURATION_MS}ms` }}
            />
          </div>
        </div>
      </div>
    </div>
  )
}

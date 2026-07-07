import { Loader2 } from 'lucide-react'

interface LoadingSpinnerProps {
  label?: string
  fullScreen?: boolean
}

export function LoadingSpinner({ label = 'Yükleniyor...', fullScreen }: LoadingSpinnerProps) {
  const content = (
    <div className="flex flex-col items-center justify-center gap-3">
      <Loader2 className="h-8 w-8 animate-spin text-brand-500" />
      <p className="text-sm text-slate-500">{label}</p>
    </div>
  )

  if (fullScreen) {
    return (
      <div className="flex min-h-[50vh] items-center justify-center">{content}</div>
    )
  }

  return content
}

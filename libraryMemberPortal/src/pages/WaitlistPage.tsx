import { BookOpen, Clock } from 'lucide-react'
import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { api, ApiError } from '../api/client'
import type { WaitingRow } from '../api/types'
import { EmptyState } from '../components/EmptyState'
import { LoadingSpinner } from '../components/LoadingSpinner'
import { StatusBadge } from '../components/StatusBadge'
import { Toast } from '../components/Toast'
import { useBookSheet } from '../context/BookSheetContext'
import { coverUrl, formatDate } from '../utils/formatters'

function libraryName(kitap: NonNullable<WaitingRow['kitap']>): string | null {
  return kitap.kutuphane_adi || kitap.kutuphane?.title || null
}

export function WaitlistPage() {
  const { openBook } = useBookSheet()
  const [waitings, setWaitings] = useState<WaitingRow[]>([])
  const [loading, setLoading] = useState(true)
  const [removingId, setRemovingId] = useState<number | null>(null)
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null)

  const loadWaitings = useCallback(async () => {
    setLoading(true)
    try {
      const res = await api.waitings()
      if (res.data) setWaitings(res.data.rows)
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    loadWaitings()
  }, [loadWaitings])

  const handleRemove = async (katalogId: number) => {
    setRemovingId(katalogId)
    try {
      await api.removeWaiting(katalogId)
      setWaitings((prev) => prev.filter((w) => w.kitap?.id !== katalogId))
      setToast({ message: 'Bekleme listesinden çıkarıldı.', type: 'success' })
    } catch (err) {
      setToast({
        message: err instanceof ApiError ? err.message : 'İşlem başarısız.',
        type: 'error',
      })
    } finally {
      setRemovingId(null)
    }
  }

  if (loading) return <LoadingSpinner fullScreen />

  return (
    <div className="animate-fade-in">
      {toast && (
        <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />
      )}

      <div className="mb-4">
        <h2 className="text-xl font-bold text-slate-800 md:text-2xl">Bekleme Listem</h2>
        <p className="mt-1 text-sm text-slate-500">{waitings.length} kitap</p>
      </div>

      {waitings.length === 0 ? (
        <EmptyState
          icon={Clock}
          title="Bekleme listeniz boş"
          description="Müsait olmayan kitapları bekleme listesine ekleyerek sıra geldiğinde haberdar olabilirsiniz."
          action={
            <Link
              to="/"
              className="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/20"
            >
              Kitapları Keşfet
            </Link>
          }
        />
      ) : (
        <div className="space-y-3">
          {waitings.map((waiting) => {
            const kitap = waiting.kitap
            if (!kitap) return null
            const cover = coverUrl(kitap.kapak)
            const lib = libraryName(kitap)

            return (
              <div
                key={waiting.islem_id}
                className="flex gap-3 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-100"
              >
                <button
                  type="button"
                  onClick={() => openBook(kitap.id)}
                  className="relative h-24 w-[72px] shrink-0 overflow-hidden rounded-xl bg-brand-50"
                >
                  {cover ? (
                    <img
                      src={cover}
                      alt={kitap.eser_adi}
                      className="h-full w-full object-cover"
                    />
                  ) : (
                    <div className="flex h-full w-full items-center justify-center">
                      <BookOpen className="h-8 w-8 text-brand-200" />
                    </div>
                  )}
                </button>

                <div className="flex min-w-0 flex-1 flex-col justify-between py-0.5">
                  <div>
                    <button type="button" onClick={() => openBook(kitap.id)}>
                      <h3 className="line-clamp-2 text-left text-sm font-semibold text-slate-800 hover:text-brand-600">
                        {kitap.eser_adi}
                      </h3>
                    </button>
                    <p className="mt-1 text-xs text-slate-500">{kitap.yazar_adi}</p>
                    <p className="mt-1 text-[11px] text-slate-400">
                      Eklendi: {formatDate(waiting.ekleme_tarihi)}
                    </p>
                    {kitap.tahmini_musaitlik && (
                      <p className="mt-1 text-[11px] text-amber-600">
                        Tahmini müsaitlik: {formatDate(kitap.tahmini_musaitlik)}
                      </p>
                    )}
                  </div>
                  <div className="mt-2 flex items-center justify-between gap-2">
                    <StatusBadge status={kitap.durum} />
                    {lib && (
                      <span className="truncate text-[11px] text-slate-400">{lib}</span>
                    )}
                    <button
                      type="button"
                      disabled={removingId === kitap.id}
                      onClick={() => handleRemove(kitap.id)}
                      className="shrink-0 text-xs font-medium text-red-500 hover:text-red-600 disabled:opacity-50"
                    >
                      {removingId === kitap.id ? 'Çıkarılıyor...' : 'Çıkar'}
                    </button>
                  </div>
                </div>
              </div>
            )
          })}
        </div>
      )}
    </div>
  )
}

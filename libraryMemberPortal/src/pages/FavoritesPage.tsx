import { BookOpen, Heart } from 'lucide-react'
import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { api, ApiError } from '../api/client'
import type { FavoriteRow } from '../api/types'
import { EmptyState } from '../components/EmptyState'
import { LoadingSpinner } from '../components/LoadingSpinner'
import { StatusBadge } from '../components/StatusBadge'
import { Toast } from '../components/Toast'
import { useBookSheet } from '../context/BookSheetContext'
import { coverUrl, formatDate } from '../utils/formatters'

export function FavoritesPage() {
  const { openBook } = useBookSheet()
  const [favorites, setFavorites] = useState<FavoriteRow[]>([])
  const [loading, setLoading] = useState(true)
  const [removingId, setRemovingId] = useState<number | null>(null)
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null)

  const loadFavorites = useCallback(async () => {
    setLoading(true)
    try {
      const res = await api.favorites()
      if (res.data) setFavorites(res.data.rows)
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    loadFavorites()
  }, [loadFavorites])

  const handleRemove = async (katalogId: number) => {
    setRemovingId(katalogId)
    try {
      await api.removeFavorite(katalogId)
      setFavorites((prev) => prev.filter((f) => f.kitap?.id !== katalogId))
      setToast({ message: 'Favorilerden çıkarıldı.', type: 'success' })
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
        <h2 className="text-xl font-bold text-slate-800 md:text-2xl">Favorilerim</h2>
        <p className="mt-1 text-sm text-slate-500">{favorites.length} kitap</p>
      </div>

      {favorites.length === 0 ? (
        <EmptyState
          icon={Heart}
          title="Henüz favori kitabınız yok"
          description="Beğendiğiniz kitapları favorilere ekleyerek buradan takip edebilirsiniz."
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
          {favorites.map((fav) => {
            const kitap = fav.kitap
            if (!kitap) return null
            const cover = coverUrl(kitap.kapak)

            return (
              <div
                key={fav.islem_id}
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
                      Eklendi: {formatDate(fav.ekleme_tarihi)}
                    </p>
                  </div>
                  <div className="mt-2 flex items-center justify-between">
                    <StatusBadge status={kitap.durum} />
                    <button
                      type="button"
                      disabled={removingId === kitap.id}
                      onClick={() => handleRemove(kitap.id)}
                      className="text-xs font-medium text-red-500 hover:text-red-600 disabled:opacity-50"
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

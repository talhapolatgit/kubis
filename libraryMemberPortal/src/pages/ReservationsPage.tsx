import { BookOpen, CalendarCheck } from 'lucide-react'
import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { api, ApiError } from '../api/client'
import type { ReservationRow } from '../api/types'
import { EmptyState } from '../components/EmptyState'
import { LoadingSpinner } from '../components/LoadingSpinner'
import { StatusBadge } from '../components/StatusBadge'
import { Toast } from '../components/Toast'
import { useBookSheet } from '../context/BookSheetContext'
import { coverUrl, formatDateTime } from '../utils/formatters'

export function ReservationsPage() {
  const { openBook } = useBookSheet()
  const [reservations, setReservations] = useState<ReservationRow[]>([])
  const [loading, setLoading] = useState(true)
  const [cancellingId, setCancellingId] = useState<number | null>(null)
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null)

  const loadReservations = useCallback(async () => {
    setLoading(true)
    try {
      const res = await api.reservations()
      if (res.data) setReservations(res.data.rows)
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    loadReservations()
  }, [loadReservations])

  const handleCancel = async (katalogId: number) => {
    setCancellingId(katalogId)
    try {
      await api.cancelReservation(katalogId)
      await loadReservations()
      setToast({ message: 'Rezervasyon iptal edildi.', type: 'success' })
    } catch (err) {
      setToast({
        message: err instanceof ApiError ? err.message : 'İptal işlemi başarısız.',
        type: 'error',
      })
    } finally {
      setCancellingId(null)
    }
  }

  const isActive = (r: ReservationRow) =>
    r.iptalMi === 'false' && r.oduncAldiMi === 'false' && r.suresiDolduMu === 'false'

  if (loading) return <LoadingSpinner fullScreen />

  const activeCount = reservations.filter(isActive).length

  return (
    <div className="animate-fade-in">
      {toast && (
        <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />
      )}

      <div className="mb-4">
        <h2 className="text-xl font-bold text-slate-800 md:text-2xl">Rezervasyonlarım</h2>
        <p className="mt-1 text-sm text-slate-500">
          {activeCount} aktif rezervasyon
        </p>
      </div>

      {reservations.length === 0 ? (
        <EmptyState
          icon={CalendarCheck}
          title="Rezervasyonunuz yok"
          description="Müsait kitapları rezerve ederek 24 saat içinde kütüphaneden alabilirsiniz."
          action={
            <Link
              to="/"
              className="rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/20"
            >
              Kitap Ara
            </Link>
          }
        />
      ) : (
        <div className="space-y-3">
          {reservations.map((rez) => {
            const kitap = rez.kitap
            if (!kitap) return null
            const cover = coverUrl(kitap.kapak)
            const active = isActive(rez)

            return (
              <div
                key={rez.islem_id}
                className={`overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ${
                  active ? 'ring-brand-200' : 'ring-slate-100 opacity-75'
                }`}
              >
                {active && (
                  <div className="bg-brand-600 px-4 py-1.5 text-center text-[11px] font-semibold text-white">
                    Aktif Rezervasyon
                  </div>
                )}

                <div className="flex gap-3 p-3">
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

                  <div className="flex min-w-0 flex-1 flex-col">
                    <button type="button" onClick={() => openBook(kitap.id)}>
                      <h3 className="line-clamp-2 text-left text-sm font-semibold text-slate-800 hover:text-brand-600">
                        {kitap.eser_adi}
                      </h3>
                    </button>
                    <p className="mt-1 text-xs text-slate-500">{kitap.yazar_adi}</p>

                    <div className="mt-2 space-y-1 text-[11px] text-slate-500">
                      <p>Başlangıç: {formatDateTime(rez.rezerve_baslangic)}</p>
                      <p>Bitiş: {formatDateTime(rez.rezerve_bitis)}</p>
                    </div>

                    <div className="mt-2 flex items-center justify-between">
                      <StatusBadge status={kitap.durum} />
                      {active && (
                        <button
                          type="button"
                          disabled={cancellingId === kitap.id}
                          onClick={() => handleCancel(kitap.id)}
                          className="rounded-lg bg-red-50 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-100 disabled:opacity-50"
                        >
                          {cancellingId === kitap.id ? 'İptal...' : 'İptal Et'}
                        </button>
                      )}
                      {!active && rez.iptalMi === 'true' && (
                        <span className="text-xs text-slate-400">İptal edildi</span>
                      )}
                      {rez.oduncAldiMi === 'true' && (
                        <span className="text-xs text-emerald-600">Ödünç alındı</span>
                      )}
                      {rez.suresiDolduMu === 'true' && rez.iptalMi === 'false' && (
                        <span className="text-xs text-amber-600">Süre doldu</span>
                      )}
                    </div>
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

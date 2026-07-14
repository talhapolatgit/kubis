import {
  Barcode,
  BookOpen,
  Briefcase,
  Clock,
  Heart,
  MapPin,
  X,
  XCircle,
} from 'lucide-react'
import { useCallback, useEffect, useState } from 'react'
import { api, ApiError } from '../api/client'
import type { BookDetail } from '../api/types'
import { useBookSheet } from '../context/BookSheetContext'
import { coverUrl } from '../utils/formatters'
import { StatusBadge } from './StatusBadge'
import { Toast } from './Toast'

export function BookDetailSheet() {
  const { bookId, closeBook } = useBookSheet()
  const [book, setBook] = useState<BookDetail | null>(null)
  const [loading, setLoading] = useState(false)
  const [actionLoading, setActionLoading] = useState('')
  const [toast, setToast] = useState<{ message: string; type: 'success' | 'error' } | null>(null)
  const [visible, setVisible] = useState(false)

  const isOpen = bookId !== null

  const loadBook = useCallback(async () => {
    if (!bookId) return
    setLoading(true)
    setBook(null)
    try {
      const res = await api.bookDetail(bookId)
      if (res.data?.[0]) setBook(res.data[0])
    } catch {
      setToast({ message: 'Kitap bilgileri yüklenemedi.', type: 'error' })
    } finally {
      setLoading(false)
    }
  }, [bookId])

  useEffect(() => {
    if (isOpen) {
      setVisible(true)
      setToast(null)
      document.body.style.overflow = 'hidden'
      loadBook()
    } else {
      document.body.style.overflow = ''
      setToast(null)
      const timer = setTimeout(() => setVisible(false), 300)
      return () => clearTimeout(timer)
    }
    return () => {
      document.body.style.overflow = ''
    }
  }, [isOpen, loadBook])

  useEffect(() => {
    if (!isOpen) return
    const onKeyDown = (e: KeyboardEvent) => {
      if (e.key === 'Escape') closeBook()
    }
    window.addEventListener('keydown', onKeyDown)
    return () => window.removeEventListener('keydown', onKeyDown)
  }, [isOpen, closeBook])

  const handleAction = async (
    action: 'favorite-add' | 'favorite-remove' | 'reserve' | 'cancel-reserve' | 'waiting-add',
  ) => {
    if (!book) return
    setActionLoading(action)
    try {
      switch (action) {
        case 'favorite-add':
          await api.addFavorite(book.id)
          setBook({ ...book, favorimi: true })
          setToast({ message: 'Favorilere eklendi.', type: 'success' })
          break
        case 'favorite-remove':
          await api.removeFavorite(book.id)
          setBook({ ...book, favorimi: false })
          setToast({ message: 'Favorilerden çıkarıldı.', type: 'success' })
          break
        case 'reserve':
          await api.addReservation(book.id)
          setBook({ ...book, rezervemi: true, durum: 'Rezerve' })
          setToast({ message: 'Kitap başarıyla rezerve edildi!', type: 'success' })
          break
        case 'cancel-reserve':
          await api.cancelReservation(book.id)
          setBook({ ...book, rezervemi: false, durum: 'Rafta' })
          setToast({ message: 'Rezervasyon iptal edildi.', type: 'success' })
          break
        case 'waiting-add':
          await api.addWaiting(book.id)
          setBook({ ...book, beklememi: true })
          setToast({ message: 'Bekleme listesine eklendi.', type: 'success' })
          break
      }
    } catch (err) {
      setToast({
        message: err instanceof ApiError ? err.message : 'İşlem başarısız.',
        type: 'error',
      })
    } finally {
      setActionLoading('')
    }
  }

  if (!visible && !isOpen) return null

  const cover = book ? coverUrl(book.kapak) : null
  const canReserve = book?.durum === 'Rafta' && !book.rezerv_edilemez && !book.rezervemi
  const canCancelReserve = book?.rezervemi
  const canAddToWaitlist =
    book &&
    !canReserve &&
    !canCancelReserve &&
    !book.rezerv_edilemez &&
    !book.oduncmu &&
    (book.durum === 'Ödünç' || book.durum === 'Rezerve') &&
    !book.beklememi
  const isOnWaitlist =
    book &&
    !canReserve &&
    !canCancelReserve &&
    book.beklememi &&
    (book.durum === 'Ödünç' || book.durum === 'Rezerve')

  return (
    <>
      {toast && (
        <Toast message={toast.message} type={toast.type} onClose={() => setToast(null)} />
      )}

      <div
        className={`fixed inset-0 z-[60] transition-opacity duration-300 ${
          isOpen ? 'opacity-100' : 'pointer-events-none opacity-0'
        }`}
      >
        <button
          type="button"
          aria-label="Kapat"
          className="absolute inset-0 bg-black/40"
          onClick={closeBook}
        />

        <div
          className={`absolute bottom-0 left-0 right-0 mx-auto max-w-lg rounded-t-3xl bg-white shadow-2xl transition-transform duration-300 ease-out safe-bottom ${
            isOpen ? 'translate-y-0' : 'translate-y-full'
          }`}
        >
          <div className="flex justify-center pt-3">
            <div className="h-1 w-10 rounded-full bg-slate-200" />
          </div>

          <button
            type="button"
            onClick={closeBook}
            className="absolute right-4 top-4 flex h-8 w-8 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition-colors hover:bg-slate-200"
            aria-label="Kapat"
          >
            <X className="h-4 w-4" />
          </button>

          {loading ? (
            <SheetSkeleton />
          ) : !book ? (
            <div className="px-6 py-12 text-center">
              <p className="text-sm text-slate-500">Kitap bulunamadı.</p>
            </div>
          ) : (
            <>
              <div className="flex gap-4 px-5 pb-4 pt-2">
                <div className="h-36 w-[100px] shrink-0 overflow-hidden rounded-xl bg-brand-50 shadow-sm">
                  {cover ? (
                    <img src={cover} alt={book.eser_adi} className="h-full w-full object-cover" />
                  ) : (
                    <div className="flex h-full w-full items-center justify-center">
                      <BookOpen className="h-10 w-10 text-brand-200" />
                    </div>
                  )}
                </div>

                <div className="min-w-0 flex-1 pt-1 pr-8">
                  <StatusBadge status={book.durum} />
                  <h2 className="mt-2 text-lg font-bold leading-snug text-slate-900">
                    {book.eser_adi}
                  </h2>
                  <p className="mt-1 text-sm text-slate-500">{book.yazar_adi}</p>

                  <div className="mt-3 space-y-1.5">
                    {book.kutuphane_adi && (
                      <MetaRow icon={<MapPin className="h-3.5 w-3.5" />} text={book.kutuphane_adi} />
                    )}
                    {book.isbn_issn && (
                      <MetaRow icon={<Barcode className="h-3.5 w-3.5" />} text={book.isbn_issn} />
                    )}
                    {book.demirbas_no && (
                      <MetaRow
                        icon={<Briefcase className="h-3.5 w-3.5" />}
                        text={`Demirbaş: ${book.demirbas_no}`}
                      />
                    )}
                  </div>
                </div>
              </div>

              {book.aciklama && (
                <div className="border-t border-slate-100 px-5 py-3">
                  <p className="line-clamp-3 text-sm leading-relaxed text-slate-600">
                    {book.aciklama}
                  </p>
                </div>
              )}

              <div className="border-t border-slate-100 px-5 py-4">
                <div className="flex items-center gap-3">
                  <button
                    type="button"
                    disabled={!!actionLoading}
                    onClick={() =>
                      handleAction(book.favorimi ? 'favorite-remove' : 'favorite-add')
                    }
                    className={`flex h-12 w-12 shrink-0 items-center justify-center rounded-full border transition-all active:scale-95 disabled:opacity-50 ${
                      book.favorimi
                        ? 'border-red-200 bg-red-50 text-red-500'
                        : 'border-slate-200 bg-white text-slate-400 hover:border-red-200 hover:text-red-400'
                    }`}
                    aria-label={book.favorimi ? 'Favorilerden çıkar' : 'Favorilere ekle'}
                  >
                    <Heart className={`h-5 w-5 ${book.favorimi ? 'fill-current' : ''}`} />
                  </button>

                  {canReserve && (
                    <button
                      type="button"
                      disabled={!!actionLoading}
                      onClick={() => handleAction('reserve')}
                      className="flex flex-1 items-center justify-center rounded-full bg-emerald-500 py-3.5 text-sm font-semibold text-white shadow-lg shadow-emerald-500/25 transition-all hover:bg-emerald-600 active:scale-[0.98] disabled:opacity-50"
                    >
                      {actionLoading === 'reserve' ? 'Rezerve ediliyor...' : 'Rezerve Et'}
                    </button>
                  )}

                  {canCancelReserve && (
                    <button
                      type="button"
                      disabled={!!actionLoading}
                      onClick={() => handleAction('cancel-reserve')}
                      className="flex flex-1 items-center justify-center gap-2 rounded-full bg-red-500 py-3.5 text-sm font-semibold text-white transition-all hover:bg-red-600 active:scale-[0.98] disabled:opacity-50"
                    >
                      <XCircle className="h-4 w-4" />
                      {actionLoading === 'cancel-reserve' ? 'İptal ediliyor...' : 'Rezervasyonu İptal Et'}
                    </button>
                  )}

                  {canAddToWaitlist && (
                    <button
                      type="button"
                      disabled={!!actionLoading}
                      onClick={() => handleAction('waiting-add')}
                      className="flex flex-1 items-center justify-center gap-2 rounded-full bg-amber-500 py-3.5 text-sm font-semibold text-white shadow-lg shadow-amber-500/25 transition-all hover:bg-amber-600 active:scale-[0.98] disabled:opacity-50"
                    >
                      <Clock className="h-4 w-4" />
                      {actionLoading === 'waiting-add' ? 'Ekleniyor...' : 'Bekleme Listesine Ekle'}
                    </button>
                  )}

                  {isOnWaitlist && (
                    <div className="flex flex-1 items-center justify-center gap-2 rounded-full bg-amber-50 py-3.5 text-sm font-semibold text-amber-700 ring-1 ring-amber-200">
                      <Clock className="h-4 w-4" />
                      Bekleme listesinde
                    </div>
                  )}

                  {!canReserve && !canCancelReserve && !canAddToWaitlist && !isOnWaitlist && (
                    <div className="flex flex-1 items-center justify-center rounded-full bg-slate-100 py-3.5 text-sm font-medium text-slate-500">
                      {book.rezerv_edilemez ? 'Rezerve edilemez' : 'Şu an müsait değil'}
                    </div>
                  )}
                </div>
              </div>
            </>
          )}
        </div>
      </div>
    </>
  )
}

function MetaRow({ icon, text }: { icon: React.ReactNode; text: string }) {
  return (
    <p className="flex items-center gap-2 text-xs text-slate-500">
      <span className="shrink-0 text-slate-400">{icon}</span>
      <span className="truncate">{text}</span>
    </p>
  )
}

function SheetSkeleton() {
  return (
    <div className="flex gap-4 px-5 pb-6 pt-2">
      <div className="skeleton h-36 w-[100px] shrink-0 rounded-xl" />
      <div className="flex-1 space-y-3 pt-1">
        <div className="skeleton h-5 w-16 rounded-full" />
        <div className="skeleton h-5 w-3/4 rounded" />
        <div className="skeleton h-4 w-1/2 rounded" />
        <div className="skeleton mt-4 h-3 w-full rounded" />
        <div className="skeleton h-3 w-2/3 rounded" />
        <div className="skeleton h-3 w-1/2 rounded" />
      </div>
    </div>
  )
}

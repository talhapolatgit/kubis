import { BookCopy, BookOpen, Calendar, CheckCircle2, MapPin } from 'lucide-react'
import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../api/client'
import type { LoanRow } from '../api/types'
import { EmptyState } from '../components/EmptyState'
import { LoadingSpinner } from '../components/LoadingSpinner'
import { useBookSheet } from '../context/BookSheetContext'
import { coverUrl, formatDateShort } from '../utils/formatters'

function loanProgress(loan: LoanRow): number {
  if (!loan.odunc_tarihi || !loan.iade_tarihi_planlanan || loan.kalan_gun == null) return 0
  const start = new Date(loan.odunc_tarihi).getTime()
  const end = new Date(loan.iade_tarihi_planlanan).getTime()
  const totalDays = Math.max(1, Math.round((end - start) / (1000 * 60 * 60 * 24)))
  const elapsed = totalDays - loan.kalan_gun
  return Math.min(100, Math.max(0, (elapsed / totalDays) * 100))
}

function isReturned(loan: LoanRow) {
  return loan.statu === 'iade_edildi'
}

export function LoansPage() {
  const { openBook } = useBookSheet()
  const [loans, setLoans] = useState<LoanRow[]>([])
  const [loading, setLoading] = useState(true)

  const loadLoans = useCallback(async () => {
    setLoading(true)
    try {
      const res = await api.loans({ per_page: 50 })
      if (res.data) setLoans(res.data.rows)
    } finally {
      setLoading(false)
    }
  }, [])

  useEffect(() => {
    loadLoans()
  }, [loadLoans])

  if (loading) return <LoadingSpinner fullScreen />

  const activeCount = loans.filter((l) => l.statu === 'aktif').length

  return (
    <div className="animate-fade-in">
      <div className="mb-5">
        <h2 className="text-xl font-bold text-slate-800 md:text-2xl">Ödünç Aldıklarım</h2>
        <p className="mt-1 text-sm text-slate-500">
          <span className="font-semibold text-slate-700">{activeCount}</span> aktif ödünç ·{' '}
          <span className="font-semibold text-slate-700">{loans.length}</span> toplam kayıt
        </p>
      </div>

      {loans.length === 0 ? (
        <EmptyState
          icon={BookCopy}
          title="Ödünç kitabınız yok"
          description="Kütüphaneden ödünç aldığınız kitaplar burada listelenir."
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
          {loans.map((loan) => {
            const kitap = loan.kitap
            if (!kitap) return null

            const cover = coverUrl(kitap.kapak)
            const returned = isReturned(loan)
            const overdue = !returned && loan.gecikiyor_mu
            const days = overdue ? loan.gecikme_gun ?? 0 : loan.kalan_gun ?? 0
            const progress = loanProgress(loan)

            const accentColor = returned
              ? 'bg-slate-300'
              : overdue
                ? 'bg-red-500'
                : 'bg-emerald-500'

            return (
              <div
                key={loan.islem_id}
                className={`flex overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-slate-100 ${
                  returned ? 'opacity-80' : ''
                }`}
              >
                <div className={`w-1 shrink-0 ${accentColor}`} aria-hidden />

                <div className="flex min-w-0 flex-1 gap-3 p-3">
                  <button
                    type="button"
                    onClick={() => openBook(kitap.id)}
                    className="relative h-[88px] w-[60px] shrink-0 overflow-hidden rounded-lg bg-brand-50 shadow-sm"
                  >
                    {cover ? (
                      <img
                        src={cover}
                        alt={kitap.eser_adi}
                        className="h-full w-full object-cover"
                      />
                    ) : (
                      <div className="flex h-full w-full items-center justify-center">
                        <BookOpen className="h-7 w-7 text-brand-200" />
                      </div>
                    )}
                  </button>

                  <div className="flex min-w-0 flex-1 flex-col justify-between py-0.5">
                    <div>
                      <button type="button" onClick={() => openBook(kitap.id)}>
                        <h3 className="line-clamp-2 text-left text-sm font-bold leading-snug text-slate-900 hover:text-brand-600">
                          {kitap.eser_adi}
                        </h3>
                      </button>
                      <p className="mt-0.5 line-clamp-1 text-xs text-slate-500">{kitap.yazar_adi}</p>
                    </div>

                    <div className="mt-2 space-y-1">
                      {loan.kutuphane_adi && (
                        <p className="flex items-center gap-1.5 text-[11px] text-slate-500">
                          <MapPin className="h-3.5 w-3.5 shrink-0 text-slate-400" />
                          <span className="truncate">{loan.kutuphane_adi}</span>
                        </p>
                      )}
                      <p className="flex items-center gap-1.5 text-[11px] text-slate-500">
                        <Calendar className="h-3.5 w-3.5 shrink-0 text-slate-400" />
                        <span>
                          {formatDateShort(loan.odunc_tarihi)} –{' '}
                          {returned
                            ? formatDateShort(loan.iade_tarihi_gercek)
                            : formatDateShort(loan.iade_tarihi_planlanan)}
                        </span>
                      </p>
                      {!returned && !overdue && (
                        <div className="h-1 w-full overflow-hidden rounded-full bg-slate-100">
                          <div
                            className="h-full rounded-full bg-emerald-500 transition-all"
                            style={{ width: `${progress}%` }}
                          />
                        </div>
                      )}
                    </div>
                  </div>

                  <div className="flex shrink-0 flex-col items-center justify-center pl-1">
                    {returned ? (
                      <>
                        <div className="flex h-12 w-12 items-center justify-center rounded-full border-2 border-slate-300 bg-slate-50 text-slate-500">
                          <CheckCircle2 className="h-5 w-5" />
                        </div>
                        <span className="mt-1 text-[10px] font-medium text-slate-500">
                          iade edildi
                        </span>
                      </>
                    ) : (
                      <>
                        <div
                          className={`flex h-12 w-12 items-center justify-center rounded-full border-2 ${
                            overdue
                              ? 'border-red-500 text-red-500'
                              : 'border-emerald-500 text-emerald-600'
                          }`}
                        >
                          <span className="text-sm font-bold leading-none">
                            {overdue ? `-${days}` : days}
                          </span>
                        </div>
                        <span
                          className={`mt-1 text-[10px] font-medium ${
                            overdue ? 'text-red-500' : 'text-emerald-600'
                          }`}
                        >
                          {overdue ? 'gecikme' : 'gün kaldı'}
                        </span>
                      </>
                    )}
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

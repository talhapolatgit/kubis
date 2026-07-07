import { BookOpen } from 'lucide-react'
import type { BookListItem } from '../api/types'
import { useBookSheet } from '../context/BookSheetContext'
import { coverUrl } from '../utils/formatters'
import { StatusBadge } from './StatusBadge'

interface BookCardProps {
  book: BookListItem
  showFavorite?: boolean
  isFavorite?: boolean
  onToggleFavorite?: () => void
}

export function BookCard({
  book,
  showFavorite,
  isFavorite,
  onToggleFavorite,
}: BookCardProps) {
  const { openBook } = useBookSheet()
  const cover = coverUrl(book.kapak)

  return (
    <button
      type="button"
      onClick={() => openBook(book.id)}
      className="group flex w-full gap-3 rounded-2xl bg-white p-3 text-left shadow-sm ring-1 ring-slate-100 transition-all hover:shadow-md active:scale-[0.98]"
    >
      <div className="relative h-24 w-[72px] shrink-0 overflow-hidden rounded-xl bg-brand-50">
        {cover ? (
          <img
            src={cover}
            alt={book.eser_adi}
            className="h-full w-full object-cover"
            loading="lazy"
          />
        ) : (
          <div className="flex h-full w-full items-center justify-center">
            <BookOpen className="h-8 w-8 text-brand-200" />
          </div>
        )}
      </div>

      <div className="flex min-w-0 flex-1 flex-col justify-between py-0.5">
        <div>
          <h3 className="line-clamp-2 text-sm font-semibold leading-snug text-slate-800 group-hover:text-brand-600">
            {book.eser_adi}
          </h3>
          <p className="mt-1 line-clamp-1 text-xs text-slate-500">{book.yazar_adi}</p>
        </div>
        <div className="mt-2 flex items-center justify-between gap-2">
          <StatusBadge status={book.durum} />
          {book.kutuphane_adi && (
            <span className="truncate text-[11px] text-slate-400">
              {book.kutuphane_adi}
            </span>
          )}
        </div>
      </div>

      {showFavorite && onToggleFavorite && (
        <button
          type="button"
          onClick={(e) => {
            e.stopPropagation()
            onToggleFavorite()
          }}
          className="flex h-9 w-9 shrink-0 items-center justify-center self-center rounded-full bg-slate-50 text-lg transition-colors hover:bg-red-50"
          aria-label={isFavorite ? 'Favorilerden çıkar' : 'Favorilere ekle'}
        >
          {isFavorite ? '❤️' : '🤍'}
        </button>
      )}
    </button>
  )
}

export function BookCardSkeleton() {
  return (
    <div className="flex gap-3 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-100">
      <div className="skeleton h-24 w-[72px] shrink-0 rounded-xl" />
      <div className="flex flex-1 flex-col justify-between py-1">
        <div className="space-y-2">
          <div className="skeleton h-4 w-3/4 rounded" />
          <div className="skeleton h-3 w-1/2 rounded" />
        </div>
        <div className="skeleton mt-3 h-5 w-16 rounded-full" />
      </div>
    </div>
  )
}

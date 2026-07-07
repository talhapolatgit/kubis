import { BookOpen } from 'lucide-react'
import type { BookListItem } from '../api/types'
import { useBookSheet } from '../context/BookSheetContext'
import { coverUrl } from '../utils/formatters'

interface BookCoverGalleryProps {
  books: BookListItem[]
}

export function BookCoverGallery({ books }: BookCoverGalleryProps) {
  const { openBook } = useBookSheet()

  return (
    <div className="grid grid-cols-3 gap-2.5 sm:grid-cols-4 sm:gap-3 md:grid-cols-5 lg:grid-cols-6">
      {books.map((book, index) => (
        <BookCoverTile
          key={book.id}
          book={book}
          index={index}
          onOpen={() => openBook(book.id)}
        />
      ))}
    </div>
  )
}

function BookCoverTile({
  book,
  index,
  onOpen,
}: {
  book: BookListItem
  index: number
  onOpen: () => void
}) {
  const cover = coverUrl(book.kapak)

  return (
    <button
      type="button"
      onClick={onOpen}
      className="group relative aspect-[2/3] overflow-hidden rounded-xl bg-brand-50 shadow-sm ring-1 ring-slate-200/80 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-brand-900/15 hover:ring-brand-300 active:scale-[0.97] animate-fade-in"
      style={{ animationDelay: `${Math.min(index, 12) * 40}ms` }}
      aria-label={book.eser_adi}
    >
      {cover ? (
        <img
          src={cover}
          alt={book.eser_adi}
          className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
          loading="lazy"
        />
      ) : (
        <div className="flex h-full w-full flex-col items-center justify-center gap-2 bg-gradient-to-br from-brand-100 to-brand-50 p-2">
          <BookOpen className="h-8 w-8 text-brand-300" />
          <span className="line-clamp-3 text-center text-[10px] font-medium leading-tight text-brand-400">
            {book.eser_adi}
          </span>
        </div>
      )}

      <div className="pointer-events-none absolute inset-0 bg-gradient-to-t from-brand-900/80 via-brand-900/20 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />

      <div className="pointer-events-none absolute inset-x-0 bottom-0 translate-y-2 p-2 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100">
        <p className="line-clamp-2 text-left text-[10px] font-semibold leading-tight text-white sm:text-xs">
          {book.eser_adi}
        </p>
        <p className="mt-0.5 line-clamp-1 text-left text-[9px] text-white/75 sm:text-[10px]">
          {book.yazar_adi}
        </p>
      </div>

      <span
        className={`pointer-events-none absolute right-1.5 top-1.5 rounded-full px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wide shadow-sm sm:text-[9px] ${
          book.durum === 'Rafta'
            ? 'bg-emerald-500/90 text-white'
            : book.durum === 'Rezerve'
              ? 'bg-amber-500/90 text-white'
              : 'bg-slate-600/90 text-white'
        }`}
      >
        {book.durum === 'Rafta' ? 'Müsait' : book.durum}
      </span>
    </button>
  )
}

export function BookCoverGallerySkeleton() {
  return (
    <div className="grid grid-cols-3 gap-2.5 sm:grid-cols-4 sm:gap-3 md:grid-cols-5 lg:grid-cols-6">
      {Array.from({ length: 12 }).map((_, i) => (
        <div key={i} className="skeleton aspect-[2/3] rounded-xl" />
      ))}
    </div>
  )
}

import { BookOpen, ChevronDown, Filter, LayoutGrid, List } from 'lucide-react'
import { useCallback, useEffect, useState } from 'react'
import { api } from '../api/client'
import type { BookListItem, Category, Library } from '../api/types'
import { BookCard, BookCardSkeleton } from '../components/BookCard'
import { BookCoverGallery, BookCoverGallerySkeleton } from '../components/BookCoverGallery'
import { EmptyState } from '../components/EmptyState'
import { SearchBar } from '../components/SearchBar'

type ViewMode = 'list' | 'gallery'

export function BooksPage() {
  const [books, setBooks] = useState<BookListItem[]>([])
  const [categories, setCategories] = useState<Category[]>([])
  const [libraries, setLibraries] = useState<Library[]>([])
  const [search, setSearch] = useState('')
  const [debouncedSearch, setDebouncedSearch] = useState('')
  const [categoryId, setCategoryId] = useState('')
  const [libraryId, setLibraryId] = useState('')
  const [durum, setDurum] = useState('')
  const [page, setPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  const [loading, setLoading] = useState(true)
  const [loadingMore, setLoadingMore] = useState(false)
  const [showFilters, setShowFilters] = useState(false)
  const [viewMode, setViewMode] = useState<ViewMode>('list')
  const [total, setTotal] = useState(0)

  useEffect(() => {
    const timer = setTimeout(() => setDebouncedSearch(search), 400)
    return () => clearTimeout(timer)
  }, [search])

  useEffect(() => {
    Promise.all([api.categories(), api.libraries()])
      .then(([catRes, libRes]) => {
        if (catRes.data) setCategories(catRes.data)
        if (libRes.data) setLibraries(libRes.data)
      })
      .catch(() => {})
  }, [])

  const fetchBooks = useCallback(
    async (pageNum: number, append = false) => {
      if (append) setLoadingMore(true)
      else setLoading(true)

      try {
        const params: Record<string, string | number> = {
          page: pageNum,
          per_page: 20,
        }
        if (debouncedSearch) params.search = debouncedSearch
        if (categoryId) params.kategori_id = categoryId
        if (libraryId) params.kutuphane_id = libraryId
        if (durum) params.durum = durum

        const res = await api.books(params)
        if (res.data) {
          setBooks((prev) =>
            append ? [...prev, ...res.data!.rows] : res.data!.rows,
          )
          setPage(res.data.current_page)
          setLastPage(res.data.last_page)
          setTotal(res.data.total_records)
        }
      } finally {
        setLoading(false)
        setLoadingMore(false)
      }
    },
    [debouncedSearch, categoryId, libraryId, durum],
  )

  useEffect(() => {
    fetchBooks(1)
  }, [fetchBooks])

  const activeFilterCount = [categoryId, libraryId, durum].filter(Boolean).length

  return (
    <div className="animate-fade-in">
      <div className="mb-4">
        <h2 className="text-xl font-bold text-slate-800 md:text-2xl">Kitap Kataloğu</h2>
        {!loading && (
          <p className="mt-1 text-sm text-slate-500">{total} kitap bulundu</p>
        )}
      </div>

      <div className="mb-4 space-y-3">
        <SearchBar value={search} onChange={setSearch} />
        <div className="flex gap-2">
          <button
            type="button"
            onClick={() => setShowFilters(!showFilters)}
            className="flex flex-1 items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-medium text-slate-600 shadow-sm ring-1 ring-slate-200 transition-colors hover:bg-slate-50"
          >
            <Filter className="h-4 w-4" />
            Filtreler
            {activeFilterCount > 0 && (
              <span className="flex h-5 w-5 items-center justify-center rounded-full bg-brand-600 text-[10px] font-bold text-white">
                {activeFilterCount}
              </span>
            )}
            <ChevronDown
              className={`ml-auto h-4 w-4 transition-transform ${showFilters ? 'rotate-180' : ''}`}
            />
          </button>

          <button
            type="button"
            onClick={() => setViewMode((mode) => (mode === 'list' ? 'gallery' : 'list'))}
            className={`flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-xl shadow-sm ring-1 transition-all active:scale-95 ${
              viewMode === 'gallery'
                ? 'bg-brand-600 text-white ring-brand-600 hover:bg-brand-700'
                : 'bg-white text-slate-600 ring-slate-200 hover:bg-slate-50'
            }`}
            aria-label={viewMode === 'list' ? 'Galeri görünümü' : 'Liste görünümü'}
            title={viewMode === 'list' ? 'Galeri görünümü' : 'Liste görünümü'}
          >
            {viewMode === 'list' ? (
              <LayoutGrid className="h-4 w-4" />
            ) : (
              <List className="h-4 w-4" />
            )}
          </button>
        </div>

        {showFilters && (
          <div className="grid gap-3 rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100 sm:grid-cols-3 animate-fade-in">
            <FilterSelect
              label="Kategori"
              value={categoryId}
              onChange={setCategoryId}
              options={categories.map((c) => ({ value: String(c.id), label: c.title }))}
            />
            <FilterSelect
              label="Kütüphane"
              value={libraryId}
              onChange={setLibraryId}
              options={libraries.map((l) => ({ value: String(l.id), label: l.title }))}
            />
            <FilterSelect
              label="Durum"
              value={durum}
              onChange={setDurum}
              options={[
                { value: 'Rafta', label: 'Rafta' },
                { value: 'Ödünç', label: 'Ödünç' },
                { value: 'Rezerve', label: 'Rezerve' },
              ]}
            />
          </div>
        )}
      </div>

      {loading ? (
        viewMode === 'gallery' ? (
          <BookCoverGallerySkeleton />
        ) : (
          <div className="space-y-3">
            {Array.from({ length: 6 }).map((_, i) => (
              <BookCardSkeleton key={i} />
            ))}
          </div>
        )
      ) : books.length === 0 ? (
        <EmptyState
          icon={BookOpen}
          title="Kitap bulunamadı"
          description="Arama kriterlerinizi değiştirmeyi deneyin."
        />
      ) : (
        <>
          {viewMode === 'gallery' ? (
            <BookCoverGallery books={books} />
          ) : (
            <div className="space-y-3">
              {books.map((book) => (
                <BookCard key={book.id} book={book} />
              ))}
            </div>
          )}

          {page < lastPage && (
            <div className="mt-6 flex justify-center">
              <button
                type="button"
                disabled={loadingMore}
                onClick={() => fetchBooks(page + 1, true)}
                className="rounded-xl bg-white px-6 py-3 text-sm font-medium text-brand-600 shadow-sm ring-1 ring-slate-200 transition-all hover:bg-brand-50 active:scale-[0.98] disabled:opacity-50"
              >
                {loadingMore ? 'Yükleniyor...' : 'Daha Fazla Göster'}
              </button>
            </div>
          )}
        </>
      )}
    </div>
  )
}

function FilterSelect({
  label,
  value,
  onChange,
  options,
}: {
  label: string
  value: string
  onChange: (v: string) => void
  options: { value: string; label: string }[]
}) {
  return (
    <div>
      <label className="mb-1 block text-xs font-medium text-slate-500">{label}</label>
      <select
        value={value}
        onChange={(e) => onChange(e.target.value)}
        className="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm focus:border-brand-400 focus:outline-none"
      >
        <option value="">Tümü</option>
        {options.map((o) => (
          <option key={o.value} value={o.value}>{o.label}</option>
        ))}
      </select>
    </div>
  )
}

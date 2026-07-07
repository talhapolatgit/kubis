import { createContext, useCallback, useContext, useState, type ReactNode } from 'react'
import { BookDetailSheet } from '../components/BookDetailSheet'

interface BookSheetContextValue {
  openBook: (id: number) => void
  closeBook: () => void
  bookId: number | null
}

const BookSheetContext = createContext<BookSheetContextValue | null>(null)

export function BookSheetProvider({ children }: { children: ReactNode }) {
  const [bookId, setBookId] = useState<number | null>(null)

  const openBook = useCallback((id: number) => setBookId(id), [])
  const closeBook = useCallback(() => setBookId(null), [])

  return (
    <BookSheetContext.Provider value={{ openBook, closeBook, bookId }}>
      {children}
      <BookDetailSheet />
    </BookSheetContext.Provider>
  )
}

export function useBookSheet() {
  const ctx = useContext(BookSheetContext)
  if (!ctx) throw new Error('useBookSheet must be used within BookSheetProvider')
  return ctx
}

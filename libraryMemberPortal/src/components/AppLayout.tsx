import { LogOut } from 'lucide-react'
import { Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'
import { BookSheetProvider } from '../context/BookSheetContext'
import { KubisBrand } from './KubisLogo'
import { BottomNav, DesktopNav } from './BottomNav'
import { LoadingSpinner } from './LoadingSpinner'

export function AppLayout() {
  const { profile, isLoading, logout } = useAuth()
  const navigate = useNavigate()

  if (isLoading) {
    return <LoadingSpinner fullScreen label="Oturum kontrol ediliyor..." />
  }

  const handleLogout = () => {
    logout()
    navigate('/giris')
  }

  return (
    <BookSheetProvider>
    <div className="min-h-dvh bg-gradient-to-b from-brand-50/50 to-slate-100">
      <header className="sticky top-0 z-40 border-b border-slate-200/60 bg-white/90 backdrop-blur-lg safe-top">
        <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-3 md:px-6 md:py-4">
          <div className="flex items-center gap-3">
            <KubisBrand
              size="header"
              showTagline={false}
              subtitle={profile ? `Hoş geldiniz, ${profile.ad}` : undefined}
            />
          </div>

          <div className="flex items-center gap-2">
            <DesktopNav />
            <button
              type="button"
              onClick={handleLogout}
              className="hidden items-center gap-1.5 rounded-xl px-3 py-2 text-sm text-slate-500 transition-colors hover:bg-red-50 hover:text-red-600 md:flex"
            >
              <LogOut className="h-4 w-4" />
              Çıkış
            </button>
          </div>
        </div>
      </header>

      <main className="mx-auto max-w-5xl px-4 pb-24 pt-4 md:px-6 md:pb-8">
        <Outlet />
      </main>

      <BottomNav />
    </div>
    </BookSheetProvider>
  )
}

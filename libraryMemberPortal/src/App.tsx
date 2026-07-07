import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { AppLayout } from './components/AppLayout'
import { GuestRoute, ProtectedRoute } from './components/ProtectedRoute'
import { AuthProvider } from './context/AuthContext'
import { BooksPage } from './pages/BooksPage'
import { FavoritesPage } from './pages/FavoritesPage'
import { LoansPage } from './pages/LoansPage'
import { LoginPage } from './pages/LoginPage'
import { ProfilePage } from './pages/ProfilePage'
import { RegisterPage } from './pages/RegisterPage'
import { ReservationsPage } from './pages/ReservationsPage'
import { WaitlistPage } from './pages/WaitlistPage'

export default function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          <Route element={<GuestRoute />}>
            <Route path="/giris" element={<LoginPage />} />
            <Route path="/kayit" element={<RegisterPage />} />
          </Route>

          <Route element={<ProtectedRoute />}>
            <Route element={<AppLayout />}>
              <Route index element={<BooksPage />} />
              <Route path="favoriler" element={<FavoritesPage />} />
              <Route path="rezervasyonlar" element={<ReservationsPage />} />
              <Route path="oduncler" element={<LoansPage />} />
              <Route path="beklemeler" element={<WaitlistPage />} />
              <Route path="profil" element={<ProfilePage />} />
            </Route>
          </Route>

          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  )
}

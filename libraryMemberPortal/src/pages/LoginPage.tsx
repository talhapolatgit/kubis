import { Eye, EyeOff } from 'lucide-react'
import { useState, type FormEvent } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { ApiError } from '../api/client'
import { KubisBrand } from '../components/KubisLogo'
import { useAuth } from '../context/AuthContext'

export function LoginPage() {
  const { login } = useAuth()
  const navigate = useNavigate()
  const [tcKimlik, setTcKimlik] = useState('')
  const [dogumTarihi, setDogumTarihi] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)
  const [showHint, setShowHint] = useState(false)

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault()
    setError('')
    setLoading(true)

    try {
      await login({ tc_kimlik: tcKimlik, dogum_tarihi: dogumTarihi })
      navigate('/')
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Giriş başarısız.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="flex min-h-dvh flex-col bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800">
      <div className="flex flex-1 flex-col items-center justify-center px-6 py-12 safe-top safe-bottom">
        <div className="mb-8 flex flex-col items-center animate-fade-in">
          <KubisBrand size="lg" variant="light" layout="vertical" className="mb-2" />
          <p className="mt-3 text-center text-sm text-brand-200">
            Kitapları keşfedin, rezerve edin ve favorilerinizi yönetin
          </p>
        </div>

        <div className="w-full max-w-sm animate-fade-in rounded-3xl bg-white p-6 shadow-2xl shadow-brand-900/30">
          <h2 className="text-xl font-bold text-slate-800">Giriş Yap</h2>
          <p className="mt-1 text-sm text-slate-500">
            T.C. kimlik numaranız ve doğum tarihiniz ile giriş yapın
          </p>

          <form onSubmit={handleSubmit} className="mt-6 space-y-4">
            <div>
              <label htmlFor="tc" className="mb-1.5 block text-sm font-medium text-slate-700">
                T.C. Kimlik No
              </label>
              <input
                id="tc"
                type="text"
                inputMode="numeric"
                maxLength={11}
                value={tcKimlik}
                onChange={(e) => setTcKimlik(e.target.value.replace(/\D/g, ''))}
                required
                className="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-brand-400 focus:ring-2 focus:ring-brand-100 focus:outline-none"
                placeholder="11 haneli T.C. kimlik no"
              />
            </div>

            <div>
              <label htmlFor="dogum" className="mb-1.5 block text-sm font-medium text-slate-700">
                Doğum Tarihi
              </label>
              <input
                id="dogum"
                type="date"
                value={dogumTarihi}
                onChange={(e) => setDogumTarihi(e.target.value)}
                required
                max={new Date().toISOString().split('T')[0]}
                className="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-brand-400 focus:ring-2 focus:ring-brand-100 focus:outline-none"
              />
            </div>

            {error && (
              <div className="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">
                {error}
              </div>
            )}

            <button
              type="submit"
              disabled={loading || tcKimlik.length !== 11 || !dogumTarihi}
              className="w-full rounded-xl bg-brand-600 py-3.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/30 transition-all hover:bg-brand-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50"
            >
              {loading ? 'Giriş yapılıyor...' : 'Giriş Yap'}
            </button>
          </form>

          <div className="mt-6 text-center">
            <p className="text-sm text-slate-500">
              Henüz üye değil misiniz?{' '}
              <Link to="/kayit" className="font-semibold text-brand-600 hover:text-brand-700">
                Kayıt Ol
              </Link>
            </p>
          </div>
        </div>

        <button
          type="button"
          onClick={() => setShowHint(!showHint)}
          className="mt-6 flex items-center gap-1.5 text-xs text-brand-200 hover:text-white"
        >
          {showHint ? <EyeOff className="h-3.5 w-3.5" /> : <Eye className="h-3.5 w-3.5" />}
          Giriş bilgileri hakkında
        </button>
        {showHint && (
          <p className="mt-2 max-w-xs text-center text-xs text-brand-200/80 animate-fade-in">
            Giriş için kayıtlı T.C. kimlik numaranız ve doğum tarihiniz kullanılır. Şifre gerekmez.
          </p>
        )}
      </div>
    </div>
  )
}

import {
  BookCopy,
  Calendar,
  Clock,
  Heart,
  LogOut,
  Mail,
  MapPin,
  Phone,
  User,
} from 'lucide-react'
import { useEffect, useState, type ReactNode } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { api } from '../api/client'
import type { MemberCounts } from '../api/types'
import { LoadingSpinner } from '../components/LoadingSpinner'
import { useAuth } from '../context/AuthContext'
import { formatDate } from '../utils/formatters'

export function ProfilePage() {
  const { profile, logout } = useAuth()
  const navigate = useNavigate()
  const [counts, setCounts] = useState<MemberCounts | null>(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    api
      .memberCounts()
      .then((res) => {
        if (res.data) setCounts(res.data)
      })
      .finally(() => setLoading(false))
  }, [])

  const handleLogout = () => {
    logout()
    navigate('/giris')
  }

  if (!profile) return <LoadingSpinner fullScreen />

  const stats = [
    { label: 'Favori', value: counts?.favori_count ?? 0, icon: Heart, color: 'text-red-500 bg-red-50', to: '/favoriler' },
    { label: 'Rezervasyon', value: counts?.rezerve_count ?? 0, icon: Calendar, color: 'text-blue-500 bg-blue-50', to: '/rezervasyonlar' },
    { label: 'Bekleme Listesi', value: counts?.bekleme_count ?? 0, icon: Clock, color: 'text-amber-500 bg-amber-50', to: '/beklemeler' },
    { label: 'Ödünçler', value: counts?.odunc_count ?? 0, icon: BookCopy, color: 'text-emerald-500 bg-emerald-50', to: '/oduncler' },
  ]

  return (
    <div className="animate-fade-in">
      <div className="mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-brand-600 to-brand-800 p-6 text-white shadow-lg">
        <div className="flex items-center gap-4">
          <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/15 text-2xl font-bold ring-1 ring-white/20">
            {profile.ad.charAt(0)}{profile.soyad.charAt(0)}
          </div>
          <div>
            <h2 className="text-xl font-bold">{profile.ad_soyad}</h2>
            <p className="mt-0.5 text-sm text-brand-200">
              Üye #{profile.id} · {profile.statu === 'aktif' ? 'Aktif' : profile.statu}
            </p>
          </div>
        </div>

        {!loading && (
          <div className="mt-5 grid grid-cols-2 gap-3">
            {stats.map(({ label, value, icon: Icon, color, to }) => (
              <Link
                key={label}
                to={to}
                className="flex items-center gap-3 rounded-2xl bg-white/10 px-4 py-3 backdrop-blur-sm transition-colors hover:bg-white/15 active:scale-[0.98]"
              >
                <div className={`flex h-9 w-9 items-center justify-center rounded-xl ${color}`}>
                  <Icon className="h-4 w-4" />
                </div>
                <div>
                  <p className="text-lg font-bold">{value}</p>
                  <p className="text-[11px] text-brand-200">{label}</p>
                </div>
              </Link>
            ))}
          </div>
        )}
      </div>

      <div className="space-y-3">
        <Section title="İletişim Bilgileri">
          <InfoRow icon={Mail} label="E-posta" value={profile.email} />
          <InfoRow icon={Phone} label="Telefon" value={profile.telefon} />
          {profile.telefon2 && (
            <InfoRow icon={Phone} label="Telefon 2" value={profile.telefon2} />
          )}
        </Section>

        <Section title="Adres">
          <InfoRow
            icon={MapPin}
            label="Konum"
            value={`${profile.il}, ${profile.ilce}${profile.mahalle ? `, ${profile.mahalle}` : ''}`}
          />
          {profile.acik_adres && (
            <InfoRow icon={MapPin} label="Açık Adres" value={profile.acik_adres} />
          )}
        </Section>

        <Section title="Üyelik">
          <InfoRow icon={User} label="T.C. Kimlik" value={maskTc(profile.tc_kimlik)} />
          <InfoRow icon={Calendar} label="Doğum Tarihi" value={formatDate(profile.dogum_tarihi)} />
          <InfoRow
            icon={Calendar}
            label="Üyelik Başlangıcı"
            value={formatDate(profile.uyelik_baslangic)}
          />
          {profile.uyelik_bitis && (
            <InfoRow
              icon={Calendar}
              label="Üyelik Bitişi"
              value={formatDate(profile.uyelik_bitis)}
            />
          )}
        </Section>
      </div>

      <button
        type="button"
        onClick={handleLogout}
        className="mt-6 flex w-full items-center justify-center gap-2 rounded-2xl bg-red-50 py-3.5 text-sm font-semibold text-red-600 transition-colors hover:bg-red-100 md:hidden"
      >
        <LogOut className="h-4 w-4" />
        Çıkış Yap
      </button>
    </div>
  )
}

function Section({ title, children }: { title: string; children: ReactNode }) {
  return (
    <div className="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-slate-100">
      <h3 className="mb-3 text-sm font-semibold text-slate-700">{title}</h3>
      <div className="space-y-3">{children}</div>
    </div>
  )
}

function InfoRow({
  icon: Icon,
  label,
  value,
}: {
  icon: React.ComponentType<{ className?: string }>
  label: string
  value: string
}) {
  return (
    <div className="flex items-start gap-3">
      <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-50">
        <Icon className="h-4 w-4 text-slate-400" />
      </div>
      <div className="min-w-0">
        <p className="text-[11px] font-medium text-slate-400">{label}</p>
        <p className="text-sm text-slate-700">{value}</p>
      </div>
    </div>
  )
}

function maskTc(tc: string): string {
  if (tc.length !== 11) return tc
  return `${tc.slice(0, 3)}*****${tc.slice(8)}`
}

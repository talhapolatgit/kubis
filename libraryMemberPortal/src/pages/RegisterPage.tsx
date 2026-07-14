import { ArrowLeft, ArrowRight } from 'lucide-react'
import { useMemo, useState, type FormEvent, type ReactNode } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { ApiError } from '../api/client'
import type { RegisterPayload } from '../api/types'
import { KubisLogo } from '../components/KubisLogo'
import { APP_NAME } from '../constants/branding'
import { useAuth } from '../context/AuthContext'
import { formatBirthDateInput, isMinor, parseBirthDate } from '../utils/formatters'

const TURKIYE_ILLER = [
  'Adana', 'Adıyaman', 'Afyonkarahisar', 'Ağrı', 'Amasya', 'Ankara', 'Antalya', 'Artvin',
  'Aydın', 'Balıkesir', 'Bilecik', 'Bingöl', 'Bitlis', 'Bolu', 'Burdur', 'Bursa',
  'Çanakkale', 'Çankırı', 'Çorum', 'Denizli', 'Diyarbakır', 'Edirne', 'Elazığ', 'Erzincan',
  'Erzurum', 'Eskişehir', 'Gaziantep', 'Giresun', 'Gümüşhane', 'Hakkari', 'Hatay', 'Isparta',
  'Mersin', 'İstanbul', 'İzmir', 'Kars', 'Kastamonu', 'Kayseri', 'Kırklareli', 'Kırşehir',
  'Kocaeli', 'Konya', 'Kütahya', 'Malatya', 'Manisa', 'Kahramanmaraş', 'Mardin', 'Muğla',
  'Muş', 'Nevşehir', 'Niğde', 'Ordu', 'Rize', 'Sakarya', 'Samsun', 'Siirt', 'Sinop',
  'Sivas', 'Tekirdağ', 'Tokat', 'Trabzon', 'Tunceli', 'Şanlıurfa', 'Uşak', 'Van',
  'Yozgat', 'Zonguldak', 'Aksaray', 'Bayburt', 'Karaman', 'Kırıkkale', 'Batman', 'Şırnak',
  'Bartın', 'Ardahan', 'Iğdır', 'Yalova', 'Karabük', 'Kilis', 'Osmaniye', 'Düzce',
]

export function RegisterPage() {
  const { register } = useAuth()
  const navigate = useNavigate()
  const [step, setStep] = useState(1)
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  const [form, setForm] = useState({
    tc_kimlik: '',
    dogum_tarihi: '',
    email: '',
    telefon: '',
    il: '',
    ilce: '',
    mahalle: '',
    acik_adres: '',
    veli_ad: '',
    veli_soyad: '',
    veli_tc_kimlik: '',
    veli_dogum_tarihi: '',
    veli_telefon: '',
  })

  const normalizedBirthDate = useMemo(
    () => parseBirthDate(form.dogum_tarihi),
    [form.dogum_tarihi],
  )
  const normalizedVeliBirthDate = useMemo(
    () => parseBirthDate(form.veli_dogum_tarihi),
    [form.veli_dogum_tarihi],
  )

  const needsGuardian = useMemo(
    () => normalizedBirthDate !== null && isMinor(normalizedBirthDate),
    [normalizedBirthDate],
  )

  const update = (field: string, value: string) => {
    setForm((prev) => ({ ...prev, [field]: value }))
  }

  const handleSubmit = async (e: FormEvent) => {
    e.preventDefault()
    setError('')

    if (!normalizedBirthDate) {
      setError('Doğum tarihinizi GG.AA.YYYY formatında girin.')
      return
    }

    if (needsGuardian && !normalizedVeliBirthDate) {
      setError('Veli doğum tarihini GG.AA.YYYY formatında girin.')
      return
    }

    setLoading(true)

    try {
      const payload: RegisterPayload = {
        tc_kimlik: form.tc_kimlik,
        dogum_tarihi: normalizedBirthDate,
        email: form.email,
        telefon: form.telefon,
        il: form.il,
        ilce: form.ilce,
      }
      if (form.mahalle) payload.mahalle = form.mahalle
      if (form.acik_adres) payload.acik_adres = form.acik_adres

      if (needsGuardian) {
        payload.veli_ad = form.veli_ad
        payload.veli_soyad = form.veli_soyad
        payload.veli_tc_kimlik = form.veli_tc_kimlik
        payload.veli_dogum_tarihi = normalizedVeliBirthDate!
        payload.veli_telefon = form.veli_telefon
      }

      await register(payload)
      navigate('/')
    } catch (err) {
      setError(err instanceof ApiError ? err.message : 'Kayıt başarısız.')
    } finally {
      setLoading(false)
    }
  }

  const canProceedStep1 =
    form.tc_kimlik.length === 11 && normalizedBirthDate && form.email && form.telefon

  const canProceedStep2 = form.il && form.ilce

  const canSubmit =
    canProceedStep1 &&
    canProceedStep2 &&
    (!needsGuardian ||
      (form.veli_ad &&
        form.veli_soyad &&
        form.veli_tc_kimlik.length === 11 &&
        normalizedVeliBirthDate &&
        form.veli_telefon))

  return (
    <div className="flex min-h-dvh flex-col bg-gradient-to-br from-brand-700 via-brand-600 to-brand-800">
      <div className="flex flex-1 flex-col items-center justify-center px-6 py-12 safe-top safe-bottom">
        <div className="w-full max-w-lg animate-fade-in">
          <div className="mb-8 flex flex-col items-center text-center">
            <KubisLogo size="lg" />
            <h1 className="mt-4 text-xl font-bold text-white">{`${APP_NAME}'e Kayıt Ol`}</h1>
            <p className="mt-1 text-sm text-brand-200">
              Adım {step} / {needsGuardian ? 3 : 2}
            </p>
          </div>

          <div className="mb-6 flex gap-2">
          {[1, 2, ...(needsGuardian ? [3] : [])].map((s) => (
            <div
              key={s}
              className={`h-1 flex-1 rounded-full transition-colors ${
                s <= step ? 'bg-accent-400' : 'bg-white/20'
              }`}
            />
          ))}
        </div>

        <form
          onSubmit={handleSubmit}
          className="rounded-3xl bg-white p-6 shadow-2xl shadow-brand-900/30 animate-fade-in"
        >
          {step === 1 && (
            <div className="space-y-4">
              <h2 className="text-lg font-bold text-slate-800">Kimlik Bilgileri</h2>
              <p className="text-sm text-slate-500">
                Bilgileriniz KPS üzerinden doğrulanacaktır.
              </p>

              <Field label="T.C. Kimlik No" required>
                <input
                  type="text"
                  inputMode="numeric"
                  maxLength={11}
                  value={form.tc_kimlik}
                  onChange={(e) => update('tc_kimlik', e.target.value.replace(/\D/g, ''))}
                  className={inputClass}
                  placeholder="11 haneli"
                />
              </Field>

              <Field label="Doğum Tarihi" required>
                <input
                  type="text"
                  inputMode="numeric"
                  value={form.dogum_tarihi}
                  onChange={(e) => update('dogum_tarihi', formatBirthDateInput(e.target.value))}
                  className={inputClass}
                  placeholder="GG.AA.YYYY"
                  autoComplete="bday"
                />
              </Field>

              {needsGuardian && (
                <div className="rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-700">
                  18 yaşından küçük olduğunuz için veli bilgileri gereklidir.
                </div>
              )}

              <Field label="E-posta" required>
                <input
                  type="email"
                  value={form.email}
                  onChange={(e) => update('email', e.target.value)}
                  className={inputClass}
                  placeholder="ornek@email.com"
                />
              </Field>

              <Field label="Telefon" required>
                <input
                  type="tel"
                  value={form.telefon}
                  onChange={(e) => update('telefon', e.target.value)}
                  className={inputClass}
                  placeholder="05XX XXX XX XX"
                />
              </Field>

              <ContinueButton
                disabled={!canProceedStep1}
                onClick={() => setStep(2)}
                className="w-full"
              />
            </div>
          )}

          {step === 2 && (
            <div className="space-y-4">
              <h2 className="text-lg font-bold text-slate-800">Adres Bilgileri</h2>

              <Field label="İl" required>
                <select
                  value={form.il}
                  onChange={(e) => update('il', e.target.value)}
                  className={inputClass}
                >
                  <option value="">İl seçin</option>
                  {TURKIYE_ILLER.map((il) => (
                    <option key={il} value={il}>{il}</option>
                  ))}
                </select>
              </Field>

              <Field label="İlçe" required>
                <input
                  type="text"
                  value={form.ilce}
                  onChange={(e) => update('ilce', e.target.value)}
                  className={inputClass}
                />
              </Field>

              <Field label="Mahalle">
                <input
                  type="text"
                  value={form.mahalle}
                  onChange={(e) => update('mahalle', e.target.value)}
                  className={inputClass}
                />
              </Field>

              <Field label="Açık Adres">
                <textarea
                  value={form.acik_adres}
                  onChange={(e) => update('acik_adres', e.target.value)}
                  rows={3}
                  className={inputClass}
                />
              </Field>

              <div className="flex gap-3">
                <button type="button" onClick={() => setStep(1)} className={btnSecondary}>
                  Geri
                </button>
                {needsGuardian ? (
                  <ContinueButton
                    disabled={!canProceedStep2}
                    onClick={() => setStep(3)}
                    className="flex-1"
                  />
                ) : (
                  <button
                    type="submit"
                    disabled={!canSubmit || loading}
                    className={`flex-1 ${btnPrimary}`}
                  >
                    {loading ? 'Kaydediliyor...' : 'Kayıt Ol'}
                  </button>
                )}
              </div>
            </div>
          )}

          {step === 3 && needsGuardian && (
            <div className="space-y-4">
              <h2 className="text-lg font-bold text-slate-800">Veli Bilgileri</h2>

              <Field label="Veli Adı" required>
                <input
                  type="text"
                  value={form.veli_ad}
                  onChange={(e) => update('veli_ad', e.target.value)}
                  className={inputClass}
                />
              </Field>

              <Field label="Veli Soyadı" required>
                <input
                  type="text"
                  value={form.veli_soyad}
                  onChange={(e) => update('veli_soyad', e.target.value)}
                  className={inputClass}
                />
              </Field>

              <Field label="Veli T.C. Kimlik No" required>
                <input
                  type="text"
                  inputMode="numeric"
                  maxLength={11}
                  value={form.veli_tc_kimlik}
                  onChange={(e) => update('veli_tc_kimlik', e.target.value.replace(/\D/g, ''))}
                  className={inputClass}
                />
              </Field>

              <Field label="Veli Doğum Tarihi" required>
                <input
                  type="text"
                  inputMode="numeric"
                  value={form.veli_dogum_tarihi}
                  onChange={(e) =>
                    update('veli_dogum_tarihi', formatBirthDateInput(e.target.value))
                  }
                  className={inputClass}
                  placeholder="GG.AA.YYYY"
                />
              </Field>

              <Field label="Veli Telefon" required>
                <input
                  type="tel"
                  value={form.veli_telefon}
                  onChange={(e) => update('veli_telefon', e.target.value)}
                  className={inputClass}
                />
              </Field>

              <div className="flex gap-3">
                <button type="button" onClick={() => setStep(2)} className={btnSecondary}>
                  Geri
                </button>
                <button
                  type="submit"
                  disabled={!canSubmit || loading}
                  className={`flex-1 ${btnPrimary}`}
                >
                  {loading ? 'Kaydediliyor...' : 'Kayıt Ol'}
                </button>
              </div>
            </div>
          )}

          {error && (
            <div className="mt-4 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-600">
              {error}
            </div>
          )}
        </form>

        <Link
          to="/giris"
          className="mt-8 flex items-center justify-center gap-1.5 text-sm text-brand-200 hover:text-white"
        >
          <ArrowLeft className="h-4 w-4" />
          Giriş sayfasına dön
        </Link>
        </div>
      </div>
    </div>
  )
}

function Field({
  label,
  required,
  children,
}: {
  label: string
  required?: boolean
  children: ReactNode
}) {
  return (
    <div>
      <label className="mb-1.5 block text-sm font-medium text-slate-700">
        {label}
        {required && <span className="text-red-500"> *</span>}
      </label>
      {children}
    </div>
  )
}

const inputClass =
  'w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-brand-400 focus:ring-2 focus:ring-brand-100 focus:outline-none'

const btnPrimary =
  'rounded-xl bg-brand-600 py-3.5 text-sm font-semibold text-white shadow-lg shadow-brand-600/30 transition-all hover:bg-brand-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50'

const btnSecondary =
  'rounded-xl border border-slate-200 px-4 py-3.5 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-50 active:scale-[0.98]'

function ContinueButton({
  disabled,
  onClick,
  className = '',
}: {
  disabled: boolean
  onClick: () => void
  className?: string
}) {
  return (
    <button
      type="button"
      disabled={disabled}
      onClick={onClick}
      className={`group flex items-center justify-center gap-2 rounded-xl py-3.5 text-sm font-semibold transition-all active:scale-[0.98] disabled:cursor-not-allowed ${
        disabled
          ? 'bg-slate-100 text-slate-400 shadow-none'
          : 'bg-brand-600 text-white shadow-lg shadow-brand-600/30 hover:bg-brand-700 hover:shadow-brand-600/40'
      } ${className}`}
    >
      Devam Et
      <ArrowRight
        className={`h-4 w-4 transition-transform ${disabled ? '' : 'group-hover:translate-x-0.5'}`}
      />
    </button>
  )
}

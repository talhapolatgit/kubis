export interface ApiResponse<T = unknown> {
  status: number
  success: boolean
  message: string
  data?: T
}

export interface PaginatedData<T> {
  rows: T[]
  current_page: number
  last_page: number
  per_page: number
  total_records: number
  from: number
  to: number
}

export interface AuthData {
  token_type: string
  token: string
  expires_in: number
  uye_id: number
}

export interface MemberProfile {
  id: number
  tc_kimlik: string
  ad: string
  soyad: string
  ad_soyad: string
  dogum_tarihi: string
  email: string
  telefon: string
  telefon2: string | null
  telefon_dogrulandi: boolean
  il: string
  ilce: string
  mahalle: string | null
  acik_adres: string | null
  statu: string
  uyelik_baslangic: string
  uyelik_bitis: string | null
}

export interface BookListItem {
  id: number
  eser_adi: string
  yazar_adi: string
  kutuphane_adi: string
  durum: string
  kapak: string | null
}

export interface BookDetail {
  id: number
  demirbas_no: string
  eser_adi: string
  eser_adi_alt: string | null
  isbn_issn: string | null
  yazar_adi: string
  yayinevi_adi: string
  kutuphane_adi: string
  yayin_yeri: string | null
  yayin_tarihi: string | null
  kategori_id: number | null
  siniflama_yer: string | null
  dil: string | null
  sayfaSayisi: number | null
  aciklama: string | null
  durum: string
  odunc_verilemez: boolean
  rezerv_edilemez: boolean
  kapak: string | null
  tahmini_musaitlik: string | null
  favorimi: boolean
  rezervemi: boolean
  beklememi: boolean
  oduncmu: boolean
}

export interface Category {
  id: number
  title: string
}

export interface Library {
  id: number
  title: string
  address: string | null
  phone: string | null
  email: string | null
}

export interface FavoriteRow {
  islem_id: number
  ekleme_tarihi: string
  kitap: BookDetail | null
}

export interface WaitingRow {
  islem_id: number
  ekleme_tarihi: string
  kitap: (BookDetail & { kutuphane?: Library | null }) | null
}

export interface ReservationRow {
  islem_id: number
  ekleme_tarihi: string
  rezerve_baslangic: string
  rezerve_bitis: string
  oduncAldiMi: string
  iptalMi: string
  suresiDolduMu: string
  kitap: BookDetail | null
}

export interface LoanBook {
  id: number
  demirbas_no: string
  eser_adi: string
  eser_adi_alt: string | null
  isbn_issn: string | null
  yazar_adi: string
  yayinevi_adi: string
  yayin_tarihi: string | null
  kapak: string | null
  sayfaSayisi: number | null
  aciklama: string | null
}

export interface LoanRow {
  islem_id: number
  odunc_tarihi: string | null
  iade_tarihi_planlanan: string | null
  iade_tarihi_gercek: string | null
  sure_uzatimi: number | null
  statu: string
  statu_label: string
  gecikiyor_mu: boolean
  gecikme_gun: number | null
  kalan_gun: number | null
  kitap: LoanBook | null
  kutuphane_adi: string | null
}

export interface MemberCounts {
  favori_count: number
  odunc_count: number
  rezerve_count: number
  bekleme_count: number
}

export interface RegisterPayload {
  tc_kimlik: string
  dogum_tarihi: string
  email: string
  telefon: string
  il: string
  ilce: string
  mahalle?: string
  acik_adres?: string
  veli_ad?: string
  veli_soyad?: string
  veli_tc_kimlik?: string
  veli_dogum_tarihi?: string
  veli_telefon?: string
}

export interface LoginPayload {
  tc_kimlik: string
  dogum_tarihi: string
}

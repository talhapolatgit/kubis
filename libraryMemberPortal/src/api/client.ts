import type {
  ApiResponse,
  AuthData,
  BookDetail,
  BookListItem,
  Category,
  FavoriteRow,
  Library,
  LoanRow,
  LoginPayload,
  MemberCounts,
  MemberProfile,
  PaginatedData,
  RegisterPayload,
  ReservationRow,
  WaitingRow,
} from './types'

const API_BASE =
  import.meta.env.VITE_API_BASE_URL?.replace(/\/$/, '') || '/api/mobile'

class ApiError extends Error {
  status: number

  constructor(message: string, status: number) {
    super(message)
    this.status = status
    this.name = 'ApiError'
  }
}

function getToken(): string | null {
  return localStorage.getItem('auth_token')
}

async function request<T>(
  path: string,
  options: RequestInit = {},
  auth = true,
): Promise<T> {
  const headers: Record<string, string> = {
    Accept: 'application/json',
    ...(options.headers as Record<string, string>),
  }

  if (options.body && !(options.body instanceof FormData)) {
    headers['Content-Type'] = 'application/json'
  }

  if (auth) {
    const token = getToken()
    if (token) {
      headers['Authorization'] = `Bearer ${token}`
    }
  }

  const response = await fetch(`${API_BASE}${path}`, {
    ...options,
    headers,
  })

  let payload: ApiResponse<T> | { message?: string } | null = null

  try {
    payload = await response.json()
  } catch {
    // non-json response
  }

  if (!response.ok) {
    const message =
      (payload && 'message' in payload && payload.message) ||
      'Bir hata oluştu. Lütfen tekrar deneyin.'
    throw new ApiError(message, response.status)
  }

  return payload as T
}

export const api = {
  register(data: RegisterPayload) {
    return request<ApiResponse<AuthData>>('/auth/register', {
      method: 'POST',
      body: JSON.stringify(data),
    }, false)
  },

  login(data: LoginPayload) {
    return request<ApiResponse<AuthData>>('/auth/token', {
      method: 'POST',
      body: JSON.stringify(data),
    }, false)
  },

  profile() {
    return request<ApiResponse<MemberProfile>>('/uyelik-bilgileri')
  },

  books(params: Record<string, string | number> = {}) {
    const query = new URLSearchParams()
    Object.entries(params).forEach(([k, v]) => {
      if (v !== '' && v !== undefined) query.set(k, String(v))
    })
    const qs = query.toString()
    return request<ApiResponse<PaginatedData<BookListItem>>>(
      `/kitaplar${qs ? `?${qs}` : ''}`,
    )
  },

  bookDetail(katalogId: number) {
    return request<ApiResponse<BookDetail[]>>(
      `/kitapdetay?katalog_id=${katalogId}`,
    )
  },

  categories() {
    return request<ApiResponse<Category[]>>('/kategoriler')
  },

  libraries() {
    return request<ApiResponse<Library[]>>('/kutuphaneler')
  },

  favorites(page = 1) {
    return request<ApiResponse<PaginatedData<FavoriteRow>>>(
      `/favoriler?page=${page}&per_page=20`,
    )
  },

  addFavorite(katalogId: number) {
    return request<ApiResponse>('/favoriekle', {
      method: 'POST',
      body: JSON.stringify({ katalog_id: katalogId }),
    })
  },

  removeFavorite(katalogId: number) {
    return request<ApiResponse>('/favorisil', {
      method: 'DELETE',
      body: JSON.stringify({ katalog_id: katalogId }),
    })
  },

  reservations(page = 1) {
    return request<ApiResponse<PaginatedData<ReservationRow>>>(
      `/rezervasyonlar?page=${page}&per_page=20`,
    )
  },

  addReservation(katalogId: number) {
    return request<ApiResponse>('/rezervasyonekle', {
      method: 'POST',
      body: JSON.stringify({ katalog_id: katalogId }),
    })
  },

  cancelReservation(katalogId: number) {
    return request<ApiResponse>('/rezervasyoniptal', {
      method: 'POST',
      body: JSON.stringify({ katalog_id: katalogId }),
    })
  },

  loans(params: Record<string, string | number> = {}) {
    const query = new URLSearchParams()
    Object.entries(params).forEach(([k, v]) => {
      if (v !== '' && v !== undefined) query.set(k, String(v))
    })
    const qs = query.toString()
    return request<ApiResponse<PaginatedData<LoanRow>>>(
      `/oduncler${qs ? `?${qs}` : ''}`,
    )
  },

  waitings(page = 1) {
    return request<ApiResponse<PaginatedData<WaitingRow>>>(
      `/beklemeler?page=${page}&per_page=20`,
    )
  },

  removeWaiting(katalogId: number) {
    return request<ApiResponse>('/beklemesil', {
      method: 'DELETE',
      body: JSON.stringify({ katalog_id: katalogId }),
    })
  },

  addWaiting(katalogId: number) {
    return request<ApiResponse>('/beklemeekle', {
      method: 'POST',
      body: JSON.stringify({ katalog_id: katalogId }),
    })
  },

  memberCounts() {
    return request<ApiResponse<MemberCounts>>('/uyesayac')
  },
}

export { ApiError }

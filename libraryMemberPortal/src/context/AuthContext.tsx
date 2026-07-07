import {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useState,
  type ReactNode,
} from 'react'
import { api } from '../api/client'
import type { LoginPayload, MemberProfile, RegisterPayload } from '../api/types'

interface AuthState {
  token: string | null
  profile: MemberProfile | null
  isLoading: boolean
  isAuthenticated: boolean
}

interface AuthContextValue extends AuthState {
  login: (data: LoginPayload) => Promise<void>
  register: (data: RegisterPayload) => Promise<void>
  logout: () => void
  refreshProfile: () => Promise<void>
}

const AuthContext = createContext<AuthContextValue | null>(null)

const TOKEN_KEY = 'auth_token'
const UYE_ID_KEY = 'uye_id'

export function AuthProvider({ children }: { children: ReactNode }) {
  const [token, setToken] = useState<string | null>(() =>
    localStorage.getItem(TOKEN_KEY),
  )
  const [profile, setProfile] = useState<MemberProfile | null>(null)
  const [isLoading, setIsLoading] = useState(!!localStorage.getItem(TOKEN_KEY))

  const persistAuth = useCallback((authToken: string, uyeId: number) => {
    localStorage.setItem(TOKEN_KEY, authToken)
    localStorage.setItem(UYE_ID_KEY, String(uyeId))
    setToken(authToken)
  }, [])

  const logout = useCallback(() => {
    localStorage.removeItem(TOKEN_KEY)
    localStorage.removeItem(UYE_ID_KEY)
    setToken(null)
    setProfile(null)
  }, [])

  const refreshProfile = useCallback(async () => {
    const res = await api.profile()
    if (res.data) setProfile(res.data)
  }, [])

  useEffect(() => {
    if (!token) {
      setIsLoading(false)
      return
    }

    let cancelled = false

    ;(async () => {
      try {
        const res = await api.profile()
        if (!cancelled && res.data) setProfile(res.data)
      } catch {
        if (!cancelled) logout()
      } finally {
        if (!cancelled) setIsLoading(false)
      }
    })()

    return () => {
      cancelled = true
    }
  }, [token, logout])

  const login = useCallback(
    async (data: LoginPayload) => {
      const res = await api.login(data)
      if (res.data?.token) {
        persistAuth(res.data.token, res.data.uye_id)
        await refreshProfile()
      }
    },
    [persistAuth, refreshProfile],
  )

  const register = useCallback(
    async (data: RegisterPayload) => {
      const res = await api.register(data)
      if (res.data?.token) {
        persistAuth(res.data.token, res.data.uye_id)
        await refreshProfile()
      }
    },
    [persistAuth, refreshProfile],
  )

  const value = useMemo<AuthContextValue>(
    () => ({
      token,
      profile,
      isLoading,
      isAuthenticated: !!token && !!profile,
      login,
      register,
      logout,
      refreshProfile,
    }),
    [token, profile, isLoading, login, register, logout, refreshProfile],
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

export function useAuth() {
  const ctx = useContext(AuthContext)
  if (!ctx) throw new Error('useAuth must be used within AuthProvider')
  return ctx
}

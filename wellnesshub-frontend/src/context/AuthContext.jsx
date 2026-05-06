import { createContext, useEffect, useMemo, useState } from 'react'
import { authService } from '@/services/authService'
import { unwrapApiData } from '@/utils/apiResponse'
import { normalizeUser } from '@/utils/normalizers'
import { clearStoredAuth, getStoredToken, getStoredUser, setStoredAuth } from '@/utils/authStorage'

export const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [token, setToken] = useState(getStoredToken())
  const [user, setUser] = useState(() => normalizeUser(getStoredUser()))
  const [loading, setLoading] = useState(true)

  const saveAuth = ({ access_token: nextToken, user: nextUser }) => {
    if (!nextToken) return

    const normalizedUser = normalizeUser(nextUser)
    setToken(nextToken)
    setUser(normalizedUser)
    setStoredAuth(nextToken, nextUser)
  }

  const clearAuthState = () => {
    setToken(null)
    setUser(normalizeUser(null))
    clearStoredAuth()
  }

  const login = async (credentials) => {
    const response = await authService.login(credentials)
    const data = unwrapApiData(response)

    saveAuth(data)

    return data
  }

  const register = async (payload) => {
    const response = await authService.register(payload)
    const data = unwrapApiData(response)

    if (data?.token) {
      saveAuth(data)
    }

    return data
  }

  const logout = async () => {
    try {
      if (token) {
        await authService.logout()
      }
    } finally {
      clearAuthState()
    }
  }

  useEffect(() => {
    const bootstrapAuth = async () => {
      if (!token) {
        setLoading(false)
        return
      }

      try {
        const response = await authService.me()
        const meData = unwrapApiData(response)
        const rawUser = meData?.user || meData
        setUser(normalizeUser(rawUser))
      } catch {
        clearAuthState()
      } finally {
        setLoading(false)
      }
    }

    bootstrapAuth()
  }, [token])

  useEffect(() => {
    const handleUnauthorized = () => {
      clearAuthState()
    }

    window.addEventListener('auth:unauthorized', handleUnauthorized)
    return () => {
      window.removeEventListener('auth:unauthorized', handleUnauthorized)
    }
  }, [])

  const value = useMemo(
    () => ({
      user,
      token,
      isAuthenticated: Boolean(token),
      loading,
      login,
      register,
      logout,
    }),
    [user, token, loading]
  )

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>
}

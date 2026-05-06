import { STORAGE_KEYS } from '@/utils/constants'
import Cookies from 'js-cookie'

const COOKIE_OPTIONS = {
  expires: 7,
  sameSite: 'Lax',
}

export function getStoredToken() {
  return Cookies.get(STORAGE_KEYS.TOKEN) || null
}

export function getStoredUser() {
  const rawUser = Cookies.get(STORAGE_KEYS.USER)
  if (!rawUser) return null

  try {
    return JSON.parse(rawUser)
  } catch {
    return null
  }
}

export function setStoredAuth(token, user) {
  if (token) {
    Cookies.set(STORAGE_KEYS.TOKEN, token, COOKIE_OPTIONS)
  }

  if (user) {
    Cookies.set(STORAGE_KEYS.USER, JSON.stringify(user), COOKIE_OPTIONS)
  }
}

export function clearStoredAuth() {
  Cookies.remove(STORAGE_KEYS.TOKEN)
  Cookies.remove(STORAGE_KEYS.USER)
}

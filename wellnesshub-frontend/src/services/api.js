import axios from 'axios'
import Cookies from 'js-cookie'
import { clearStoredAuth } from '@/utils/authStorage'
import { STORAGE_KEYS } from '@/utils/constants'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  },
})

// The backend returns a Sanctum Bearer token, so we store it in a cookie and attach it to API requests.
api.interceptors.request.use((config) => {
  const token = Cookies.get(STORAGE_KEYS.TOKEN)
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error?.response?.status === 401) {
      clearStoredAuth()
      window.dispatchEvent(new CustomEvent('auth:unauthorized'))

      const isLoginPage = window.location.pathname === '/login'
      if (!isLoginPage) {
        window.location.assign('/login')
      }
    }

    return Promise.reject(error)
  }
)

export default api

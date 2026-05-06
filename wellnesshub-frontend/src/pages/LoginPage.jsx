import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import AuthLayout from '@/components/layout/AuthLayout'
import Button from '@/components/common/Button'
import ErrorState from '@/components/common/ErrorState'
import Input from '@/components/common/Input'
import { useAuth } from '@/hooks/useAuth'
import { unwrapError } from '@/utils/apiResponse'

// LoginPage handles account sign-in and stores auth state through AuthContext.
export default function LoginPage() {
  const navigate = useNavigate()
  const { login } = useAuth()

  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  const handleSubmit = async (event) => {
    event.preventDefault()
    setLoading(true)
    setError('')

    try {
      await login({ email, password })
      navigate('/')
    } catch (requestError) {
      setError(unwrapError(requestError))
    } finally {
      setLoading(false)
    }
  }

  return (
    <AuthLayout title="Welcome back">
      <form className="space-y-4" onSubmit={handleSubmit}>
        <Input
          id="login-email"
          label="Email"
          type="email"
          value={email}
          onChange={(event) => setEmail(event.target.value)}
          required
        />
        <Input
          id="login-password"
          label="Password"
          type="password"
          value={password}
          onChange={(event) => setPassword(event.target.value)}
          required
        />
        {error ? <ErrorState message={error} /> : null}
        <Button type="submit" loading={loading} className="w-full">
          Login
        </Button>
        <p className="text-sm text-slate-600">
          No account yet?{' '}
          <Link to="/register" className="font-medium text-emerald-700">
            Create one
          </Link>
        </p>
      </form>
    </AuthLayout>
  )
}

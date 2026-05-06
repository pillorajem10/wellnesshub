import { useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import AuthLayout from '@/components/layout/AuthLayout'
import Button from '@/components/common/Button'
import ErrorState from '@/components/common/ErrorState'
import Input from '@/components/common/Input'
import { useAuth } from '@/hooks/useAuth'
import { unwrapError } from '@/utils/apiResponse'

// RegisterPage handles user sign-up and initializes auth state when registration returns a token.
export default function RegisterPage() {
  const navigate = useNavigate()
  const { register } = useAuth()

  const [form, setForm] = useState({
    fname: '',
    lname: '',
    email: '',
    password: '',
    password_confirmation: '',
  })
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  const updateField = (key, value) => {
    setForm((current) => ({ ...current, [key]: value }))
  }

  const handleSubmit = async (event) => {
    event.preventDefault()
    setLoading(true)
    setError('')

    try {
      await register(form)
      navigate('/')
    } catch (requestError) {
      setError(unwrapError(requestError))
    } finally {
      setLoading(false)
    }
  }

  return (
    <AuthLayout title="Create account" subtitle="Join the WellnessHub community.">
      <form className="space-y-4" onSubmit={handleSubmit}>
        <Input
          id="register-fname"
          label="First name"
          value={form.fname}
          onChange={(event) => updateField('fname', event.target.value)}
          required
        />
        <Input
          id="register-lname"
          label="Last name"
          value={form.lname}
          onChange={(event) => updateField('lname', event.target.value)}
          required
        />
        <Input
          id="register-email"
          label="Email"
          type="email"
          value={form.email}
          onChange={(event) => updateField('email', event.target.value)}
          required
        />
        <Input
          id="register-password"
          label="Password"
          type="password"
          value={form.password}
          onChange={(event) => updateField('password', event.target.value)}
          required
        />
        <Input
          id="register-confirm-password"
          label="Confirm password"
          type="password"
          value={form.password_confirmation}
          onChange={(event) => updateField('password_confirmation', event.target.value)}
          required
        />
        {error ? <ErrorState message={error} /> : null}
        <Button type="submit" loading={loading} className="w-full">
          Register
        </Button>
        <p className="text-sm text-slate-600">
          Already have an account?{' '}
          <Link to="/login" className="font-medium text-emerald-700">
            Login
          </Link>
        </p>
      </form>
    </AuthLayout>
  )
}

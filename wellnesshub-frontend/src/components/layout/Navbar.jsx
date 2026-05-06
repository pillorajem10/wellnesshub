import { Link, NavLink, useNavigate } from 'react-router-dom'
import { Leaf, LogOut, PlusCircle, User } from 'lucide-react'
import Button from '@/components/common/Button'
import { useAuth } from '@/hooks/useAuth'

function navClassName({ isActive }) {
  return isActive
    ? 'text-emerald-700 font-semibold'
    : 'text-slate-600 transition hover:text-emerald-700'
}

export default function Navbar() {
  const navigate = useNavigate()
  const { isAuthenticated, user, logout } = useAuth()

  const handleLogout = async () => {
    await logout()
    navigate('/login')
  }

  return (
    <header className="sticky top-0 z-20 border-b border-slate-200 bg-white/95 backdrop-blur">
      <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <Link
          to="/"
          className="inline-flex items-center gap-2 text-xl font-bold tracking-tight text-slate-950"
        >
          <Leaf className="h-5 w-5 text-emerald-600" />
          WellnessHub
        </Link>

        <nav className="flex flex-wrap items-center gap-4 text-sm">
          <NavLink to="/protocols" className={navClassName}>
            Protocols
          </NavLink>
          <NavLink to="/threads" className={navClassName}>
            Threads
          </NavLink>

          {isAuthenticated ? (
            <>
              <NavLink
                to="/protocols/create"
                className="inline-flex items-center gap-1 text-slate-600 transition hover:text-emerald-700"
              >
                <PlusCircle className="h-4 w-4" />
                <span>Create Protocol</span>
              </NavLink>
              <span className="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                <User className="h-3.5 w-3.5" />
                {user.firstName || 'User'}
              </span>
              <Button
                variant="danger"
                size="sm"
                aria-label="Logout"
                title="Logout"
                onClick={handleLogout}
                className="px-2"
              >
                <LogOut className="h-5 w-5" />
              </Button>
            </>
          ) : (
            <>
              <NavLink to="/login" className={navClassName}>
                Login
              </NavLink>
              <NavLink to="/register" className={navClassName}>
                Register
              </NavLink>
            </>
          )}
        </nav>
      </div>
    </header>
  )
}

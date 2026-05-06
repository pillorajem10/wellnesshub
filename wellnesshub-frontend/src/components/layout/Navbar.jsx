import { useEffect, useMemo, useState } from 'react'
import { createPortal } from 'react-dom'
import { Link, NavLink, useNavigate } from 'react-router-dom'
import { Leaf, LogOut, Menu, PlusCircle, User, X } from 'lucide-react'
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
  const [isMobileMenuMounted, setIsMobileMenuMounted] = useState(false)
  const [isMobileMenuVisible, setIsMobileMenuVisible] = useState(false)

  const handleLogout = async () => {
    await logout()
    setIsMobileMenuVisible(false)
    navigate('/login')
  }

  const navItems = useMemo(() => {
    const items = [
      { to: '/protocols', label: 'Protocols' },
      { to: '/threads', label: 'Threads' },
    ]

    if (isAuthenticated) {
      items.push({
        to: '/protocols/create',
        label: 'Create Protocol',
        icon: PlusCircle,
      })
    } else {
      items.push({ to: '/login', label: 'Login' })
      items.push({ to: '/register', label: 'Register' })
    }

    return items
  }, [isAuthenticated])

  const openMobileMenu = () => {
    setIsMobileMenuMounted(true)
    // Allow initial off-screen state to paint before sliding in.
    requestAnimationFrame(() => setIsMobileMenuVisible(true))
  }

  const closeMobileMenu = () => {
    setIsMobileMenuVisible(false)
  }

  useEffect(() => {
    if (!isMobileMenuMounted) return

    const onKeyDown = (event) => {
      if (event.key === 'Escape') closeMobileMenu()
    }

    document.addEventListener('keydown', onKeyDown)
    document.body.style.overflow = 'hidden'

    return () => {
      document.removeEventListener('keydown', onKeyDown)
      document.body.style.overflow = ''
    }
  }, [isMobileMenuMounted])

  useEffect(() => {
    if (isMobileMenuVisible) return
    if (!isMobileMenuMounted) return

    const timeout = window.setTimeout(() => {
      setIsMobileMenuMounted(false)
    }, 200)

    return () => window.clearTimeout(timeout)
  }, [isMobileMenuMounted, isMobileMenuVisible])

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

        <nav className="hidden flex-wrap items-center gap-4 text-sm md:flex">
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

        <button
          type="button"
          aria-label="Open menu"
          className="inline-flex items-center justify-center rounded-md border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700 md:hidden"
          onClick={openMobileMenu}
        >
          <Menu className="h-5 w-5" />
        </button>
      </div>

      {isMobileMenuMounted
        ? createPortal(
            <div className="fixed inset-0 z-[9999] md:hidden">
              <button
                type="button"
                aria-label="Close menu overlay"
                className={`fixed inset-0 bg-slate-900/40 transition-opacity duration-200 ${
                  isMobileMenuVisible ? 'opacity-100' : 'opacity-0'
                }`}
                onClick={closeMobileMenu}
              />

              <aside
                className={`fixed inset-y-0 left-0 z-[10000] w-72 max-w-[80vw] border-r border-slate-200 bg-white shadow-xl transition-transform duration-200 ease-out ${
                  isMobileMenuVisible ? 'translate-x-0' : '-translate-x-full'
                }`}
                role="dialog"
                aria-modal="true"
              >
                <div className="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                  <div className="flex flex-col items-start gap-1">
                    <span className="inline-flex items-center gap-2 text-base font-bold tracking-tight text-slate-950">
                      <Leaf className="h-5 w-5 text-emerald-600" />
                      WellnessHub
                    </span>
                    <span className="text-sm font-semibold text-slate-900">Menu</span>
                  </div>
                  <button
                    type="button"
                    aria-label="Close menu"
                    className="rounded-md p-2 text-slate-600 transition hover:bg-slate-50 hover:text-slate-900"
                    onClick={closeMobileMenu}
                  >
                    <X className="h-5 w-5" />
                  </button>
                </div>

                <div className="space-y-3 px-5 py-4 text-sm">
                  {isAuthenticated ? (
                    <div className="inline-flex items-center gap-2 rounded-xl bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700">
                      <User className="h-4 w-4" />
                      {user.firstName || 'User'}
                    </div>
                  ) : null}

                  <div className="flex flex-col gap-2">
                    {navItems.map((item) => {
                      const Icon = item.icon
                      return (
                        <NavLink
                          key={item.to}
                          to={item.to}
                          className={({ isActive }) =>
                            isActive
                              ? 'inline-flex w-full items-center justify-start gap-3 rounded-lg bg-emerald-50 px-3 py-2 text-left font-semibold text-emerald-700'
                              : 'inline-flex w-full items-center justify-start gap-3 rounded-lg px-3 py-2 text-left text-slate-600 transition hover:bg-slate-50 hover:text-emerald-700'
                          }
                          onClick={closeMobileMenu}
                        >
                          {Icon ? <Icon className="h-4 w-4" /> : null}
                          {item.label}
                        </NavLink>
                      )
                    })}

                    {isAuthenticated ? (
                      <Button
                        variant="danger"
                        size="sm"
                        aria-label="Logout"
                        title="Logout"
                        onClick={handleLogout}
                        className="mt-2 w-full justify-start"
                      >
                        <LogOut className="h-5 w-5" />
                        <span className="ml-2">Logout</span>
                      </Button>
                    ) : null}
                  </div>
                </div>
              </aside>
            </div>,
            document.body
          )
        : null}
    </header>
  )
}

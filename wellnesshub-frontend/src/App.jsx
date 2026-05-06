import { Route, Routes } from 'react-router-dom'
import ProtectedRoute from '@/components/auth/ProtectedRoute'
import PublicOnlyRoute from '@/components/auth/PublicOnlyRoute'
import AppLayout from '@/components/layout/AppLayout'
import HomePage from '@/pages/HomePage'
import LoginPage from '@/pages/LoginPage'
import RegisterPage from '@/pages/RegisterPage'
import ProtocolsPage from '@/pages/ProtocolsPage'
import ProtocolDetailPage from '@/pages/ProtocolDetailPage'
import ThreadsPage from '@/pages/ThreadsPage'
import ThreadDetailPage from '@/pages/ThreadDetailPage'
import CreateProtocolPage from '@/pages/CreateProtocolPage'
import CreateThreadPage from '@/pages/CreateThreadPage'
import NotFoundPage from '@/pages/NotFoundPage'

export default function App() {
  return (
    <AppLayout>
      <Routes>
        <Route path="/" element={<HomePage />} />
        <Route
          path="/login"
          element={
            <PublicOnlyRoute>
              <LoginPage />
            </PublicOnlyRoute>
          }
        />
        <Route
          path="/register"
          element={
            <PublicOnlyRoute>
              <RegisterPage />
            </PublicOnlyRoute>
          }
        />
        <Route path="/protocols" element={<ProtocolsPage />} />
        <Route path="/protocols/:id" element={<ProtocolDetailPage />} />
        <Route path="/threads" element={<ThreadsPage />} />
        <Route path="/threads/:id" element={<ThreadDetailPage />} />
        <Route
          path="/protocols/create"
          element={
            <ProtectedRoute>
              <CreateProtocolPage />
            </ProtectedRoute>
          }
        />
        <Route
          path="/protocols/:id/threads/create"
          element={
            <ProtectedRoute>
              <CreateThreadPage />
            </ProtectedRoute>
          }
        />
        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </AppLayout>
  )
}

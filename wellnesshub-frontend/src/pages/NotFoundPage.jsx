import { Link } from 'react-router-dom'
import Card from '@/components/common/Card'

// NotFoundPage provides a friendly fallback when a route is missing.
export default function NotFoundPage() {
  return (
    <Card className="mx-auto max-w-lg p-8 text-center">
      <h1 className="text-3xl font-bold text-slate-900">404</h1>
      <p className="mt-2 text-slate-600">The page you are looking for does not exist.</p>
      <Link to="/" className="mt-4 inline-block text-sm font-medium text-emerald-700">
        Go back home
      </Link>
    </Card>
  )
}

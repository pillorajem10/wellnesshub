import { HeartPulse } from 'lucide-react'

export default function Footer() {
  return (
    <footer className="border-t border-slate-200 bg-white/95 backdrop-blur">
      <div className="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-6 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
        <p>WellnessHub community protocol discussions</p>
        <p className="inline-flex items-center gap-2">
          <HeartPulse className="h-4 w-4 text-emerald-600" />
          Build better routines together
        </p>
      </div>
    </footer>
  )
}

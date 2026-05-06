import clsx from 'clsx'

export default function LoadingSpinner({ label = 'Loading...', inline = false, centered = true }) {
  return (
    <div
      className={clsx(
        inline
          ? 'inline-flex items-center gap-2'
          : 'flex items-center gap-2 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm',
        centered && 'justify-center'
      )}
    >
      <span className="h-4 w-4 animate-spin rounded-full border-2 border-emerald-200 border-t-emerald-600" />
      <span className="text-sm text-slate-600">{label}</span>
    </div>
  )
}

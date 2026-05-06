import clsx from 'clsx'

export default function Badge({ children, variant = 'emerald' }) {
  return (
    <span
      className={clsx(
        'rounded-full px-3 py-1 text-xs font-medium',
        variant === 'emerald' && 'bg-emerald-50 text-emerald-700',
        variant === 'slate' && 'bg-slate-100 text-slate-600'
      )}
    >
      #{children}
    </span>
  )
}

import clsx from 'clsx'

export default function Card({ children, className = '', hover = false }) {
  return (
    <div
      className={clsx(
        'rounded-2xl border border-slate-200 bg-white shadow-sm',
        hover && 'transition hover:-translate-y-0.5 hover:shadow-md',
        className
      )}
    >
      {children}
    </div>
  )
}

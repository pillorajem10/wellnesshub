import clsx from 'clsx'

const variantClass = {
  primary: 'bg-emerald-600 text-white shadow-sm hover:bg-emerald-700',
  secondary: 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50',
  ghost: 'text-slate-700 hover:bg-slate-100',
  danger: 'text-slate-500 hover:bg-red-50 hover:text-red-600',
}

const sizeClass = {
  sm: 'px-3 py-1.5 text-xs',
  md: 'px-4 py-2 text-sm',
  lg: 'px-5 py-2.5 text-sm',
}

export default function Button({
  children,
  className,
  variant = 'primary',
  size = 'md',
  loading = false,
  as: Component = 'button',
  type = 'button',
  disabled,
  ...props
}) {
  return (
    <Component
      type={Component === 'button' ? type : undefined}
      disabled={Component === 'button' ? disabled || loading : undefined}
      className={clsx(
        'inline-flex items-center justify-center gap-2 rounded-xl font-medium transition focus:outline-none focus:ring-2 focus:ring-emerald-500 disabled:cursor-not-allowed disabled:opacity-60',
        variantClass[variant],
        sizeClass[size],
        className
      )}
      {...props}
    >
      {loading ? 'Please wait...' : children}
    </Component>
  )
}

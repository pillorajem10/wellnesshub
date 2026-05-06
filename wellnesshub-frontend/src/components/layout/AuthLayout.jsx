import Card from '@/components/common/Card'

export default function AuthLayout({ title, subtitle, children }) {
  return (
    <div className="mx-auto w-full max-w-md">
      <Card className="p-6 sm:p-7">
        <h1 className="text-2xl font-bold tracking-tight text-slate-950">{title}</h1>
        {subtitle ? <p className="mt-1 text-sm text-slate-600">{subtitle}</p> : null}
        <div className="mt-6">{children}</div>
      </Card>
    </div>
  )
}

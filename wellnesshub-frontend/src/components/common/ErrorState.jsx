import Button from '@/components/common/Button'

export default function ErrorState({ message, retryLabel = 'Try again', onRetry }) {
  return (
    <div className="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
      <p>{message || 'Something went wrong.'}</p>
      {onRetry ? (
        <Button className="mt-3" variant="secondary" size="sm" onClick={onRetry}>
          {retryLabel}
        </Button>
      ) : null}
    </div>
  )
}

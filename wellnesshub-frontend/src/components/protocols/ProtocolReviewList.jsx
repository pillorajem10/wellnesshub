import Card from '@/components/common/Card'
import EmptyState from '@/components/common/EmptyState'
import { formatDate } from '@/utils/formatters'

export default function ProtocolReviewList({ reviews }) {
  if (!reviews.length) {
    return (
      <EmptyState title="No reviews yet." description="Be the first to share your experience." />
    )
  }

  return (
    <div className="space-y-3">
      {reviews.map((review) => (
        <Card key={review.id} className="p-4">
          <p className="text-sm font-semibold text-slate-900">
            {review.rating}/5 by {review.author.fullName}
          </p>
          <p className="mt-1 text-sm text-slate-700">{review.feedback}</p>
          <p className="mt-2 text-xs text-slate-500">{formatDate(review.createdAt)}</p>
        </Card>
      ))}
    </div>
  )
}

import { Link } from 'react-router-dom'
import { ArrowRight, MessageCircle, Sparkles } from 'lucide-react'
import Card from '@/components/common/Card'
import Badge from '@/components/common/Badge'
import { excerpt, formatDate } from '@/utils/formatters'

export default function ProtocolCard({ protocol }) {
  return (
    <Card hover className="p-5">
      <div className="flex items-start justify-between gap-3">
        <h3 className="text-lg font-bold tracking-tight text-slate-950">
          <Link to={`/protocols/${protocol.id}`} className="hover:text-emerald-700">
            {protocol.title}
          </Link>
        </h3>
        <Sparkles className="h-4 w-4 shrink-0 text-emerald-600" />
      </div>

      <p className="mt-2 text-sm leading-relaxed text-slate-600">
        {excerpt(protocol.content, 180)}
      </p>

      <div className="mt-3 flex flex-wrap gap-2">
        {protocol.tags.map((tag) => (
          <Badge key={tag}>{tag}</Badge>
        ))}
      </div>

      <div className="mt-4 flex items-center justify-between text-xs text-slate-500">
        <p>
          {protocol.avgRating} rating � {protocol.reviewsCount} reviews � {protocol.votesCount}{' '}
          votes
        </p>
        <p>{formatDate(protocol.createdAt)}</p>
      </div>
    </Card>
  )
}

import { Link } from 'react-router-dom'
import { MessageCircle, Vote } from 'lucide-react'
import Card from '@/components/common/Card'
import Badge from '@/components/common/Badge'
import { excerpt, formatDate } from '@/utils/formatters'

export default function ThreadCard({ thread }) {
  return (
    <Card hover className="p-5">
      <h3 className="text-lg font-bold tracking-tight text-slate-950">
        <Link to={`/threads/${thread.id}`} className="hover:text-emerald-700">
          {thread.title}
        </Link>
      </h3>

      <p className="mt-2 text-sm leading-relaxed text-slate-600">{excerpt(thread.body, 180)}</p>

      <div className="mt-3 flex flex-wrap gap-2">
        {thread.tags.map((tag) => (
          <Badge key={tag} variant="slate">
            {tag}
          </Badge>
        ))}
      </div>

      <div className="mt-4 flex items-center justify-between text-xs text-slate-500">
        <p className="inline-flex items-center gap-3">
          <span className="inline-flex items-center gap-1">
            <Vote className="h-3.5 w-3.5" />
            {thread.votesCount}
          </span>
          <span className="inline-flex items-center gap-1">
            <MessageCircle className="h-3.5 w-3.5" />
            {thread.commentsCount}
          </span>
        </p>
        <p>{formatDate(thread.createdAt)}</p>
      </div>
    </Card>
  )
}

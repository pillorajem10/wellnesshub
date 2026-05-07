import { Link } from 'react-router-dom'
import { MessageCircle, Vote } from 'lucide-react'
import { useEffect, useState } from 'react'
import Card from '@/components/common/Card'
import Badge from '@/components/common/Badge'
import VoteButtons from '@/components/threads/VoteButtons'
import { useAuth } from '@/hooks/useAuth'
import { excerpt, formatDate } from '@/utils/formatters'

export default function ThreadCard({ thread, onThreadUpdated }) {
  const { isAuthenticated } = useAuth()
  const [votesCount, setVotesCount] = useState(thread.votesCount)
  const [userVote, setUserVote] = useState(thread.userVote)

  useEffect(() => {
    setVotesCount(thread.votesCount)
    setUserVote(thread.userVote)
  }, [thread.id, thread.votesCount, thread.userVote])

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

      <div className="mt-4 flex items-center justify-between gap-4 text-xs text-slate-500">
        <div className="flex items-center gap-3">
          <div className="hidden sm:block">
            <VoteButtons
              votableType="thread"
              votableId={thread.id}
              initialCount={votesCount}
              initialUserVote={userVote}
              isAuthenticated={isAuthenticated}
              onVoted={({ votesCount: nextVotesCount, userVote: nextUserVote }) => {
                setVotesCount(nextVotesCount)
                setUserVote(nextUserVote)
                if (onThreadUpdated) {
                  onThreadUpdated(thread.id, {
                    votesCount: nextVotesCount,
                    userVote: nextUserVote,
                  })
                }
              }}
            />
          </div>

          <span className="inline-flex items-center gap-1">
            <Vote className="h-3.5 w-3.5" />
            {votesCount}
          </span>

          <span className="inline-flex items-center gap-1">
            <MessageCircle className="h-3.5 w-3.5" />
            {thread.commentsCount}
          </span>
        </div>

        <p className="shrink-0">{formatDate(thread.createdAt)}</p>
      </div>
    </Card>
  )
}

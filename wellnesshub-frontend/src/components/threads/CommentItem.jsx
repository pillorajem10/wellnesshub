import { useState } from 'react'
import Button from '@/components/common/Button'
import Card from '@/components/common/Card'
import CommentForm from '@/components/threads/CommentForm'
import VoteButtons from '@/components/threads/VoteButtons'

function getCommentIndentClass(depth) {
  if (depth <= 1) return ''
  if (depth === 2) return 'ml-4 border-l border-slate-200 pl-4'
  return 'ml-8 border-l border-emerald-100 pl-4'
}

export default function CommentItem({ comment, onReply, canReply, isAuthenticated, depth = 1 }) {
  const [showReplyForm, setShowReplyForm] = useState(false)

  const handleReplySubmit = async (payload) => {
    await onReply(payload, comment.id)
    setShowReplyForm(false)
  }

  const cappedDepth = Math.min(depth, 3)
  const cardClassName =
    cappedDepth === 1
      ? 'p-4 shadow-sm'
      : 'p-3 border border-slate-200/90 bg-slate-50/40 shadow-none'

  return (
    <div className={getCommentIndentClass(cappedDepth)}>
      <Card className={cardClassName}>
        <p className="text-sm font-medium text-slate-900">{comment.author.fullName}</p>
        <p className="mt-1 text-sm text-slate-700">{comment.body}</p>
        <div className="mt-2">
          <VoteButtons
            votableType="comment"
            votableId={comment.id}
            initialCount={comment.votesCount}
            initialUserVote={comment.userVote}
            isAuthenticated={isAuthenticated}
          />
        </div>

        {canReply ? (
          <div className="mt-3">
            <Button
              variant="ghost"
              className="px-0"
              onClick={() => setShowReplyForm((value) => !value)}
            >
              {showReplyForm ? 'Cancel' : 'Reply'}
            </Button>
          </div>
        ) : null}

        {showReplyForm ? (
          <div className="mt-3">
            <CommentForm parentId={comment.id} onSubmit={handleReplySubmit} />
          </div>
        ) : null}
      </Card>
    </div>
  )
}

import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { MessageCircle, User, Vote } from 'lucide-react'
import CommentForm from '@/components/threads/CommentForm'
import CommentTree from '@/components/threads/CommentTree'
import VoteButtons from '@/components/threads/VoteButtons'
import EmptyState from '@/components/common/EmptyState'
import ErrorState from '@/components/common/ErrorState'
import LoadingSpinner from '@/components/common/LoadingSpinner'
import Badge from '@/components/common/Badge'
import Card from '@/components/common/Card'
import { useAuth } from '@/hooks/useAuth'
import { commentService } from '@/services/commentService'
import { threadService } from '@/services/threadService'
import { unwrapApiData, unwrapError } from '@/utils/apiResponse'
import { normalizeComment, normalizeThread } from '@/utils/normalizers'

// ThreadDetailPage shows one discussion thread and its nested comments.
export default function ThreadDetailPage() {
  const { id } = useParams()
  const { isAuthenticated } = useAuth()

  const [thread, setThread] = useState(null)
  const [loading, setLoading] = useState(true)
  const [commentLoading, setCommentLoading] = useState(false)
  const [error, setError] = useState('')

  const loadThread = async () => {
    const response = await threadService.getThread(id)
    const payload = unwrapApiData(response)

    const rawThread = payload?.thread || payload
    const rawComments = Array.isArray(payload?.comments)
      ? payload.comments
      : Array.isArray(rawThread?.comments)
        ? rawThread.comments
        : []

    const normalizedThread = normalizeThread({
      ...rawThread,
      comments: rawComments,
    })

    setThread({
      ...normalizedThread,
      comments: rawComments.map(normalizeComment),
    })
  }

  useEffect(() => {
    const initializePage = async () => {
      setLoading(true)
      setError('')

      try {
        await loadThread()
      } catch (requestError) {
        setError(unwrapError(requestError))
      } finally {
        setLoading(false)
      }
    }

    initializePage()
  }, [id])

  // The API returns tbl_* column names, but Laravel validation expects clean request keys when creating or updating records.
  const handleCommentSubmit = async ({ body, parent_id }) => {
    setCommentLoading(true)
    setError('')

    try {
      const payload = {
        thread_id: Number(id),
        body,
      }

      if (parent_id) payload.parent_id = Number(parent_id)

      await commentService.createComment(payload)
      await loadThread()
    } catch (requestError) {
      setError(unwrapError(requestError))
    } finally {
      setCommentLoading(false)
    }
  }

  if (loading) return <LoadingSpinner label="Loading thread details..." />
  if (error) return <ErrorState message={error} />
  if (!thread)
    return <EmptyState title="Thread not found." description="It may have been deleted." />

  return (
    <div className="space-y-6">
      <Card className="p-6">
        <h1 className="text-3xl font-bold tracking-tight text-slate-950">{thread.title}</h1>
        <p className="mt-2 inline-flex items-center gap-2 text-sm text-slate-600">
          <User className="h-4 w-4" />
          {thread.author.fullName}
        </p>

        <div className="mt-3 flex flex-wrap gap-2">
          {thread.tags.map((tag) => (
            <Badge key={tag} variant="slate">
              {tag}
            </Badge>
          ))}
        </div>

        <p className="mt-4 whitespace-pre-wrap leading-relaxed text-slate-700">{thread.body}</p>

        {thread.protocol ? (
          <Link
            to={`/protocols/${thread.protocol.id}`}
            className="mt-4 inline-block text-sm font-medium text-emerald-700"
          >
            Back to protocol: {thread.protocol.title}
          </Link>
        ) : null}

        <div className="mt-4 flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
          <VoteButtons
            votableType="thread"
            votableId={thread.id}
            initialCount={thread.votesCount}
            initialUserVote={thread.userVote}
            isAuthenticated={isAuthenticated}
          />
          <p className="text-sm text-slate-600">
            <span className="inline-flex items-center gap-1">
              <MessageCircle className="h-4 w-4" />
              {thread.commentsCount} comments
            </span>
          </p>
        </div>
      </Card>

      <section className="space-y-3">
        <h2 className="text-xl font-bold tracking-tight text-slate-950">Comments</h2>

        {isAuthenticated ? (
          <Card className="p-4">
            <CommentForm onSubmit={handleCommentSubmit} loading={commentLoading} />
          </Card>
        ) : (
          <p className="text-sm text-slate-600">Log in to join the discussion.</p>
        )}

        <CommentTree
          comments={thread.comments}
          onReply={handleCommentSubmit}
          canReply={isAuthenticated}
          isAuthenticated={isAuthenticated}
        />
      </section>
    </div>
  )
}

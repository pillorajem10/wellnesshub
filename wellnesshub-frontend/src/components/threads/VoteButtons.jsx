import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { ArrowBigDown, ArrowBigUp } from 'lucide-react'
import { voteService } from '@/services/voteService'
import { unwrapError } from '@/utils/apiResponse'

export default function VoteButtons({
  votableType,
  votableId,
  initialCount = 0,
  initialUserVote = null,
  onVoted,
  isAuthenticated,
}) {
  const navigate = useNavigate()
  const [count, setCount] = useState(Number(initialCount || 0))
  const [userVote, setUserVote] = useState(initialUserVote ?? null)
  const [isVoting, setIsVoting] = useState(false)
  const [error, setError] = useState('')

  const submitVote = async (value) => {
    if (!isAuthenticated) {
      navigate('/login')
      return
    }

    setIsVoting(true)
    setError('')

    try {
      const response = await voteService.vote({
        votable_type: votableType,
        votable_id: Number(votableId),
        value,
      })

      // The backend owns the toggle result, so the UI updates from votes_count and user_vote returned by the API.
      const payload = response?.data?.data || response?.data || response
      const nextCount = Number(
        payload?.votes_count ?? response?.data?.votes_count ?? response?.votes_count ?? count
      )
      const nextUserVote =
        payload?.user_vote ?? response?.data?.user_vote ?? response?.user_vote ?? null

      setCount(nextCount)
      setUserVote(nextUserVote)

      if (onVoted) {
        onVoted({
          votesCount: nextCount,
          userVote: nextUserVote,
        })
      }
    } catch (requestError) {
      setError(unwrapError(requestError))
    } finally {
      setIsVoting(false)
    }
  }

  return (
    <div className="space-y-1">
      <div className="flex items-center gap-2">
        <button
          type="button"
          onClick={() => submitVote(1)}
          disabled={isVoting}
          aria-label="Upvote"
          className={`rounded-md border p-1 transition ${
            userVote === 1
              ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
              : 'border-transparent text-slate-500 hover:bg-slate-50 hover:text-emerald-700'
          } ${isVoting ? 'opacity-60' : ''}`}
        >
          <ArrowBigUp size={18} />
        </button>
        <span className="text-sm font-semibold">{count}</span>
        <button
          type="button"
          onClick={() => submitVote(-1)}
          disabled={isVoting}
          aria-label="Downvote"
          className={`rounded-md border p-1 transition ${
            userVote === -1
              ? 'border-red-200 bg-red-50 text-red-600'
              : 'border-transparent text-slate-500 hover:bg-slate-50 hover:text-red-600'
          } ${isVoting ? 'opacity-60' : ''}`}
        >
          <ArrowBigDown size={18} />
        </button>
      </div>
      {error ? <p className="text-xs text-slate-500">{error}</p> : null}
    </div>
  )
}

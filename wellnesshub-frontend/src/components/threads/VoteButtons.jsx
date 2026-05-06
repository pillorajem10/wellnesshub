import { useEffect, useMemo, useRef, useState } from 'react'
import { useNavigate } from 'react-router-dom'
import { ArrowBigDown, ArrowBigUp } from 'lucide-react'
import { voteService } from '@/services/voteService'
import { unwrapError } from '@/utils/apiResponse'

function normalizeVoteValue(value) {
  const parsed = Number(value)
  if (parsed === 1) return 1
  if (parsed === -1) return -1
  return null
}

function getNextUserVote(currentUserVote, clickedValue) {
  const current = normalizeVoteValue(currentUserVote)
  const clicked = normalizeVoteValue(clickedValue)
  if (!clicked) return current
  return current === clicked ? null : clicked
}

export default function VoteButtons({
  votableType,
  votableId,
  initialCount = 0,
  initialUserVote = null,
  onVoted,
  isAuthenticated,
}) {
  const navigate = useNavigate()
  const safeInitialCount = useMemo(() => Number(initialCount || 0), [initialCount])
  const safeInitialUserVote = useMemo(
    () => normalizeVoteValue(initialUserVote),
    [initialUserVote]
  )

  const [count, setCount] = useState(safeInitialCount)
  const [userVote, setUserVote] = useState(safeInitialUserVote)
  const [isVoting, setIsVoting] = useState(false)
  const [error, setError] = useState('')

  const lastHydratedRef = useRef({
    votableId: votableId,
    count: safeInitialCount,
    userVote: safeInitialUserVote,
  })

  // Hydrate from props when:
  // - switching to a different votable id, OR
  // - parent data genuinely changes AND our local state still matches the last hydrated snapshot
  //   (prevents overwriting the just-clicked vote state with stale props after the request settles).
  useEffect(() => {
    const last = lastHydratedRef.current
    const isNewItem = last.votableId !== votableId
    const parentChanged =
      last.count !== safeInitialCount || last.userVote !== safeInitialUserVote
    const localMatchesLast = count === last.count && userVote === last.userVote

    if (isNewItem || (parentChanged && localMatchesLast)) {
      setCount(safeInitialCount)
      setUserVote(safeInitialUserVote)
      lastHydratedRef.current = {
        votableId,
        count: safeInitialCount,
        userVote: safeInitialUserVote,
      }
    }
  }, [votableId, safeInitialCount, safeInitialUserVote, count, userVote])

  const submitVote = async (value) => {
    if (!isAuthenticated) {
      navigate('/login')
      return
    }

    if (isVoting) return

    const previous = { count, userVote }
    const nextUserVote = getNextUserVote(userVote, value)
    const delta = (nextUserVote ?? 0) - (userVote ?? 0)

    // Optimistic update
    setCount((current) => current + delta)
    setUserVote(nextUserVote)

    setIsVoting(true)
    setError('')

    try {
      const response = await voteService.vote({
        votable_type: votableType,
        votable_id: Number(votableId),
        value: Number(value),
      })

      // API response is the source of truth after success.
      const payload = response?.data?.data || response?.data || response
      const nextCount = Number(
        payload?.votes_count ?? response?.data?.votes_count ?? response?.votes_count ?? previous.count
      )
      const serverUserVote = normalizeVoteValue(
        payload?.current_user_vote ?? payload?.user_vote ?? null
      )

      setCount(nextCount)
      setUserVote(serverUserVote)
      lastHydratedRef.current = {
        votableId,
        count: nextCount,
        userVote: serverUserVote,
      }

      if (onVoted) {
        onVoted({
          votesCount: nextCount,
          userVote: serverUserVote,
          upvotesCount: payload?.upvotes_count ?? null,
          downvotesCount: payload?.downvotes_count ?? null,
        })
      }
    } catch (requestError) {
      setError(unwrapError(requestError))
      // Rollback optimistic update
      setCount(previous.count)
      setUserVote(previous.userVote)
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

import { Link } from 'react-router-dom'
import EmptyState from '@/components/common/EmptyState'

export default function RelatedThreads({ threads }) {
  if (!threads.length) {
    return (
      <EmptyState
        title="No related threads yet."
        description="Start a discussion for this protocol."
      />
    )
  }

  return (
    <div className="space-y-2">
      {threads.map((thread) => (
        <Link
          key={thread.id}
          to={`/threads/${thread.id}`}
          className="block rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-700 hover:bg-slate-50"
        >
          {thread.title}
        </Link>
      ))}
    </div>
  )
}

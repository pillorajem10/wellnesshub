import EmptyState from '@/components/common/EmptyState'
import CommentItem from '@/components/threads/CommentItem'

function flattenReplies(replies = []) {
  // After the third level, replies stay visually flat so long conversations remain readable.
  const result = []

  replies.forEach((reply) => {
    result.push(reply)

    if (Array.isArray(reply.replies) && reply.replies.length > 0) {
      result.push(...flattenReplies(reply.replies))
    }
  })

  return result
}

export default function CommentTree({ comments, onReply, canReply, isAuthenticated }) {
  const safeComments = Array.isArray(comments) ? comments : []

  if (!safeComments.length) {
    return <EmptyState title="No comments yet." description="Start the conversation below." />
  }

  return (
    <div className="space-y-3">
      {safeComments.map((rootComment) => {
        const levelTwoReplies = Array.isArray(rootComment.replies) ? rootComment.replies : []

        return (
          <div key={rootComment.id} className="space-y-3">
            <CommentItem
              comment={rootComment}
              depth={1}
              onReply={onReply}
              canReply={canReply}
              isAuthenticated={isAuthenticated}
            />

            {levelTwoReplies.map((levelTwoReply) => {
              const levelThreeReplies = flattenReplies(levelTwoReply.replies)

              return (
                <div key={levelTwoReply.id} className="space-y-3">
                  <CommentItem
                    comment={levelTwoReply}
                    depth={2}
                    onReply={onReply}
                    canReply={canReply}
                    isAuthenticated={isAuthenticated}
                  />

                  {levelThreeReplies.map((levelThreeReply) => (
                    <CommentItem
                      key={levelThreeReply.id}
                      comment={levelThreeReply}
                      depth={3}
                      onReply={onReply}
                      canReply={canReply}
                      isAuthenticated={isAuthenticated}
                    />
                  ))}
                </div>
              )
            })}
          </div>
        )
      })}
    </div>
  )
}

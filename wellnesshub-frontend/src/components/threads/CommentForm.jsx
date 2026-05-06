import { useState } from 'react'
import Button from '@/components/common/Button'
import Textarea from '@/components/common/Textarea'

export default function CommentForm({ onSubmit, loading, parentId = null }) {
  const [body, setBody] = useState('')

  const handleSubmit = (event) => {
    event.preventDefault()
    if (!body.trim()) return

    onSubmit({
      body,
      parent_id: parentId,
    })

    setBody('')
  }

  return (
    <form className="space-y-2" onSubmit={handleSubmit}>
      <Textarea
        id={parentId ? `reply-${parentId}` : 'new-comment'}
        label={parentId ? 'Reply' : 'Comment'}
        rows={3}
        value={body}
        onChange={(event) => setBody(event.target.value)}
      />
      <Button type="submit" disabled={loading || !body.trim()}>
        {loading ? 'Posting...' : parentId ? 'Reply' : 'Post Comment'}
      </Button>
    </form>
  )
}

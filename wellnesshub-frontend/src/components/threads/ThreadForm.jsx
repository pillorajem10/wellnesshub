import { useState } from 'react'
import Button from '@/components/common/Button'
import Input from '@/components/common/Input'
import Textarea from '@/components/common/Textarea'

export default function ThreadForm({ onSubmit, loading }) {
  const [title, setTitle] = useState('')
  const [body, setBody] = useState('')
  const [tags, setTags] = useState('')

  const handleSubmit = (event) => {
    event.preventDefault()
    onSubmit({ title, body, tags })
  }

  return (
    <form className="space-y-4" onSubmit={handleSubmit}>
      <Input
        id="thread-title"
        label="Title"
        value={title}
        onChange={(event) => setTitle(event.target.value)}
        required
      />
      <Textarea
        id="thread-body"
        label="Body"
        rows={7}
        value={body}
        onChange={(event) => setBody(event.target.value)}
        required
      />
      <Input
        id="thread-tags"
        label="Tags (comma-separated)"
        value={tags}
        onChange={(event) => setTags(event.target.value)}
        placeholder="sleep, experience"
      />
      <Button type="submit" disabled={loading}>
        {loading ? 'Creating...' : 'Create Thread'}
      </Button>
    </form>
  )
}

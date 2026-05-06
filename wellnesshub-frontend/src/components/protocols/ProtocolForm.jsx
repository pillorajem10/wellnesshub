import { useState } from 'react'
import Button from '@/components/common/Button'
import Input from '@/components/common/Input'
import Textarea from '@/components/common/Textarea'

export default function ProtocolForm({ onSubmit, loading }) {
  const [title, setTitle] = useState('')
  const [content, setContent] = useState('')
  const [tags, setTags] = useState('')

  const handleSubmit = (event) => {
    event.preventDefault()
    onSubmit({ title, content, tags })
  }

  return (
    <form className="space-y-4" onSubmit={handleSubmit}>
      <Input
        id="protocol-title"
        label="Title"
        value={title}
        onChange={(event) => setTitle(event.target.value)}
        required
      />
      <Textarea
        id="protocol-content"
        label="Content"
        rows={7}
        value={content}
        onChange={(event) => setContent(event.target.value)}
        required
      />
      <Input
        id="protocol-tags"
        label="Tags (comma-separated)"
        value={tags}
        onChange={(event) => setTags(event.target.value)}
        placeholder="sleep, recovery, circadian-rhythm"
      />
      <Button type="submit" disabled={loading}>
        {loading ? 'Creating...' : 'Create Protocol'}
      </Button>
    </form>
  )
}

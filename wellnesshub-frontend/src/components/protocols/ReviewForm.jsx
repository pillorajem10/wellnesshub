import { useState } from 'react'
import Button from '@/components/common/Button'
import Select from '@/components/common/Select'
import Textarea from '@/components/common/Textarea'

export default function ReviewForm({ onSubmit, loading }) {
  const [rating, setRating] = useState('5')
  const [feedback, setFeedback] = useState('')

  const handleSubmit = (event) => {
    event.preventDefault()
    onSubmit({ rating: Number(rating), feedback })
    setFeedback('')
  }

  return (
    <form className="space-y-3" onSubmit={handleSubmit}>
      <Select
        id="review-rating"
        label="Rating"
        value={rating}
        onChange={(event) => setRating(event.target.value)}
        options={[1, 2, 3, 4, 5].map((value) => ({ value: String(value), label: String(value) }))}
      />
      <Textarea
        id="review-feedback"
        label="Feedback"
        rows={4}
        value={feedback}
        onChange={(event) => setFeedback(event.target.value)}
        required
      />
      <Button type="submit" disabled={loading}>
        {loading ? 'Submitting...' : 'Submit Review'}
      </Button>
    </form>
  )
}

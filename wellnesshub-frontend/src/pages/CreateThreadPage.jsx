import { useState } from 'react'
import { useNavigate, useParams } from 'react-router-dom'
import ErrorState from '@/components/common/ErrorState'
import ThreadForm from '@/components/threads/ThreadForm'
import { threadService } from '@/services/threadService'
import { unwrapApiData, unwrapError } from '@/utils/apiResponse'

// CreateThreadPage lets authenticated users create a thread under a specific protocol.
export default function CreateThreadPage() {
  const navigate = useNavigate()
  const { id } = useParams()

  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  // The API returns tbl_* column names, but Laravel validation expects clean request keys when creating or updating records.
  const handleSubmit = async ({ title, body, tags }) => {
    setLoading(true)
    setError('')

    try {
      const payload = {
        protocol_id: Number(id),
        title,
        body,
        tags: tags
          .split(',')
          .map((tag) => tag.trim())
          .filter(Boolean),
      }

      const response = await threadService.createThread(payload)
      const data = unwrapApiData(response)
      const createdId = data?.tbl_thread_id || data?.id

      if (createdId) {
        navigate(`/threads/${createdId}`)
      } else {
        navigate(`/protocols/${id}`)
      }
    } catch (requestError) {
      setError(unwrapError(requestError))
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-semibold text-slate-900">Create Thread</h1>
      {error ? <ErrorState message={error} /> : null}
      <ThreadForm onSubmit={handleSubmit} loading={loading} />
    </div>
  )
}

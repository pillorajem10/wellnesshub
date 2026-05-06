import { useState } from 'react'
import { useNavigate } from 'react-router-dom'
import ErrorState from '@/components/common/ErrorState'
import ProtocolForm from '@/components/protocols/ProtocolForm'
import { protocolService } from '@/services/protocolService'
import { unwrapApiData, unwrapError } from '@/utils/apiResponse'

// CreateProtocolPage handles the protected flow for submitting a new protocol.
export default function CreateProtocolPage() {
  const navigate = useNavigate()
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  // The API returns tbl_* column names, but Laravel validation expects clean request keys when creating or updating records.
  const handleSubmit = async ({ title, content, tags }) => {
    setLoading(true)
    setError('')

    try {
      const payload = {
        title,
        content,
        tags: tags
          .split(',')
          .map((tag) => tag.trim())
          .filter(Boolean),
      }

      const response = await protocolService.createProtocol(payload)
      const data = unwrapApiData(response)
      const createdId = data?.tbl_protocol_id || data?.id

      if (createdId) {
        navigate(`/protocols/${createdId}`)
      } else {
        navigate('/protocols')
      }
    } catch (requestError) {
      setError(unwrapError(requestError))
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="space-y-4">
      <h1 className="text-2xl font-semibold text-slate-900">Create Protocol</h1>
      {error ? <ErrorState message={error} /> : null}
      <ProtocolForm onSubmit={handleSubmit} loading={loading} />
    </div>
  )
}

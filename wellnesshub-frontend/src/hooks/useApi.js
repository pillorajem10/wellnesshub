import { useCallback, useState } from 'react'
import { unwrapError } from '@/utils/apiResponse'

export function useApi(requestFn) {
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState('')

  const run = useCallback(
    async (...args) => {
      setLoading(true)
      setError('')

      try {
        return await requestFn(...args)
      } catch (requestError) {
        setError(unwrapError(requestError))
        throw requestError
      } finally {
        setLoading(false)
      }
    },
    [requestFn]
  )

  return { run, loading, error, setError }
}

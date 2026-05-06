// List endpoints return items, while search endpoints return hits, so this helper supports both shapes.

export function unwrapApiData(response) {
  if (response?.data?.data !== undefined) return response.data.data
  if (response?.data !== undefined) return response.data

  return response
}

export function unwrapList(response) {
  const possibleLists = [
    response?.data?.data?.items,
    response?.data?.data?.hits,
    response?.data?.items,
    response?.data?.hits,
    response?.data?.data?.data,
    response?.data?.data,
    response?.data,
    response,
  ]

  const list = possibleLists.find(Array.isArray)
  return list || []
}

export function unwrapPagination(response) {
  const meta =
    response?.data?.data?.meta ||
    response?.data?.meta ||
    response?.data?.data ||
    response?.data ||
    {}

  return {
    currentPage: Number(meta.current_page ?? meta.currentPage ?? 1),
    lastPage: Number(meta.last_page ?? meta.lastPage ?? 1),
    perPage: Number(meta.per_page ?? meta.perPage ?? 0),
    total: Number(meta.total ?? 0),
  }
}

export function unwrapError(error) {
  if (!error?.response) {
    return 'Network error. Please check your connection and try again.'
  }

  const status = error.response.status
  const data = error.response.data || {}

  if (status === 422 && data.errors) {
    const firstValidation = Object.values(data.errors)[0]
    if (Array.isArray(firstValidation) && firstValidation[0]) return firstValidation[0]
  }

  if (data.message) return data.message

  if (status === 401) return 'You need to log in to continue.'
  if (status === 403) return 'You do not have permission to do this action.'
  if (status === 404) return 'The requested resource was not found.'
  if (status >= 500) return 'The server is having trouble right now. Please try again.'

  return 'Something went wrong. Please try again.'
}

export const formatDate = (date) => {
  if (!date) return ''

  const numericDate = Number(date)

  if (!Number.isNaN(numericDate)) {
    const timestamp =
      String(Math.trunc(numericDate)).length <= 10 ? numericDate * 1000 : numericDate
    const parsedNumeric = new Date(timestamp)
    if (!Number.isNaN(parsedNumeric.getTime())) {
      return new Intl.DateTimeFormat('en', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
      }).format(parsedNumeric)
    }
  }

  const parsed = new Date(date)
  if (Number.isNaN(parsed.getTime())) return ''

  return new Intl.DateTimeFormat('en', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }).format(parsed)
}

export const formatDateTime = (date) => {
  if (!date) return ''

  const numericDate = Number(date)

  if (!Number.isNaN(numericDate)) {
    const timestamp =
      String(Math.trunc(numericDate)).length <= 10 ? numericDate * 1000 : numericDate
    const parsedNumeric = new Date(timestamp)
    if (!Number.isNaN(parsedNumeric.getTime())) {
      return new Intl.DateTimeFormat('en', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
      }).format(parsedNumeric)
    }
  }

  const parsed = new Date(date)
  if (Number.isNaN(parsed.getTime())) return ''

  return new Intl.DateTimeFormat('en', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  }).format(parsed)
}

export const excerpt = (text, length = 160) => {
  if (!text) return ''
  return text.length > length ? text.slice(0, length).trim() + '...' : text
}

export const normalizeTags = (tags) => {
  if (!tags) return []
  if (Array.isArray(tags)) return tags.filter(Boolean)
  if (typeof tags === 'string') {
    try {
      const parsed = JSON.parse(tags)
      if (Array.isArray(parsed)) return parsed.filter(Boolean)
    } catch {
      return tags
        .split(',')
        .map((tag) => tag.trim())
        .filter(Boolean)
    }
  }
  return []
}

export const getUserDisplayName = (user) => {
  if (!user) return 'Anonymous'
  const fname = user.tbl_user_fname || user.fname || ''
  const lname = user.tbl_user_lname || user.lname || ''
  const full = (fname + ' ' + lname).trim()
  return full || user.tbl_user_email || user.email || 'Anonymous'
}

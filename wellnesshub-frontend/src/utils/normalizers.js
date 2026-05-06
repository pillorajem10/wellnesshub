export function normalizeTags(tags) {
  if (!tags) return []
  if (Array.isArray(tags)) return tags.filter(Boolean)

  if (typeof tags === 'string') {
    const trimmed = tags.trim()
    if (!trimmed) return []

    try {
      const parsed = JSON.parse(trimmed)
      if (Array.isArray(parsed)) return parsed.filter(Boolean)
    } catch {
      return trimmed
        .split(',')
        .map((tag) => tag.trim())
        .filter(Boolean)
    }
  }

  return []
}

export function normalizeUser(user) {
  if (typeof user === 'string') {
    return {
      id: null,
      firstName: user,
      lastName: '',
      fullName: user,
      email: '',
    }
  }

  if (!user) {
    return {
      id: null,
      firstName: '',
      lastName: '',
      fullName: 'Anonymous',
      email: '',
    }
  }

  const firstName = user.tbl_user_fname || user.fname || ''
  const lastName = user.tbl_user_lname || user.lname || ''
  const fullName =
    `${firstName} ${lastName}`.trim() || user.tbl_user_email || user.email || 'Anonymous'

  return {
    id: user.tbl_user_id ?? user.id ?? null,
    firstName,
    lastName,
    fullName,
    email: user.tbl_user_email || user.email || '',
  }
}

export function normalizeReview(review) {
  if (!review) {
    return {
      id: null,
      protocolId: null,
      authorId: null,
      rating: 0,
      feedback: '',
      createdAt: '',
      updatedAt: '',
      author: normalizeUser(null),
    }
  }

  return {
    id: review.tbl_review_id ?? review.id ?? null,
    protocolId: review.tbl_review_protocol_id ?? review.protocol_id ?? null,
    authorId: review.tbl_review_author_id ?? review.author_id ?? null,
    rating: Number(review.tbl_review_rating ?? review.rating ?? 0),
    feedback: review.tbl_review_feedback ?? review.feedback ?? '',
    createdAt: review.tbl_review_created_at ?? review.created_at ?? '',
    updatedAt: review.tbl_review_updated_at ?? review.updated_at ?? '',
    author: normalizeUser(review.author),
  }
}

export function normalizeComment(comment) {
  if (!comment) {
    return {
      id: null,
      threadId: null,
      authorId: null,
      parentId: null,
      body: '',
      votesCount: 0,
      userVote: null,
      createdAt: '',
      updatedAt: '',
      author: normalizeUser(null),
      replies: [],
    }
  }

  const replies = Array.isArray(comment.replies) ? comment.replies : []

  return {
    id: comment.tbl_comment_id ?? comment.id ?? null,
    threadId: comment.tbl_comment_thread_id ?? comment.thread_id ?? null,
    authorId: comment.tbl_comment_author_id ?? comment.author_id ?? null,
    parentId: comment.tbl_comment_parent_id ?? comment.parent_id ?? null,
    body: comment.tbl_comment_body ?? comment.body ?? '',
    votesCount: Number(comment.tbl_comment_votes_count ?? comment.votes_count ?? 0),
    userVote: comment.user_vote ?? comment.current_user_vote ?? comment.tbl_user_vote ?? null,
    createdAt: comment.tbl_comment_created_at ?? comment.created_at ?? '',
    updatedAt: comment.tbl_comment_updated_at ?? comment.updated_at ?? '',
    author: normalizeUser(comment.author),
    replies: replies.map(normalizeComment),
  }
}

export function normalizeProtocol(protocol) {
  if (!protocol) {
    return {
      id: null,
      title: '',
      slug: '',
      content: '',
      tags: [],
      avgRating: 0,
      reviewsCount: 0,
      votesCount: 0,
      createdAt: '',
      updatedAt: '',
      author: normalizeUser(null),
      threads: [],
      reviews: [],
    }
  }

  return {
    id: Number(protocol.tbl_protocol_id ?? protocol.id ?? 0) || null,
    title: protocol.tbl_protocol_title ?? protocol.title ?? 'Untitled protocol',
    slug: protocol.tbl_protocol_slug ?? protocol.slug ?? '',
    content: protocol.tbl_protocol_content ?? protocol.content ?? protocol.body ?? '',
    tags: normalizeTags(protocol.tbl_protocol_tags ?? protocol.tags),
    avgRating: Number(protocol.tbl_protocol_avg_rating ?? protocol.avg_rating ?? 0),
    reviewsCount: Number(protocol.tbl_protocol_reviews_count ?? protocol.reviews_count ?? 0),
    votesCount: Number(protocol.tbl_protocol_votes_count ?? protocol.votes_count ?? 0),
    createdAt: protocol.tbl_protocol_created_at ?? protocol.created_at ?? '',
    updatedAt: protocol.tbl_protocol_updated_at ?? protocol.updated_at ?? '',
    author: normalizeUser(protocol.author),
    threads: Array.isArray(protocol.threads) ? protocol.threads.map(normalizeThread) : [],
    reviews: Array.isArray(protocol.reviews) ? protocol.reviews.map(normalizeReview) : [],
  }
}

export function normalizeThread(thread) {
  if (!thread) {
    return {
      id: null,
      protocolId: null,
      title: '',
      body: '',
      tags: [],
      votesCount: 0,
      userVote: null,
      commentsCount: 0,
      createdAt: '',
      updatedAt: '',
      author: normalizeUser(null),
      protocol: null,
      comments: [],
    }
  }

  return {
    id: Number(thread.tbl_thread_id ?? thread.id ?? 0) || null,
    protocolId: Number(thread.tbl_thread_protocol_id ?? thread.protocol_id ?? 0),
    title: thread.tbl_thread_title ?? thread.title ?? 'Untitled thread',
    body: thread.tbl_thread_body ?? thread.body ?? thread.content ?? '',
    tags: normalizeTags(thread.tbl_thread_tags ?? thread.tags),
    votesCount: Number(thread.tbl_thread_votes_count ?? thread.votes_count ?? 0),
    userVote: thread.user_vote ?? thread.current_user_vote ?? thread.tbl_user_vote ?? null,
    commentsCount: Number(thread.tbl_thread_comments_count ?? thread.comments_count ?? 0),
    createdAt: thread.tbl_thread_created_at ?? thread.created_at ?? '',
    updatedAt: thread.tbl_thread_updated_at ?? thread.updated_at ?? '',
    author: normalizeUser(thread.author),
    protocol: thread.protocol ? normalizeProtocol(thread.protocol) : null,
    comments: Array.isArray(thread.comments) ? thread.comments.map(normalizeComment) : [],
  }
}

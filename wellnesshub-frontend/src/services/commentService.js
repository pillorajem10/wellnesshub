import api from '@/services/api'

export const commentService = {
  getComments(params = {}) {
    return api.get('/comments', { params })
  },

  getComment(id) {
    return api.get(`/comments/${id}`)
  },

  createComment(payload) {
    return api.post('/comments', payload)
  },

  updateComment(id, payload) {
    return api.put(`/comments/${id}`, payload)
  },

  deleteComment(id) {
    return api.delete(`/comments/${id}`)
  },
}

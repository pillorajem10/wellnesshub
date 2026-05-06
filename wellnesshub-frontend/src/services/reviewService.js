import api from '@/services/api'

export const reviewService = {
  getReviews(params = {}) {
    return api.get('/reviews', { params })
  },

  getReview(id) {
    return api.get(`/reviews/${id}`)
  },

  createReview(payload) {
    return api.post('/reviews', payload)
  },

  updateReview(id, payload) {
    return api.put(`/reviews/${id}`, payload)
  },

  deleteReview(id) {
    return api.delete(`/reviews/${id}`)
  },
}

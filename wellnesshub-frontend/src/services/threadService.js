import api from '@/services/api'

export const threadService = {
  getThreads(params = {}) {
    return api.get('/threads', { params })
  },

  getThread(id) {
    return api.get(`/threads/${id}`)
  },

  createThread(payload) {
    return api.post('/threads', payload)
  },

  updateThread(id, payload) {
    return api.put(`/threads/${id}`, payload)
  },

  deleteThread(id) {
    return api.delete(`/threads/${id}`)
  },
}

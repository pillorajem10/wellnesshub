import api from '@/services/api'

export const protocolService = {
  getProtocols(params = {}) {
    return api.get('/protocols', { params })
  },

  getProtocol(id) {
    return api.get(`/protocols/${id}`)
  },

  createProtocol(payload) {
    return api.post('/protocols', payload)
  },

  updateProtocol(id, payload) {
    return api.put(`/protocols/${id}`, payload)
  },

  deleteProtocol(id) {
    return api.delete(`/protocols/${id}`)
  },
}

import api from '@/services/api'

export const searchService = {
  searchProtocols(q) {
    return api.get('/search/protocols', { params: { q } })
  },

  searchThreads(q) {
    return api.get('/search/threads', { params: { q } })
  },
}

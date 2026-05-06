import api from '@/services/api'

export const searchService = {
  searchProtocols(q, sort = 'recent') {
    return api.get('/search/protocols', {
      params: {
        q,
        sort,
      },
    })
  },

  searchThreads(q, sort = 'recent') {
    return api.get('/search/threads', {
      params: {
        q,
        sort,
      },
    })
  },
}
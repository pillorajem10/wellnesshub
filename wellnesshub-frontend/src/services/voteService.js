import api from '@/services/api'

export const voteService = {
  vote(payload) {
    return api.post('/votes', payload)
  },
}

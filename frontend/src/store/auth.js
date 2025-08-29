import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    loading: false,
    error: null
  }),
  
  getters: {
    isAuthenticated: (state) => !!state.token,
    getUser: (state) => state.user
  },
  
  actions: {
    async register(userData) {
      this.loading = true
      this.error = null
      try {
        const response = await window.axios.post('/api/register', userData)
        this.token = response.data.token
        this.user = response.data.user
        localStorage.setItem('token', this.token)
        
        // Ensure the token has the Bearer prefix
        const authHeader = this.token.startsWith('Bearer ') ? this.token : `Bearer ${this.token}`
        window.axios.defaults.headers.common['Authorization'] = authHeader
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Une erreur est survenue lors de l\'inscription'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async login(credentials) {
      this.loading = true
      this.error = null
      try {
        const response = await window.axios.post('/api/login', credentials)
        this.token = response.data.token
        this.user = response.data.user
        localStorage.setItem('token', this.token)
        
        // Ensure the token has the Bearer prefix
        const authHeader = this.token.startsWith('Bearer ') ? this.token : `Bearer ${this.token}`
        window.axios.defaults.headers.common['Authorization'] = authHeader
        return response
      } catch (error) {
        this.error = error.response?.data?.message || 'Identifiants incorrects'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async logout() {
      this.loading = true
      try {
        await window.axios.post('/api/logout')
        this.token = null
        this.user = null
        localStorage.removeItem('token')
        delete window.axios.defaults.headers.common['Authorization']
      } catch (error) {
        console.error('Erreur lors de la déconnexion:', error)
      } finally {
        this.loading = false
      }
    },
    
    async fetchUser() {
      if (!this.token) {
        return
      }
      
      // Ensure token has Bearer prefix before making API call
      if (this.token && !window.axios.defaults.headers.common['Authorization']) {
        const authHeader = this.token.startsWith('Bearer ') ? this.token : `Bearer ${this.token}`
        window.axios.defaults.headers.common['Authorization'] = authHeader
      }
      
      this.loading = true
      try {
        const response = await window.axios.get('/api/user')
        this.user = response.data
        return this.user
      } catch (error) {
        if (error.response?.status === 401) {
          this.logout()
        }
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})
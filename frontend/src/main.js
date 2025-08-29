import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import './style.css'
import App from './App.vue'
import axios from 'axios'
import { ensureAuthHeader } from './utils/auth'

// Configure axios
const axiosInstance = axios.create({
  baseURL: 'http://127.0.0.1:8000',
  timeout: 10000,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    'X-Requested-With': 'XMLHttpRequest' // Add this for Laravel to recognize AJAX requests
  },
  withCredentials: true // Include cookies in requests
})

// Make axios available globally
window.axios = axiosInstance

// Use the utility function to ensure proper Bearer format
ensureAuthHeader()

// Log initial auth state
const token = localStorage.getItem('token')
console.log('App initialization: Auth token exists:', !!token)
if (token) {
  console.log('App initialization: Authorization header set to:', window.axios.defaults.headers.common['Authorization'])
} else {
  console.log('No token found in localStorage')
}

// Fix for AbortError: The play() request was interrupted by a call to pause()
// See: https://goo.gl/LdLk22
document.addEventListener('DOMContentLoaded', () => {
  // Patch HTMLMediaElement.prototype.play to handle interrupted play requests
  const originalPlay = HTMLMediaElement.prototype.play
  HTMLMediaElement.prototype.play = function() {
    const playPromise = originalPlay.apply(this)
    if (playPromise !== undefined) {
      playPromise.catch(error => {
        if (error.name === 'AbortError') {
          console.log('Suppressed AbortError:', error.message)
        } else {
          console.error('Media play error:', error)
        }
      })
    }
    return playPromise
  }
})

// Création de l'application Vue
const app = createApp(App)

// Intégration de Pinia pour la gestion d'état
app.use(createPinia())

// Intégration de Vue Router pour la navigation
app.use(router)

// Montage de l'application sur l'élément #app
app.mount('#app')

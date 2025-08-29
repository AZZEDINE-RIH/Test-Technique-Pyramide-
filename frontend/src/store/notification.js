import { defineStore } from 'pinia'

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    message: '',
    type: 'success', // success, error, info, warning
    show: false,
    duration: 3000 // milliseconds
  }),
  
  actions: {
    showNotification({ message, type = 'success', duration = 3000 }) {
      this.message = message
      this.type = type
      this.duration = duration
      this.show = true
      
      // Auto-hide after duration
      if (duration > 0) {
        setTimeout(() => {
          this.hideNotification()
        }, duration)
      }
    },
    
    hideNotification() {
      this.show = false
    },
    
    // Convenience methods for different notification types
    success(message, duration = 3000) {
      this.showNotification({ message, type: 'success', duration })
    },
    
    error(message, duration = 3000) {
      this.showNotification({ message, type: 'error', duration })
    },
    
    info(message, duration = 3000) {
      this.showNotification({ message, type: 'info', duration })
    },
    
    warning(message, duration = 3000) {
      this.showNotification({ message, type: 'warning', duration })
    }
  }
})
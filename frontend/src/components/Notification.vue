<script setup>
import { ref, watch } from 'vue'

// Props for the notification component
const props = defineProps({
  message: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'success', // success, error, info, warning
    validator: (value) => ['success', 'error', 'info', 'warning'].includes(value)
  },
  duration: {
    type: Number,
    default: 3000 // milliseconds
  },
  show: {
    type: Boolean,
    default: false
  }
})

// Emit events
const emit = defineEmits(['close'])

// Local state
const isVisible = ref(props.show)

// Watch for changes in the show prop
watch(() => props.show, (newValue) => {
  isVisible.value = newValue
  
  // Auto-hide after duration
  if (newValue && props.duration > 0) {
    setTimeout(() => {
      closeNotification()
    }, props.duration)
  }
})

// Close notification
function closeNotification() {
  isVisible.value = false
  emit('close')
}
</script>

<template>
  <transition name="notification-fade">
    <div 
      v-if="isVisible" 
      class="notification" 
      :class="[`notification-${type}`]"
    >
      <div class="notification-content">
        <span>{{ message }}</span>
        <button @click="closeNotification" class="notification-close">&times;</button>
      </div>
    </div>
  </transition>
</template>

<style scoped>
.notification {
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 1000;
  min-width: 250px;
  max-width: 450px;
  padding: 15px;
  border-radius: 4px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  animation: slide-in 0.3s ease-out forwards;
}

.notification-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.notification-success {
  background-color: #4caf50;
  color: white;
}

.notification-error {
  background-color: #f44336;
  color: white;
}

.notification-info {
  background-color: #2196f3;
  color: white;
}

.notification-warning {
  background-color: #ff9800;
  color: white;
}

.notification-close {
  background: transparent;
  border: none;
  color: white;
  font-size: 20px;
  cursor: pointer;
  margin-left: 10px;
  padding: 0 5px;
}

.notification-fade-enter-active,
.notification-fade-leave-active {
  transition: opacity 0.3s, transform 0.3s;
}

.notification-fade-enter-from,
.notification-fade-leave-to {
  opacity: 0;
  transform: translateX(30px);
}

@keyframes slide-in {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}
</style>
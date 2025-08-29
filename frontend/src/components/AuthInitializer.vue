<script setup>
import { onMounted } from 'vue'
import { useAuthStore } from '../store/auth'

const authStore = useAuthStore()

onMounted(async () => {
  // Initialize authentication state when component is mounted
  if (authStore.token) {
    try {
      await authStore.fetchUser()
    } catch (error) {
      console.error('Failed to initialize auth state')
    }
  }
})
</script>

<template>
  <!-- This is an invisible component that just initializes auth state -->
</template>
<script setup>
// App.vue est le composant racine de l'application
// Il contient principalement le routeur-view pour afficher les différentes pages
import AuthInitializer from './components/AuthInitializer.vue'
import Notification from './components/Notification.vue'
import { useNotificationStore } from './store/notification.js'

const notificationStore = useNotificationStore()
</script>

<template>
  <!-- Le composant AuthInitializer initialise l'état d'authentification -->
  <AuthInitializer />
  
  <!-- Notification component -->
  <Notification 
    :message="notificationStore.message"
    :type="notificationStore.type"
    :show="notificationStore.show"
    :duration="notificationStore.duration"
    @close="notificationStore.hideNotification()"
  />
  
  <!-- Le composant router-view affiche le composant correspondant à la route actuelle -->
  <router-view />
</template>

<style>
/* Styles globaux de l'application */
body {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  margin: 0;
  padding: 0;
}
</style>

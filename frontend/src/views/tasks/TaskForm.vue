<script setup>
import { onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import TaskFormComponent from '../../components/task/TaskForm.vue'

const route = useRoute()
const router = useRouter()

const projectId = route.params.projectId
const taskId = route.params.id
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center">
          <button @click="router.back()" class="mr-4 text-gray-600 hover:text-gray-900">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
          </button>
          <h1 class="text-2xl font-bold text-gray-900">{{ taskId ? 'Modifier la tâche' : 'Créer une tâche' }}</h1>
        </div>
      </div>
    </header>
    
    <!-- Main content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <TaskFormComponent :project-id="projectId" :task-id="taskId" />
    </main>
  </div>
</template>
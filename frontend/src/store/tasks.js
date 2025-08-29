import { defineStore } from 'pinia'
import axios from 'axios'
import { ensureAuthHeader } from '../utils/auth'

export const useTaskStore = defineStore('tasks', {
  state: () => ({
    tasks: [],
    currentTask: null,
    loading: false,
    error: null
  }),

  getters: {
    getTasks: (state) => state.tasks,
    getCurrentTask: (state) => state.currentTask,
    isLoading: (state) => state.loading,
    getTasksByProject: (state) => (projectId) => {
      return state.tasks.filter(task => task.project_id === projectId)
    }
  },

  actions: {
    async fetchTasks(projectId) {
      this.loading = true
      this.error = null
      try {
        ensureAuthHeader()
        console.log(`Récupération des tâches pour le projet ${projectId}`)
        const response = await axios.get(`/api/projects/${projectId}/tasks`)
        console.log('Tâches récupérées:', response.data)

        // Vérifier si les données sont dans un wrapper 'data'
        if (Array.isArray(response.data)) {
          this.tasks = response.data
        } else if (response.data.data && Array.isArray(response.data.data)) {
          this.tasks = response.data.data
        } else {
          console.error('Format de réponse inattendu pour les tâches:', response.data)
          this.tasks = []
        }

        return this.tasks
      } catch (error) {
        console.error('Erreur lors de la récupération des tâches:', error)
        this.error = error.response?.data?.message || 'Erreur lors de la récupération des tâches'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchTask(id) {
      this.loading = true
      this.error = null
      try {
        ensureAuthHeader()
        const response = await axios.get(`/api/tasks/${id}`)
        this.currentTask = response.data
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la récupération de la tâche'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createTask(projectId, taskData) {
      this.loading = true
      this.error = null
      try {
        ensureAuthHeader()
        console.log(`Création d'une tâche pour le projet ${projectId}:`, taskData)
        const response = await axios.post(`/api/projects/${projectId}/tasks`, taskData)
        console.log('Réponse de création de tâche:', response.data)

        // Vérifier si les données sont dans un wrapper 'data'
        const newTask = response.data.data || response.data
        this.tasks.push(newTask)
        return newTask
      } catch (error) {
        console.error('Erreur lors de la création de la tâche:', error)
        console.error('Détails de la réponse:', error.response?.data)
        this.error = error.response?.data?.message || 'Erreur lors de la création de la tâche'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateTask(id, taskData) {
      this.loading = true
      this.error = null
      try {
        ensureAuthHeader()
        console.log(`Mise à jour de la tâche ${id}:`, taskData)
        const response = await axios.put(`/api/tasks/${id}`, taskData)
        console.log('Réponse de mise à jour de tâche:', response.data)

        // Vérifier si les données sont dans un wrapper 'data'
        const updatedTask = response.data.data || response.data

        const index = this.tasks.findIndex(t => t.id === id)
        if (index !== -1) {
          this.tasks[index] = updatedTask
        }
        if (this.currentTask && this.currentTask.id === id) {
          this.currentTask = updatedTask
        }
        return updatedTask
      } catch (error) {
        console.error('Erreur lors de la mise à jour de la tâche:', error)
        console.error('Détails de la réponse:', error.response?.data)
        this.error = error.response?.data?.message || 'Erreur lors de la mise à jour de la tâche'
        throw error
      } finally {
        this.loading = false
      }
    },

    async updateTaskStatus(id, status) {
      this.loading = true
      this.error = null
      try {
        ensureAuthHeader()
        console.log(`Mise à jour du statut de la tâche ${id} à ${status}`)
        
        // Mise à jour du statut au lieu de is_completed
        const response = await axios.put(`/api/tasks/${id}`, { status: status })
        console.log('Réponse de mise à jour du statut:', response.data)
        
        // Vérifier si les données sont dans un wrapper 'data'
        const updatedTask = response.data.data || response.data
        
        const index = this.tasks.findIndex(t => t.id === parseInt(id))
        if (index !== -1) {
          this.tasks[index] = updatedTask
        }
        if (this.currentTask && this.currentTask.id === parseInt(id)) {
          this.currentTask = updatedTask
        }
        return updatedTask
      } catch (error) {
        console.error('Erreur lors de la mise à jour du statut:', error)
        console.error('Détails de la réponse:', error.response?.data)
        this.error = error.response?.data?.message || 'Erreur lors de la mise à jour du statut de la tâche'
        throw error
      } finally {
        this.loading = false
      }
    },

    async deleteTask(id) {
      this.loading = true
      this.error = null
      try {
        ensureAuthHeader()
        await axios.delete(`/api/tasks/${id}`)
        this.tasks = this.tasks.filter(t => t.id !== id)
        if (this.currentTask && this.currentTask.id === id) {
          this.currentTask = null
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la suppression de la tâche'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})
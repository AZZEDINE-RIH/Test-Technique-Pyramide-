import { defineStore } from 'pinia'
import axios from 'axios'

export const useProjectStore = defineStore('projects', {
  state: () => ({
    projects: [],
    currentProject: null,
    loading: false,
    error: null
  }),
  
  getters: {
    getProjects: (state) => state.projects,
    getCurrentProject: (state) => state.currentProject,
    isLoading: (state) => state.loading
  },
  
  actions: {
    async fetchProjects() {
      this.loading = true
      this.error = null
      try {
        // Ensure auth header is set
        const token = localStorage.getItem('token')
        if (token) {
          const authHeader = token.startsWith('Bearer ') ? token : `Bearer ${token}`
          axios.defaults.headers.common['Authorization'] = authHeader
        }
        
        // Add ?with=user,tasks to fetch projects with user and tasks data
        const response = await axios.get('/api/projects?with=user,tasks')
        
        // Force projects to be an array
        if (Array.isArray(response.data)) {
          this.projects = response.data
        } else if (response.data && typeof response.data === 'object') {
          // If it's an object with data property (Laravel API resource)
          if (Array.isArray(response.data.data)) {
            this.projects = response.data.data
          } else {
            // If it's a single object, wrap it in an array
            this.projects = [response.data]
          }
        } else {
          // Fallback to empty array
          this.projects = []
        }
        
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la récupération des projets'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async fetchProject(id) {
      this.loading = true
      this.error = null
      try {
        // Add ?with=user,tasks to fetch project with user and tasks data
        const response = await axios.get(`/api/projects/${id}?with=user,tasks`)
        // Handle different response structures
        if (response.data && response.data.data) {
          // If response has a data property (Laravel API resource)
          this.currentProject = response.data.data
        } else {
          // Direct response
          this.currentProject = response.data
        }
        
        return this.currentProject
      } catch (error) {

        this.error = error.response?.data?.message || 'Erreur lors de la récupération du projet'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async createProject(projectData) {
      this.loading = true
      
      // Ensure auth header is set
      const token = localStorage.getItem('token')
      if (token) {
        const authHeader = token.startsWith('Bearer ') ? token : `Bearer ${token}`
        axios.defaults.headers.common['Authorization'] = authHeader
      }
      this.error = null
      try {
        const response = await axios.post('/api/projects', projectData)

        
        // Make sure projects is an array before pushing
        if (!Array.isArray(this.projects)) {
          this.projects = []
        }
        
        // Laravel wraps JsonResource responses in a 'data' key
        // Extract the project data from the response
        const createdProject = response.data
        
        // Add the new project to our projects array
        this.projects.push(createdProject)
        return createdProject
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la création du projet'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async updateProject(id, projectData) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.put(`/api/projects/${id}`, projectData)
        // Make sure projects is an array before using findIndex
        if (!Array.isArray(this.projects)) {
          this.projects = []
        }
        // Extract the project data from the response
        const updatedProject = response.data.data || response.data
        
        const index = this.projects.findIndex(p => p.id === id)
        if (index !== -1) {
          this.projects[index] = updatedProject
        }
        if (this.currentProject && this.currentProject.id === id) {
          this.currentProject = updatedProject
        }
        return updatedProject
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la mise à jour du projet'
        throw error
      } finally {
        this.loading = false
      }
    },
    
    async deleteProject(id) {
      this.loading = true
      this.error = null
      try {
        await axios.delete(`/api/projects/${id}`)
        this.projects = this.projects.filter(p => p.id !== id)
        if (this.currentProject && this.currentProject.id === id) {
          this.currentProject = null
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la suppression du projet'
        throw error
      } finally {
        this.loading = false
      }
    }
  }
})
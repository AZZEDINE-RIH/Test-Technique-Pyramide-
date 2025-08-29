/**
 * Service for handling project-related API calls
 */
import { ensureAuthHeader } from '../utils/auth';

export default {
  /**
   * Get all projects
   * @returns {Promise} Promise with projects data
   */
  async getProjects() {
    // Ensure auth header is set and return authentication status
    const isAuthenticated = ensureAuthHeader();
    if (!isAuthenticated) {
      throw new Error('Authentication token not found');
    }
    
    try {
      const response = await window.axios.get('/api/projects');
      return response.data.data;
    } catch (error) {
      throw error;
    }
  },
  
  /**
   * Create a new project
   * @param {Object} projectData - The project data
   * @returns {Promise} Promise with created project data
   */
  async createProject(projectData) {
    // Ensure auth header is set and return authentication status
    const isAuthenticated = ensureAuthHeader();
    if (!isAuthenticated) {
      throw new Error('Authentication token not found');
    }
    
    try {
      const response = await window.axios.post('/api/projects', projectData);
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  /**
   * Get a specific project
   * @param {number} id - The project ID
   * @returns {Promise} Promise with project data
   */
  async getProject(id) {
    // Ensure auth header is set before making the request
    ensureAuthHeader();
    
    try {
      const response = await window.axios.get(`/api/projects/${id}`);
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  /**
   * Update a project
   * @param {number} id - The project ID
   * @param {Object} projectData - The updated project data
   * @returns {Promise} Promise with updated project data
   */
  async updateProject(id, projectData) {
    // Ensure auth header is set before making the request
    ensureAuthHeader();
    
    try {
      const response = await window.axios.put(`/api/projects/${id}`, projectData);
      return response.data;
    } catch (error) {
      throw error;
    }
  },
  
  /**
   * Delete a project
   * @param {number} id - The project ID
   * @returns {Promise} Promise with deletion status
   */
  async deleteProject(id) {
    // Ensure auth header is set before making the request
    ensureAuthHeader();
    
    try {
      const response = await window.axios.delete(`/api/projects/${id}`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }
};
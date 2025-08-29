/**
 * Utility functions for authentication
 */

/**
 * Ensures the Authorization header is set for Axios requests
 * @returns {boolean} True if authentication header was set successfully, false otherwise
 */
import axios from 'axios';

export const ensureAuthHeader = () => {
  const token = localStorage.getItem('token');
  
  if (token) {
    // Ensure the token has the Bearer prefix
    const authHeader = token.startsWith('Bearer ') ? token : `Bearer ${token}`;
    
    // Set the header directly on axios instance
    axios.defaults.headers.common['Authorization'] = authHeader;
    return true;
  } else {
    return false;
  }
};

/**
 * Checks if the user is authenticated
 * @returns {boolean} True if authenticated, false otherwise
 */
export const isAuthenticated = () => {
  return !!localStorage.getItem('token');
};
// src/services/AuthService.js
import apiClient from './apiClient';

const AuthService = {
  login: async (credentials) => {
    try {
      const response = await apiClient.post('/users/login', credentials);
      // The response from our Yii2 API includes: user object and access_token
      // Example: response.data = { user: {id, username, email}, access_token: '...' }
      if (response.data && response.data.access_token && response.data.user) {
        return response.data; // Contains user object and access_token
      }
      throw new Error('Login response did not contain expected data.');
    } catch (error) {
      // apiClient might throw an error with response.data.errors
      const errorMsg = error.response?.data?.errors || error.response?.data?.message || 'Login failed';
      console.error('Login error:', errorMsg);
      // To make it easier for components to display errors, re-throw a structured error or the message
      throw new Error(typeof errorMsg === 'string' ? errorMsg : JSON.stringify(errorMsg));
    }
  },

  register: async (userData) => {
    try {
      const response = await apiClient.post('/users/register', userData);
      // Example: response.data = { user: {id, username, email}, access_token: '...' }
      if (response.data && response.data.access_token && response.data.user) {
        return response.data; // Contains user object and access_token
      }
      throw new Error('Registration response did not contain expected data.');
    } catch (error) {
      const errorMsg = error.response?.data?.errors || error.response?.data?.message || 'Registration failed';
      console.error('Registration error:', errorMsg);
      throw new Error(typeof errorMsg === 'string' ? errorMsg : JSON.stringify(errorMsg));
    }
  },

  logout: async (token) => {
    // Inform the backend to invalidate the token if necessary.
    // Our current backend UserController::actionLogout clears the access_token in the DB.
    try {
      await apiClient.post('/users/logout'); // Token is sent via interceptor
      // Frontend logout (clearing local storage etc.) is handled in AuthContext.logoutAction
    } catch (error) {
        // Even if backend logout fails, proceed with frontend logout.
        // Log error but don't block frontend logout.
        console.error('Logout API call failed:', error.response?.data?.message || error.message);
    }
  },

  getCurrentUser: async () => {
    // Fetches user profile if a token is present and valid
    // Useful for verifying token on app load or getting updated user info
    try {
      const response = await apiClient.get('/users/me');
      return response.data; // Expected to be the user object {id, username, email, stats, ...}
    } catch (error) {
      console.error('Failed to fetch current user:', error.response?.data?.message || error.message);
      throw error; // Re-throw to be handled by caller (e.g., clear token in AuthContext)
    }
  },
};

export default AuthService;
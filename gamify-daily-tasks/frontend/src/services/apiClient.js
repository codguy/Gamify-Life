// src/services/apiClient.js
import axios from 'axios';

// Determine the base URL for the API.
// For development, this might point to your local Yii2 server.
// In production, this would be your actual API domain.
// TODO: Make this configurable via environment variables (.env file)
const API_BASE_URL = process.env.REACT_APP_API_BASE_URL || 'http://localhost:8080/v1'; // Assuming Yii2 dev server runs on 8080

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    // You can add other default headers here if needed
  },
});

// Request interceptor to add the auth token to headers
apiClient.interceptors.request.use(
  (config) => {
    // TODO: Get the token from where it's stored (e.g., localStorage, AuthContext)
    const token = localStorage.getItem('authToken'); // Example: using localStorage
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Optional: Response interceptor for global error handling or token refresh logic
apiClient.interceptors.response.use(
  (response) => {
    // Any status code that lie within the range of 2xx cause this function to trigger
    return response;
  },
  (error) => {
    // Any status codes that falls outside the range of 2xx cause this function to trigger
    // You can handle global errors here, e.g., for 401 Unauthorized, redirect to login
    if (error.response && error.response.status === 401) {
      // TODO: Implement logout logic or redirect to login
      // For example, remove token and redirect:
      // localStorage.removeItem('authToken');
      // window.location.href = '/login'; 
      console.error('Unauthorized request - 401. Potentially redirect to login or clear token.', error.config);
    }
    return Promise.reject(error);
  }
);

export default apiClient;
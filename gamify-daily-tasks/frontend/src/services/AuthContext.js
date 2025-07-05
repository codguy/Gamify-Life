// src/contexts/AuthContext.js
import React, { createContext, useState, useEffect, useContext } from 'react';
// We will import AuthService and apiClient later when they are fully defined
// import AuthService from '../services/AuthService';
// import apiClient from '../services/apiClient';

export const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null); // User object or null
  const [token, setToken] = useState(localStorage.getItem('authToken')); // Init token from localStorage
  const [isLoading, setIsLoading] = useState(true); // Check auth status on initial load

  useEffect(() => {
    const storedToken = localStorage.getItem('authToken');
    if (storedToken) {
      // In a real app, you'd verify the token with the backend here, e.g., fetch user profile
      // For example:
      // apiClient.get('/users/me') // Make sure apiClient has the token via interceptor
      //   .then(response => {
      //     setUser(response.data); // Assuming response.data is the user object
      //     setToken(storedToken); // Confirm token
      //   })
      //   .catch(() => {
      //     localStorage.removeItem('authToken');
      //     setToken(null);
      //     setUser(null);
      //   })
      //   .finally(() => setIsLoading(false));
      
      // Simplified for now: if token exists, assume valid for initial load for context setup.
      // Actual user data will be fetched or set upon login or by a protected route.
      setToken(storedToken);
      setIsLoading(false);
    } else {
      setIsLoading(false);
    }
  }, []);

  // login, register, logout functions will be more fleshed out when AuthService is used.
  // They will typically call AuthService methods and then update context state.

  const loginAction = (userData, authToken) => {
    localStorage.setItem('authToken', authToken);
    setToken(authToken);
    setUser(userData);
    // The apiClient interceptor should pick up the new token from localStorage
  };

  const logoutAction = () => {
    localStorage.removeItem('authToken');
    setToken(null);
    setUser(null);
    // The apiClient interceptor will no longer find a token in localStorage
    // TODO: Redirect to login page, typically handled by routing logic
    console.log('User logged out. Implement redirect.');
  };

  const value = {
    user,
    setUser, // Useful if user profile is fetched/updated separately
    token,
    isLoading,
    login: loginAction, // Will be wrapped by AuthService
    logout: logoutAction,
    // register action will be similar to login, setting user and token
  };

  return (
    <AuthContext.Provider value={value}>
      {!isLoading && children} {/* Don't render children until initial loading is done */}
    </AuthContext.Provider>
  );
};

// Custom hook to use the AuthContext
export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};
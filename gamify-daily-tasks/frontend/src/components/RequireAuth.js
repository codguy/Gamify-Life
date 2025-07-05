// src/components/RequireAuth.js
import React from 'react';
import { useAuth } from '../contexts/AuthContext';
import { Navigate, useLocation } from 'react-router-dom';

function RequireAuth({ children }) {
  const auth = useAuth();
  const location = useLocation();

  if (auth.isLoading) {
    // You might want to show a loading spinner here
    return <div>Loading authentication state...</div>;
  }

  if (!auth.token) {
    // Redirect them to the /login page, but save the current location they were
    // trying to go to when they were redirected. This allows us to send them
    // along to that page after they login, which is a nicer user experience.
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  return children;
}

export default RequireAuth;
// src/App.js
import React from 'react';
import {
  BrowserRouter as Router,
  Routes,
  Route,
  Navigate
} from 'react-router-dom';
import { AuthProvider, useAuth } from './contexts/AuthContext';
import LoginPage from './pages/LoginPage';
import RegisterPage from './pages/RegisterPage';
// Placeholder for TaskListPage, we'll create it later
// import TaskListPage from './pages/TaskListPage'; 
import Navbar from './components/Navbar'; // We'll create this next
import RequireAuth from './components/RequireAuth'; // We'll create this next
import './App.css'; // Your existing App.css or a new one

// Placeholder for pages that will be protected
const TaskListPagePlaceholder = () => {
  const auth = useAuth();
  return (
    <div>
      <h2>Task List (Protected)</h2>
      <p>Welcome, {auth.user ? auth.user.username : 'User'}!</p>
      <p>Your tasks would be listed here.</p>
    </div>
  );
};

const HomePage = () => {
    const auth = useAuth();
    return (
        <div>
            <h1>Welcome to Gamify Life!</h1>
            {auth.token ? (
                <p>You are logged in. <Link to="/tasks">View Tasks</Link></p>
            ) : (
                <p>Please <Link to="/login">login</Link> or <Link to="/register">register</Link> to continue.</p>
            )}
        </div>
    );
}

function App() {
  return (
    <AuthProvider>
      <Router>
        <Navbar />
        <div className="container" style={{ paddingTop: '70px' }}> {/* Basic container with padding for fixed navbar */}
          <Routes>
            <Route path="/" element={<HomePage />} />
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
            
            {/* Protected Routes Example */}
            <Route 
              path="/tasks" 
              element={(
                <RequireAuth>
                  <TaskListPagePlaceholder /> {/* Replace with actual TaskListPage later */}
                </RequireAuth>
              )}
            />
            
            {/* Redirect non-logged-in users trying to access a non-existent protected page perhaps */}
            {/* <Route path="*" element={<Navigate to="/" />} /> */}
          </Routes>
        </div>
      </Router>
    </AuthProvider>
  );
}

export default App;
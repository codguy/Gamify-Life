// src/pages/LoginPage.js
import React, { useState } from 'react';
import { useAuth } from '../contexts/AuthContext';
import AuthService from '../services/AuthService';
import { useNavigate, Link } from 'react-router-dom'; // Assuming react-router-dom v6

function LoginPage() {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const auth = useAuth();
  const navigate = useNavigate();

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);
    try {
      const data = await AuthService.login({ username, password });
      // AuthContext's loginAction expects user data and token separately
      auth.login(data.user, data.access_token); 
      navigate('/tasks'); // Redirect to a protected route, e.g., task list or dashboard
    } catch (err) {
      setError(err.message || 'Failed to login. Please check your credentials.');
    }
    setLoading(false);
  };

  return (
    <div>
      <h2>Login</h2>
      <form onSubmit={handleSubmit}>
        <div>
          <label htmlFor="username">Username:</label>
          <input
            type="text"
            id="username"
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            required
          />
        </div>
        <div>
          <label htmlFor="password">Password:</label>
          <input
            type="password"
            id="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
          />
        </div>
        {error && <p style={{ color: 'red' }}>{error}</p>}
        <button type="submit" disabled={loading}>
          {loading ? 'Logging in...' : 'Login'}
        </button>
      </form>
      <p>
        Don't have an account? <Link to="/register">Register here</Link>
      </p>
    </div>
  );
}

export default LoginPage;
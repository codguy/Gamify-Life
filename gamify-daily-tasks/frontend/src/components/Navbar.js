// src/components/Navbar.js
import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { useAuth } from '../contexts/AuthContext';
import AuthService from '../services/AuthService'; // For logout API call

function Navbar() {
  const auth = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    try {
      if (auth.token) { // Check if token exists before calling API logout
        await AuthService.logout(auth.token); // Call API to invalidate token if necessary
      }
    } catch (error) {
      console.error('Logout API call failed, proceeding with frontend logout:', error);
    }
    auth.logout(); // This clears local token and context state
    navigate('/login');
  };

  // Basic inline styling for the navbar
  const navStyle = {
    backgroundColor: '#333',
    padding: '10px 20px',
    color: 'white',
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    position: 'fixed', // Fixed navbar
    top: 0,
    left: 0,
    right: 0,
    zIndex: 1000,
  };

  const linkStyle = {
    color: 'white',
    margin: '0 10px',
    textDecoration: 'none',
  };

  const buttonStyle = {
    background: 'none',
    border: 'none',
    color: 'white',
    cursor: 'pointer',
    padding: '0',
    margin: '0 10px',
    fontSize: 'inherit',
  };

  return (
    <nav style={navStyle}>
      <div>
        <Link to="/" style={linkStyle}>GamifyLife</Link>
        {auth.token && (
          <Link to="/tasks" style={linkStyle}>Tasks</Link>
        )}
      </div>
      <div>
        {auth.token ? (
          <>
            <span style={{ marginRight: '15px' }}>
              {auth.user ? `Hi, ${auth.user.username}` : 'User'}
            </span>
            <button onClick={handleLogout} style={buttonStyle}>Logout</button>
          </>
        ) : (
          <>
            <Link to="/login" style={linkStyle}>Login</Link>
            <Link to="/register" style={linkStyle}>Register</Link>
          </>
        )}
      </div>
    </nav>
  );
}

export default Navbar;
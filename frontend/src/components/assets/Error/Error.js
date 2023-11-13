import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import './Error.css';

const ErrorComponent = ({ message }) => {
    const navigate = useNavigate();

  const handleOkClick = () => {
    // Redirige vers la page de connexion
    navigate('/login');
  };

  return (
    <div className="error-message">
      <p>{message}</p>
      <button onClick={handleOkClick}>Se connecter</button>
    </div>
  );
};

export default ErrorComponent;

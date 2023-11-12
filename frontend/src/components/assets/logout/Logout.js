import React from 'react';
import { useNavigate } from 'react-router-dom';


const LogoutButton = ({ onLogout }) => {
    const navigate = useNavigate();

    const handleLogout = () => {
        // Ajoutez votre logique de déconnexion ici
        onLogout();
        localStorage.removeItem('token');
        navigate('/login');

    };

    return (
        <button onClick={handleLogout}>Déconnexion</button>
    );
};

export default LogoutButton;

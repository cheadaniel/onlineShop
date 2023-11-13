import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { Link, useNavigate } from 'react-router-dom';

import ErrorComponent from '../assets/Error/Error';
import UserForm from './UserForm';
import './User.css'

const UserProfile = ({ userId }) => {
    const navigate = useNavigate();

    const [user, setUser] = useState(null);
    const [error, setError] = useState(null);
    const [showForm, setShowForm] = useState(false);  // État pour gérer la visibilité du formulaire

    useEffect(() => {
        const fetchUserData = async () => {
            try {
                const token = localStorage.getItem('token');

                if (!token) {
                    console.error('JWT token not found');
                    setError('Vous devez être connecté pour voir votre profil.');
                    return;
                }

                const headers = {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json',
                };

                const response = await axios.get(`http://localhost:8000/api/user`, { headers });
                setUser(response.data);
            } catch (error) {
                setError('Vous devez être connecté pour voir votre profil.');
                console.error('Erreur lors de la récupération des informations de l\'utilisateur :', error);
                // Gérer l'erreur ici ou renvoyer une erreur à l'endroit approprié
            }
        };

        fetchUserData();
    }, [userId]);

    const handleShowForm = () => {
        setShowForm(true);
    };

    const handleUpdateSuccess = () => {
        setShowForm(false);
        localStorage.removeItem('token');
        navigate('/login')
    };


    if (!user) {
        return <ErrorComponent message={error} />;
    }

    return (
        <div className="user-info-section">
            {error && <ErrorComponent message={error} />}
            <div>
                <h2>Informations de l'utilisateur</h2>
                <p>Email : {user.email}</p>
                <p>Adresse Postale : {user.Address}</p>
                <p>Portefeuille : {user.Wallet}</p>
            </div>

            <button className="modify-profile-button" onClick={handleShowForm}>Modifier le profil</button>

            {showForm && (
                <UserForm
                    userId={userId}
                    user={user}
                    onUpdateSuccess={handleUpdateSuccess}  // Cacher le formulaire après la mise à jour
                />
            )}
        </div>
    );
};

export default UserProfile;

import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate } from 'react-router-dom';
import ErrorComponent from '../assets/Error/Error';
import UserForm from './UserForm';
import './User.css';

const UserProfiles = () => {
    const navigate = useNavigate();
    const [users, setUsers] = useState(null);
    const [error, setError] = useState(null);
    const [showForm, setShowForm] = useState(false);
    const [userIdToShowForm, setUserIdToShowForm] = useState(null);

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

                const response = await axios.get(`http://localhost:8000/api/users`, { headers });
                setUsers(response.data);
            } catch (error) {
                setError('Vous devez être connecté pour voir votre profil.');
                console.error('Erreur lors de la récupération des informations de l\'utilisateur :', error);
            }
        };

        fetchUserData();
    }, []);

    const handleShowForm = (userId) => {
        setShowForm(true);
        setUserIdToShowForm(userId);
    };

    const handleUpdateSuccess = () => {
        setShowForm(false);
        setUserIdToShowForm(null);
        localStorage.removeItem('token');
        navigate('/login');
    };

    if (!users) {
        return <ErrorComponent message={error} />;
    }

    return (
        <div className="user-info-section">
            <h2>Informations des utilisateurs</h2>

            {users.map((user) => (
                <div key={user.id} className={`user-profile ${userIdToShowForm === user.id ? 'selected' : ''}`} data-user-id={user.id}>
                    <p>Email : {user.email}</p>
                    <p>Adresse Postale : {user.Address}</p>
                    <p>Portefeuille : {user.Wallet}</p>

                    <button
                        className="modify-profile-button"
                        onClick={() => handleShowForm(user.id)}
                    >
                        Modifier le profil
                    </button>

                    {showForm && user.id === userIdToShowForm && (
                        <UserForm user={user} onUpdateSuccess={handleUpdateSuccess} />
                    )}
                </div>
            ))}
        </div>
    );
};

export default UserProfiles;

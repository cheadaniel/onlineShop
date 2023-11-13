import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from 'axios';

const RegistrationForm = () => {
    const navigate = useNavigate();

    const [formData, setFormData] = useState({
        email: '',
        password: '',
        address: '',
    });

    const [error, setError] = useState('');

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        // Validation côté client
        if (!formData.email || !formData.password || !formData.address) {
            setError('Tous les champs doivent être remplis.');
            return;
        }

        try {
            const response = await axios.post('http://localhost:8000/api/users/create', formData, {
                // Configurations pour permettre l'envoi de cookies
                withCredentials: true,
                // Autres configurations selon les besoins
            });

            console.log('Inscription réussie:', response.data);
            navigate('/login');
        } catch (error) {
            console.error('Erreur lors de l\'inscription:', error);
            setError('Erreur lors de l\'inscription. Veuillez réessayer.');
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <div>
                <label htmlFor="email">Email:</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                    required
                />
            </div>
            <div>
                <label htmlFor="password">Mot de passe:</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    value={formData.password}
                    onChange={handleChange}
                    required
                />
            </div>
            <div>
                <label htmlFor="address">Adresse:</label>
                <textarea
                    id="address"
                    name="address"
                    value={formData.address}
                    onChange={handleChange}
                    required
                />
            </div>
            <div>
                <button type="submit">S'inscrire</button>
            </div>
            {error && <p style={{ color: 'red' }}>{error}</p>}
        </form>
    );
};

export default RegistrationForm;

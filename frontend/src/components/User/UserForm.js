import React, { useState } from 'react';
import axios from 'axios';

const UserForm = ({ userId, user, onUpdateSuccess }) => {
    const [formData, setFormData] = useState({
        email: user.email,
        Address: user.Address,
        password: '',
    });

    const handleChange = (e) => {
        setFormData({
            ...formData,
            [e.target.name]: e.target.value,
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            const token = localStorage.getItem('token');

            if (!token) {
                console.error('JWT token not found');
                // Gérer l'erreur ici ou renvoyer une erreur à l'endroit approprié
                return;
            }

            const headers = {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json',
            };
            console.log(formData);

            // Remplacez {userId} par la valeur réelle de userId
            const response = await axios.put(
                `http://localhost:8000/api/users/update/${userId}`,
                formData,
                { headers }
            );

            // Si la mise à jour est réussie, appeler la fonction de rappel
            onUpdateSuccess(response.data);
        } catch (error) {
            console.error('Erreur lors de la mise à jour des informations de l\'utilisateur :', error);
            // Gérer l'erreur ici ou renvoyer une erreur à l'endroit approprié
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <label>
                Email :
                <input
                    type="email"
                    name="email"
                    value={formData.email}
                    onChange={handleChange}
                />
            </label>
            <label>
                Adresse :
                <input
                    type="text"
                    name="Address"
                    value={formData.Address}
                    onChange={handleChange}
                />
            </label>
            <label>
                Nouveau mot de passe :
                <input
                    type="password"
                    name="password"
                    value={formData.password}
                    onChange={handleChange}
                />
            </label>

            <button type="submit">Modifier</button>
        </form>
    );
};

export default UserForm;

import React, { useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import axios from 'axios';
import './Login.css';


const LoginForm = ({ onLogin }) => {
    const [credentials, setCredentials] = useState({
        username: '',
        password: '',
    });

    const navigate = useNavigate();

    const handleChange = (e) => {
        const { name, value } = e.target;
        setCredentials((prevCredentials) => ({
            ...prevCredentials,
            [name]: value,
        }));
    };

    const authenticate = async (username, password) => {
        try {
            const response = await axios.post('http://localhost:8000/api/login_check', {
                username,
                password,
            });

            const token = response.data.token;
            localStorage.setItem('token', token);

            return token;
        } catch (error) {
            console.error('Authentication failed:', error);
            throw error;
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            const token = await authenticate(credentials.username, credentials.password);

            onLogin(token);
            // Rediriger vers la page des produits
            navigate('/products');
        } catch (error) {
            console.error('Error during login:', error);
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <div>
                <label htmlFor="email">Email:</label>
                <input
                    type="email"
                    id="username"
                    name="username"
                    value={credentials.username}
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
                    value={credentials.password}
                    onChange={handleChange}
                    required
                />
            </div>
            <div>
                <button type="submit">Se connecter</button>
            </div>
        </form>
    );
};

export default LoginForm;

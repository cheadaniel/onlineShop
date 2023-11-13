import React, { useEffect, useState } from 'react';
import axios from 'axios';
import './CommandList.css'
import { Link } from 'react-router-dom';
import ErrorComponent from '../assets/Error/Error';

const CommandList = ({ userId }) => {
    const [commands, setCommands] = useState([]);
    const [error, setError] = useState(null);

    const fetchCommands = () => {
        const token = localStorage.getItem('token');

        if (!token) {
            console.error('JWT token not found');
            setError('Vous devez être connecté pour voir vos commandes.');
            return;
        }

        // Ajoutez le token dans les en-têtes de la requête
        const headers = {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
        };

        axios.get(`http://localhost:8000/api/users/${userId}/commands`, { headers })
            .then(response => {
                setCommands(response.data);
            })
            .catch(error => {
                console.error('Error fetching commands:', error);
            });
    };


    return (
        <div className="command-list-container">
            {error && <ErrorComponent message={error} />}

            <h2 className="command-list-title">Your Commands</h2>
            <button className="command-list-button" onClick={fetchCommands}>Voir Mes Commandes</button>
            <table className="command-list-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total Price</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    {commands.map(command => (
                        <tr key={command.id}>
                            <td>{command.id}</td>
                            <td>{new Date(command.Date).toLocaleString()}</td>
                            <td>{command.Status}</td>
                            <td>{command.Total_Price} €</td>
                            <td>
                                <Link to={`/command/${command.id}`}>
                                    <button className="details-button">Details</button>
                                </Link>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default CommandList;

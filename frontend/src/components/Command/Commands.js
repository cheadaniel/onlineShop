import React, { useEffect, useState } from 'react';
import axios from 'axios';
import './Commands.css';
import { Link } from 'react-router-dom';
import ErrorComponent from '../assets/Error/Error';

const CommandsList = ({ userId }) => {
    const [commands, setCommands] = useState([]);
    const [error, setError] = useState(null);
    const [newStatusMap, setNewStatusMap] = useState({}); // Utiliser un objet pour stocker le nouvel état pour chaque commande

    const fetchCommands = () => {
        const token = localStorage.getItem('token');

        if (!token) {
            console.error('JWT token not found');
            setError('Vous devez être connecté pour voir vos commandes.');
            return;
        }

        const headers = {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
        };

        axios.get(`http://localhost:8000/api/commands`, { headers })
            .then(response => {
                // Initialiser le nouvel état pour chaque commande avec le statut actuel
                const newStatusMapInit = {};
                response.data.forEach(command => {
                    newStatusMapInit[command.id] = command.Status;
                });
                setNewStatusMap(newStatusMapInit);
                setCommands(response.data);
            })
            .catch(error => {
                console.error('Error fetching commands:', error);
            });
    };

    const handleChangeStatus = (commandId) => {
        const token = localStorage.getItem('token');

        if (!token) {
            console.error('JWT token not found');
            setError('Vous devez être connecté pour mettre à jour le statut de la commande.');
            return;
        }

        const headers = {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
        };

        axios.put(`http://localhost:8000/api/commands/update/status/${commandId}`, { newStatus: newStatusMap[commandId] }, { headers })
            .then(response => {
                // Mettez à jour les données après la modification du statut
                fetchCommands();
            })
            .catch(error => {
                console.error('Error updating command status:', error);
            });
    };

    useEffect(() => {
        // Récupérer les commandes au chargement de la page
        fetchCommands();
    }, []); // L'effet ne dépend d'aucune variable, donc il se déclenchera une seule fois

    return (
        <div className="command-list-container">
            {error && <ErrorComponent message={error} />}

            <h2 className="command-list-title">Your Commands</h2>
            <button className="command-list-button" onClick={fetchCommands}>Tout voir</button>
            <table className="command-list-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Total Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    {commands.map(command => (
                        <tr key={command.id}>
                            <td>{command.id}</td>
                            <td>{new Date(command.Date).toLocaleString()}</td>
                            <td>{command.Status}</td>
                            <td>{command.Total_Price} €</td>
                            <td className='td-select'>
                                <Link to={`/command/${command.id}`}>
                                    <button className="details-button">Details</button>
                                </Link>
                                <select
                                    value={newStatusMap[command.id]}
                                    onChange={(e) => setNewStatusMap({ ...newStatusMap, [command.id]: e.target.value })}
                                >
                                    <option value="" disabled>Sélectionner un état</option>
                                    <option value="En cours de preparation">En cours de préparation</option>
                                    <option value="Envoyee">Envoyée</option>
                                    <option value="Livree">Livrée</option>
                                </select>
                                <button onClick={() => handleChangeStatus(command.id)}>Update Status</button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
};

export default CommandsList;

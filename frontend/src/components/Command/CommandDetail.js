import React, { useEffect, useState } from 'react';
import axios from 'axios';
import { useParams } from 'react-router-dom';
import ErrorComponent from '../assets/Error/Error';

import './CommandDetail.css';


const CommandDetail = (props) => {
    const { userId } = props;
    const { id } = useParams();

    const [commandDetails, setCommandDetails] = useState({});
    const [commandLines, setCommandLines] = useState([]);
    const [error, setError] = useState(null);

    useEffect(() => {
        const token = localStorage.getItem('token');

        if (!token) {
            console.error('JWT token not found');
            setError('Vous devez être connecté pour voir les détails de la commande.');
            return;
        }

        const headers = {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
        };

        axios.get(`http://localhost:8000/api/commands/${id}`, { headers })
            .then(response => {
                setCommandDetails(response.data);

                // Récupérer les lignes de commande associées à la commande
                axios.get(`http://localhost:8000/api/command-lines/${id}`, { headers })
                    .then(commandLinesResponse => {
                        setCommandLines(commandLinesResponse.data);
                    })
                    .catch(commandLinesError => {
                        console.error('Error fetching command lines:', commandLinesError);
                        setError('Une erreur s\'est produite lors de la récupération des lignes de commande.');
                    });
            })
            .catch(error => {
                console.error('Error fetching command details:', error);
                setError('Une erreur s\'est produite lors de la récupération des détails de la commande.');
            });
    }, [id]);

    return (
        <div className="command-detail-container">
            {error && <ErrorComponent message={error} />}

            {commandDetails && (
                <div>
                    <h2 className="command-header">Détails de la commande</h2>
                    <table className="command-table">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Prix total</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>{commandDetails.Status}</td>
                                <td>{commandDetails.Total_Price} €</td>
                                <td>{new Date(commandDetails.Date).toLocaleString()}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div className="product-details">
                        <h4>Details de chaque produits</h4>
                        <table className="product-table">
                            <thead>
                                <tr>
                                    <th>Nom du produit</th>
                                    <th>Prix du produit</th>
                                    <th>Quantité</th>
                                    <th>Prix total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {commandLines && commandLines.map(commandLine => (
                                    <tr key={commandLine.id}>
                                        {commandLine.product && (
                                            <>
                                                <td>{commandLine.product.name}</td>
                                                <td>{commandLine.product.price} €</td>
                                            </>
                                        )}
                                        <td>{commandLine.quantity}</td>
                                        <td>{commandLine.total_price} €</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </div>
    );
};

export default CommandDetail;

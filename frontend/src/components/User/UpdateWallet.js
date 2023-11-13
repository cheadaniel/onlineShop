import React, { useEffect } from 'react';
import { useParams } from 'react-router-dom';
import { useNavigate } from 'react-router-dom';

import axios from 'axios';

const WalletUpdateComponent = ({ onUpdateSuccess, onError }) => {
    const { id, amount } = useParams();
    const navigate = useNavigate();

    useEffect(() => {
        const handleUpdateWallet = async () => {
            try {
                const token = localStorage.getItem('token');

                if (!token) {
                    console.error('JWT token not found');
                    return;
                }
                const headers = {
                    Authorization: `Bearer ${token}`,
                    'Content-Type': 'application/json',
                };

                const response = await axios.put(`http://localhost:8000/api/users/update-wallet/${id}/${amount}`, {}, { headers });
                onUpdateSuccess(response.data);
                alert('Portefeuille mis à jour avec succès');
                navigate('/')


            } catch (error) {
                console.error('Erreur lors de la mise à jour du portefeuille :', error);
                alert('Erreur lors de la mise à jour du portefeuille');
                navigate('/')
                onError(error);

            }
        };

        handleUpdateWallet();
    }, [id, amount, onUpdateSuccess, onError]);

    return null;
};

export default WalletUpdateComponent;

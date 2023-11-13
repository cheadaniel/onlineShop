import React, { useEffect } from 'react';
import { useParams } from 'react-router-dom';
import axios from 'axios';

const WalletUpdateComponent = ({ onUpdateSuccess, onError }) => {
    const { id, amount } = useParams();

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

                alert('Portefeuille mis à jour avec succès');
                window.location.href = '/';
                onUpdateSuccess(response.data);


            } catch (error) {
                console.error('Erreur lors de la mise à jour du portefeuille :', error);
                alert('Erreur lors de la mise à jour du portefeuille');
                window.location.href = '/';
                onError(error);

            }
        };

        handleUpdateWallet();
    }, [id, amount, onUpdateSuccess, onError]);

    return null;
};

export default WalletUpdateComponent;

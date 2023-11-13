import React, { useState, useEffect } from 'react';
import axios from 'axios';
import { useNavigate, useParams } from 'react-router-dom';
import ErrorComponent from '../../assets/Error/Error';

const ProductForm = () => {
    const navigate = useNavigate();
    const { productId } = useParams();
    const [error, setError] = useState(null);

    const [formData, setFormData] = useState({
        Name: '',
        Price: 0,
        Inventory: 0,
    });

    useEffect(() => {
        // Si un productId est fourni, chargez les données du produit
        if (productId) {
            axios.get(`http://localhost:8000/api/products/${productId}`)
                .then(response => {
                    setFormData(response.data)
                    console.log(response.data);

                }
                )
                .catch(error => {
                    console.error('Error fetching product details for edit:', error);
                    setError('Error fetching product details for edit.');
                });
        }
    }, [productId]);

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
                setError('Vous devez être connecté pour créer ou modifier un produit.');
                return;
            }

            // Convertir la valeur de "Inventory" en nombre entier
            const formDataWithIntInventory = {
                ...formData,
                Inventory: parseInt(formData.Inventory, 10), // Convertir en nombre entier
            };

            const headers = {
                Authorization: `Bearer ${token}`,
                'Content-Type': 'application/json',
            };

            // Si un productId est fourni, effectue une requête de mise à jour
            // Sinon, effectue une requête de création
            const response = productId
                ? await axios.put(`http://localhost:8000/api/products/update/${productId}`, formDataWithIntInventory, { headers })
                : await axios.post(`http://localhost:8000/api/products/create`, formDataWithIntInventory, { headers });
            navigate(`/products`);

        } catch (error) {
            setError('Une erreur s\'est produite lors de la création ou de la modification du produit.');
            console.error('Error creating or updating product:', error);
        }
    };

    return (
        <div>
            <h2>{productId ? 'Modifier' : 'Ajouter'} un produit</h2>
            {error && <ErrorComponent message={error} />}
            <form onSubmit={handleSubmit}>
                <label>
                    Nom:
                    <input
                        type="text"
                        name="Name"
                        value={formData.Name}
                        onChange={handleChange}
                        required
                    />
                </label>
                <br />
                <label>
                    Prix:
                    <input
                        type="number"
                        name="Price"
                        value={formData.Price}
                        onChange={handleChange}
                        required
                    />
                </label>
                <br />
                <label>
                    Inventory:
                    <input
                        type="number"
                        name="Inventory"
                        value={formData.Inventory}
                        onChange={handleChange}
                        required
                    />
                </label>
                <br />
                <button type="submit">{productId ? 'Modifier' : 'Créer'} le produit</button>
            </form>
        </div>
    );
};

export default ProductForm;

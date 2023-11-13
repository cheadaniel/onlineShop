import React, { useState } from 'react';
import axios from 'axios';
import { useDispatch, useSelector } from 'react-redux';
import { addToCart, removeFromCart, resetCart } from '../../app/features/cart/cartSlice';


import './shoppingCart.css'
import ErrorComponent from '../assets/Error/Error';

const ShoppingCart = () => {
    const dispatch = useDispatch();
    const cartState = useSelector((state) => state.cart);
    const [error, setError] = useState(null);


    // console.log(cartState);

    const handleIncreaseQuantity = (productId, productPrice) => {
        // Dispatche l'action addToCart avec la nouvelle quantité
        dispatch(addToCart({ product_id: productId, product_price: productPrice, quantity: 1 }));
    };

    const handleDecreaseQuantity = (productId, productPrice) => {
        // Recherche le produit dans le panier
        const existingProduct = cartState.products.find((product) => product.product_id === productId);

        if (existingProduct) {
            // Si la quantité est supérieure à 1, diminue la quantité
            if (existingProduct.quantity > 1) {
                dispatch(addToCart({ product_id: productId, product_price: productPrice, quantity: -1 }));
            } else {
                // Si la quantité est égale à 1, supprime le produit du panier
                dispatch(removeFromCart(productId));
            }
        }
    };

    const handleRemoveFromCart = (productId) => {
        dispatch(removeFromCart(productId));
    };

    const handleCheckout = (productsJson) => {
        const token = localStorage.getItem('token');

        if (!token) {
            console.error('JWT token not found');
            setError('Vous devez être connecté pour valider la commande.');
            return;
        }

        const headers = {
            Authorization: `Bearer ${token}`,
            'Content-Type': 'application/json',
        };

        const commandData = productsJson

        axios.post('http://localhost:8000/api/commands/create', commandData, { headers })
            .then(response => {
                console.log(response.data.message);
                // Réinitialiser le panier après une commande réussie
                dispatch(resetCart());
            })
            .catch(error => {
                console.error('API error:', error.response.data.message);

                if (error.response.status === 401) {
                    alert('Vous devez être connecté pour valider la commande.');
                } else if (error.response.status === 404) {
                    alert('Utilisateur non trouvé.');
                } else if (error.response.status === 400) {
                    alert('Fonds insuffisants dans votre portefeuille ou Rupture de stock d\'un des produits.');
                } else {
                    alert('Une erreur inattendue s\'est produite.');
                }
            });
    };

    return (
        <div className="shopping-cart">
            <div className="shopping-cart-container">
                {error && <ErrorComponent message={error} />}
                <h2 className="cart-heading">Mon panier</h2>
                <p className="total-price">Prix total: {cartState.total_price} €</p>

                <h3 className="cart-heading-products">Produits présents dans le panier:</h3>
                <table className="cart-table">
                    <thead>
                        <tr>
                            <th>Nom du produit</th>
                            <th>Prix unitaire</th>
                            <th>Quantité</th>
                            <th>Prix total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {cartState.products.map((product) => (
                            <tr key={product.product_id}>
                                <td>{product.name}</td>
                                <td>{product.product_price} €</td>
                                <td>{product.quantity}</td>
                                <td>{product.total_price} €</td>
                                <td>
                                    <button className="cart-button" onClick={() => handleDecreaseQuantity(product.product_id, product.product_price)}>-</button>
                                    <button className="cart-button" onClick={() => handleIncreaseQuantity(product.product_id, product.product_price)}>+</button>
                                    <button className="cart-button" onClick={() => handleRemoveFromCart(product.product_id)}>Supprimer</button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                <button className="cart-button" onClick={() => handleCheckout(cartState)}>Valider la commande</button>
            </div>
        </div>
    );
};


export default ShoppingCart;



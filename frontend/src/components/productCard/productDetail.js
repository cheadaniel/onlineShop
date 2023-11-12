import React, { useState, useEffect } from 'react';
import { Link, useParams } from 'react-router-dom';
import axios from 'axios';
import './productDetail.css'; // Importez le fichier CSS


const ProductDetail = () => {
    const { id } = useParams();
    const [product, setProduct] = useState(null);

    useEffect(() => {
        // Vérifier si l'ID est présent dans l'URL
        if (id) {
            // Effectuer une requête pour récupérer les détails du produit avec l'ID
            axios.get(`http://localhost:8000/api/products/${id}`)
                .then(response => setProduct(response.data))
                .catch(error => console.error('Error fetching product details:', error));
        }
    }, [id]);

    if (!product) {
        return <div>Loading...</div>;
    }

    return (
        <div>
            <div key={product.id} className="card">
                <img src={`/images/t-shirt-blanc.jpg`} alt={product.Name} />
                <div className="card-body">
                    <div className="title">{product.Name}</div>
                    <div className="price">{product.Price} €</div>
                    <div className="inventory">Quantité disponible: {product.Inventory}</div>
                    <Link to={`/products/${product.id}`} className="product-link">
                        Voir le produit
                    </Link>
                    {product.Inventory > 0 && (
                        <button className="add-to-cart">
                            Ajouter au panier
                        </button>
                    )}
                </div>
            </div>
            <Link to="/products" className="back-to-list-button">
                Retour à la liste des produits
            </Link>
        </div>
    );
};

export default ProductDetail;

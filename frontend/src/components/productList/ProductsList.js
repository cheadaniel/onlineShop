import React, { useEffect, useState } from 'react';
import axios from 'axios';
import './ProductsList.css';

const ProductsList = () => {
  const [products, setProducts] = useState([]);

  useEffect(() => {
    // Effectue une requête GET à votre API Symfony pour obtenir la liste des produits
    axios.get('http://localhost:8000/api/products')
      .then(response => setProducts(response.data))
      .catch(error => console.error('Error fetching products:', error));
  }, []);

  return (
    <div className="product-list">
      <h2 className="product-list-title">Liste des produits</h2>
      <div className="product-list-container">
        {products.map(product => (
          <div key={product.id} className="card">
            <img src={'/images/t-shirt-blanc.jpg'} alt={product.name} />
            <div className="card-body">
              <div className="title">{product.Name}</div>
              <div className="price">${product.Price}</div>
              <div className="inventory">Inventory: {product.Inventory}</div>
              <a href={`#/product/${product.id}`} className="product-link">
                Voir le produit
              </a>
              {product.Inventory > 0 && (
                <button className="add-to-cart">
                  Ajouter au panier
                </button>
              )}
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default ProductsList;

// ProductsList.js
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import ProductCard from '../productCard/productCard';
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
    <div className="main">
      <h2 className="product-list-title">Liste des produits</h2>
      <div className="product-list-container">
        {products.map(product => (
          <ProductCard key={product.id} product={product} />
        ))}
      </div>

    </div>
  );
};

export default ProductsList;

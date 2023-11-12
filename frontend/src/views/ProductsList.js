import React, { useEffect, useState } from 'react';
import axios from 'axios';

const ProductsList = () => {
  const [products, setProducts] = useState([]);

  useEffect(() => {
    // Effectue une requête GET à votre API Symfony pour obtenir la liste des produits
    axios.get('http://localhost:8000/api/products')
      .then(response => setProducts(response.data))
      .catch(error => console.error('Error fetching products:', error));
  }, []);

  return (
    <div>
      <h2>Liste des produits</h2>
      <ul>
        {products.map(product => (
          <li key={product.id}>
            <strong>{product.Name}</strong> - {product.Price} €
          </li>
        ))}
      </ul>
    </div>
  );
};

export default ProductsList;

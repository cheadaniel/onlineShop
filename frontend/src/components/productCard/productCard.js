import React from 'react';
import { Link } from 'react-router-dom';
import { useDispatch } from 'react-redux';
import { addToCart } from '../../app/features/cart/cartSlice';

const ProductCard = ({ product }) => {
  const dispatch = useDispatch();


  if (!product) {
    return <div>Loading...</div>;
  }

  const handleAddToCart = () => {
    dispatch(addToCart({
      product_id: product.id,
      quantity: 1, // Vous pouvez ajuster la quantité selon vos besoins
      name: product.Name,
      product_price: product.Price
    }));
  };

  return (
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
          <button className="add-to-cart" onClick={handleAddToCart}>
            Ajouter au panier
          </button>
        )}
      </div>
    </div>
  );
};

export default ProductCard;

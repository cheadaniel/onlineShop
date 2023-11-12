import { Link } from 'react-router-dom';

const ProductCard = ({ product }) => {
  if (!product) {
    return <div>Loading...</div>;
  }

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
          <button className="add-to-cart">
            Ajouter au panier
          </button>
        )}
      </div>
    </div>
  );
};

export default ProductCard;

// Home.js
import React, { useEffect, useState } from 'react';
import axios from 'axios';
import ProductCard from '../../productCard/productCard';

const Home = () => {
    const [products, setProducts] = useState([]);

    useEffect(() => {
        const fetchProduct = async (productId) => {
            try {
                const response = await axios.get(`http://localhost:8000/api/products/${productId}`);
                const productData = response.data;
                setProducts((prevProducts) => [...prevProducts, productData]);
            } catch (error) {
                console.error(`Error fetching product with ID ${productId}:`, error);
            }
        };

        const fetchProducts = async () => {
            const productIds = [1, 2, 3, 4, 5,6];

            for (const productId of productIds) {
                await fetchProduct(productId);
            }
        };

        fetchProducts();
        removeDuplicateObject()
    }, []);

    const removeDuplicateObject = () => {
        // Utiliser un Set pour suivre les identifiants uniques
        const uniqueIds = new Set();

        // Filtrer le tableau pour inclure uniquement les objets avec des identifiants uniques
        const uniqueObjects = products.filter((obj) => {
            // Si l'identifiant n'est pas déjà dans le Set, l'ajouter et renvoyer true pour inclure l'objet
            if (!uniqueIds.has(obj.id)) {
                uniqueIds.add(obj.id);
                return true;
            }
            // Si l'identifiant est déjà présent, renvoyer false pour exclure l'objet
            return false;
        });
    }


    return (
        <div>
            <div className="main">
                <h2 className="product-list-title">Produits en vedettes</h2>
                <div className="product-list-container">
                    {products.map((product, index) => (
                        // Vérifier si l'identifiant n'est pas le même qu'un autre élément précédent dans le tableau
                        !products.slice(0, index).some(prevProduct => prevProduct.id === product.id) ? (
                            <ProductCard key={product.id} product={product} />
                        ) : null
                    ))}
                </div>
            </div>
        </div>
    );
};

export default Home;


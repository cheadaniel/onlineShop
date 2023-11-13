import { createSlice } from '@reduxjs/toolkit';

const cartSlice = createSlice({
    name: 'cart',
    initialState: {
        user_id: null, // Initialiser à null jusqu'à ce que l'utilisateur se connecte
        status: 'En cours de préparation',
        products: [],
        total_price: '0.00',
    },
    reducers: {
        setUserId: (state, action) => {
            state.user_id = action.payload;
        },
        addToCart: (state, action) => {
            const { product_id, quantity, name, product_price } = action.payload;
            const existingProduct = state.products.find((product) => product.product_id === product_id);

            if (existingProduct) {
                existingProduct.quantity += quantity;
                existingProduct.total_price = (parseFloat(existingProduct.total_price) + parseFloat(product_price) * quantity).toFixed(2);
            } else {
                state.products.push({
                    product_id,
                    quantity,
                    total_price: (parseFloat(product_price) * quantity).toFixed(2),
                    name,
                    product_price,
                });
            }

            // Recalculer le total_price en fonction des produits actuels dans le panier
            state.total_price = state.products.reduce((total, product) => total + parseFloat(product.total_price), 0).toFixed(2);
        },
        removeFromCart: (state, action) => {
            const productId = action.payload;
            const removedProductIndex = state.products.findIndex(product => product.product_id === productId);

            if (removedProductIndex !== -1) {
                const removedProduct = state.products[removedProductIndex];
                state.total_price = (parseFloat(state.total_price) - parseFloat(removedProduct.total_price)).toFixed(2);

                state.products.splice(removedProductIndex, 1);
            }
        },
        resetCart: (state) => {
            state.products = [];
            state.total_price = 0;
        }
    },
});

export const { setUserId, addToCart, removeFromCart, resetCart } = cartSlice.actions;
export default cartSlice.reducer;

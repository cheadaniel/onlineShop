import React, { useState } from 'react';
import { useDispatch } from 'react-redux';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import axios from 'axios';

import Header from './components/assets/Header/header';
import Footer from './components/assets/Footer/footer';
import ProductsList from './components/productList/ProductsList';
import ProductDetail from './components/productCard/productDetail';
import LoginForm from './components/assets/login/Login';
import RegistrationForm from './components/assets/Register/Register';
import APIDocumentation from './components/APIDocumentation/APIDoc';
import Home from './components/assets/Home/Home';
import { setUserId } from './app/features/cart/cartSlice';
import ShoppingCart from './components/shoppingCart/shoppingCart';

const App = () => {
  const dispatch = useDispatch();
  const [token, setToken] = useState(null);

  const handleLogin = async (newToken) => {
    setToken(newToken);

    try {
      // Faire une requête pour obtenir les informations de l'utilisateur après la connexion
      const response = await axios.get('http://localhost:8000/api/user', {
        headers: {
          Authorization: `Bearer ${newToken}`,
        },
      });

      // Extraire l'ID de l'utilisateur depuis la réponse
      const userId = response.data.id;
      // console.log(userId)

      // Dispatch de l'action setUserId pour mettre à jour l'ID de l'utilisateur dans l'état Redux
      dispatch(setUserId(userId));
    } catch (error) {
      console.error('Erreur lors de la récupération des informations utilisateur après la connexion :', error);
    }
  };

  const handleLogout = () => {
    setToken(null);
  };

  const isAuthenticated = token !== null;
  const isAdmin = true;

  return (
    <Router>
      <Header isAuthenticated={isAuthenticated} isAdmin={isAdmin} onLogout={handleLogout} />
      <Routes>
        <Route path="/" element={<Home />} />

        <Route path="/products" element={<ProductsList />} />
        <Route path="/products/:id" element={<ProductDetail />} />
        <Route
          path="/login"
          element={<LoginForm onLogin={handleLogin} />}
        />
        <Route path="/register" element={<RegistrationForm />} />
        <Route path="/api/doc" element={<APIDocumentation />} />
        <Route path="/panier" element={<ShoppingCart />} />
        
      </Routes>
      <main>
      </main>
      <Footer />
    </Router>
  );
};

export default App;
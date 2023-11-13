import React, { useEffect, useState } from 'react';
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
import CommandList from './components/Command/CommandList';
import CommandDetail from './components/Command/CommandDetail';
import UserProfile from './components/User/User';
import UserProfiles from './components/User/Users';
import WalletUpdateComponent from './components/User/UpdateWallet';
import AdminRouteGuard from './components/AdminRouteGuard/AdminRouteGuard';
import CommandsList from './components/Command/Commands';



const App = () => {
  const dispatch = useDispatch();
  const [token, setToken] = useState(null);
  const [isAdmin, setIsAdmin] = useState(false);
  const [userID, setUserID] = useState(null)

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
      const userRoles = response.data.roles;


      const userIsAdmin = userRoles.includes('ROLE_ADMIN');
      setIsAdmin(userIsAdmin);
      setUserID(userId)
      // console.log(userId)

      // Dispatch de l'action setUserId pour mettre à jour l'ID de l'utilisateur dans l'état Redux
      dispatch(setUserId(userId));
    } catch (error) {
      console.error('Erreur lors de la récupération des informations utilisateur après la connexion :', error);
    }
  };

  const handleLogout = () => {
    setToken(null);
    setUserID(null)
    setIsAdmin(false)
  };


  const isAuthenticated = token !== null;

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
        <Route path="/my_orders" element={<CommandList userId={userID} />} />
        <Route path="/command/:id" element={<CommandDetail userId={userID} />} />
        <Route path="/my_account" element={<UserProfile userId={userID} />} />
        <Route path="/update-wallet/:id/:amount" element={<AdminRouteGuard isAdmin={isAdmin}><WalletUpdateComponent /></AdminRouteGuard>} />
        <Route path="/users" element={<AdminRouteGuard isAdmin={isAdmin}><UserProfiles userId={userID} /></AdminRouteGuard>} />
        <Route path="/commands" element={<AdminRouteGuard isAdmin={isAdmin}><CommandsList userId={userID} /></AdminRouteGuard>} />

      </Routes>
      <main>
      </main>
      <Footer />
    </Router >
  );
};

export default App;
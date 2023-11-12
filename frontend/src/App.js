import React, { useState } from 'react';
import { BrowserRouter as Router, Route, Routes } from 'react-router-dom';
import Header from './components/assets/Header/header';
import Footer from './components/assets/Footer/footer';
import ProductsList from './components/productList/ProductsList';
import ProductDetail from './components/productCard/productDetail';
import LoginForm from './components/assets/login/Login';
import APIDocumentation from './components/APIDocumentation/APIDoc';
import Home from './components/assets/Home/Home';

const App = () => {
  const [token, setToken] = useState(null);

  const handleLogin = (newToken) => {
    setToken(newToken);
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
        <Route path="/api/doc" element={<APIDocumentation />} />
      </Routes>
      <main>
      </main>
      <Footer />
    </Router>
  );
};

export default App;
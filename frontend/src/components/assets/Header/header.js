import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import LogoutButton from '../logout/Logout';
import './Header.css'

const Header = ({ isAuthenticated, isAdmin, onLogout }) => {
    const navigate = useNavigate();

    const handleLogout = () => {
        // Ajoutez votre logique de déconnexion ici
        onLogout();

        // Utilisez le hook navigate pour effectuer la redirection vers la page d'accueil
        navigate('/');
    };

    return (
        <header>
            <h1>
                <Link to="/">onlineShop</Link>
            </h1>
            <nav>
                <ul>
                    <li>
                        <Link to="/products">Produits</Link>
                    </li>
                    {isAuthenticated ? (
                        <>
                            {isAdmin && (
                                <>
                                    <li>
                                        <Link to="/add">Ajouter</Link>
                                    </li>
                                    <li>
                                        <Link to="/account">Compte</Link>
                                    </li>
                                </>
                            )}
                            {!isAdmin && (
                                <>
                                    <li>
                                        <Link to="/account">Mon compte</Link>
                                    </li>
                                    <li>
                                        <Link to="/orders">Mes commandes</Link>
                                    </li>
                                </>
                            )}
                            <li>
                                <Link to="/cart">Panier</Link>
                            </li>
                            <li>
                                <LogoutButton onLogout={handleLogout} />
                            </li>
                        </>
                    ) : (
                        <>
                            <li>
                                <Link to="/login">Connexion</Link>
                            </li>
                            <li>
                                <Link to="/cart">Panier</Link>
                            </li>
                        </>
                    )}
                </ul>
            </nav>
        </header>
    );
};

export default Header;

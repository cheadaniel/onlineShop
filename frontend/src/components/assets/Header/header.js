import React from 'react';
import { Link, useNavigate } from 'react-router-dom';
import LogoutButton from '../logout/Logout';
import './Header.css'

const Header = ({ isAuthenticated, isAdmin, onLogout }) => {
    const navigate = useNavigate();

    const handleLogout = () => {
        onLogout();
        navigate('/');
    };

    return (
        <header>
            <h1>
                <Link to="/">Online Shop</Link>
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
                                        <Link to="/account">Comptes</Link>
                                    </li>
                                </>
                            )}
                            {!isAdmin && (
                                <>
                                    <li>
                                        <Link to="/account">Mon compte</Link>
                                    </li>
                                    <li>
                                        <Link to="/my_orders">Mes commandes</Link>
                                    </li>
                                </>
                            )}
                            <li>
                                <Link to="/panier">Panier</Link>
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
                                <Link to="/register">S'inscrire</Link>
                            </li>
                            <li>
                                <Link to="/panier">Panier</Link>
                            </li>
                        </>
                    )}
                </ul>
            </nav>
        </header>
    );
};

export default Header;

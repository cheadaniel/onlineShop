import React from 'react';
import { Link } from 'react-router-dom';
import './footer.css'
// import APIDocumentation from './components/APIDocumentation/APIDoc';


const Footer = () => {
  return (
    <footer>
      <p>&copy; 2023-2024 Tous droits réservés.</p>
      <p>
        <Link to="/api/doc">Documentation API</Link>
      </p>
    </footer>
  );
};

export default Footer;

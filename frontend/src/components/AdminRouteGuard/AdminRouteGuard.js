// AdminRouteGuard.js
import React, { useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

const AdminRouteGuard = ({ isAdmin, children }) => {
  const navigate = useNavigate();

  useEffect(() => {
    if (!isAdmin) {
      // Rediriger l'utilisateur vers une autre page s'il n'est pas administrateur
      navigate('/');
    }
  }, [isAdmin, navigate]);

  // Rendre l'enfant uniquement si l'utilisateur est administrateur
  return isAdmin ? <>{children}</> : null;
};

export default AdminRouteGuard;

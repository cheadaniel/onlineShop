import React from 'react';
import './APIDoc.css'

const APIDocumentation = () => {
    return (
        <div className="api-doc-container">
            <h2 className="api-doc-title">Documentation de l'API</h2>
            <p>
                Consultez la documentation complète{' '}
                <a
                    className="api-doc-link"
                    href="http://localhost:8000/api/doc"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    ici
                </a>
                .
            </p>
        </div>
    );
};

export default APIDocumentation;

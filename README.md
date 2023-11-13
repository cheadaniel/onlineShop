# onlineShop

Bienvenue sur la plateforme de vente en ligne de vêtements ! Cette application combine les technologies Symfony et React pour offrir une expérience utilisateur interactive et agréable. Explorez les fonctionnalités ci-dessous pour découvrir tout ce que notre plateforme peut vous offrir.

## Fonctionnalités Principales

### Utilisateur
- **Inscription et Connexion :** Les utilisateurs peuvent créer un compte en fournissant leurs informations ou se connecter avec un compte existant.
- **Liste de Produits :** Explorez une liste complète de vêtements disponibles sur la plateforme dans la section "Produits". Consultez les détails de chaque article et trouvez vos coups de cœur.
- **Panier d'Achat :** Ajoutez des articles à votre panier d'achat pour constituer votre sélection de vêtements. Modifiez les quantités et supprimez des articles directement depuis votre panier.
- **Passer une Commande :** Une fois votre panier prêt, passez votre commande. Suivez l'état de vos commandes depuis votre espace utilisateur.
- **Profil Utilisateur :** Consultez et éditez votre profil utilisateur. Modifiez votre adresse, consultez votre historique d'achats et suivez vos informations personnelles.

### Administrateur
- **Gestion des Produits :** L'administrateur a le pouvoir de créer de nouveaux produits et de les éditer. Modifiez les détails des articles pour les maintenir à jour.
- **Gestion des Commandes :** Modifiez le statut des commandes pour refléter leur progression. Tenez les utilisateurs informés de l'état de leurs achats.
- **Gestion des Utilisateurs :** Accédez aux informations des utilisateurs, éditez leurs profils et ajustez leur porte-monnaie.

## Configuration du Projet

Pour exécuter localement le projet Symfony et React, suivez ces étapes :

1. **Clonage du Projet :** Clonez le repository depuis GitHub.
    ```bash
    git clone https://github.com/cheadaniel/onlineShop
    ```
2. **Installation des Dépendances :** Dans les répertoires Symfony et React, exécutez les commandes suivantes.
    ```bash
    # Symfony (dans le répertoire du projet Symfony)
    composer install

    # React (dans le répertoire du projet React)
    npm install
    ```
3. **Base de Données :** Configurez et migrez la base de données.
    ```bash
    # Symfony (dans le répertoire du projet Symfony)
    php bin/console doctrine:migrations:migrate
    ```
4. **Démarrez les Serveurs :**
    ```bash
    # Symfony (dans le répertoire du projet Symfony)
    symfony server:start

    # React (dans le répertoire du projet React)
    npm start
    ```
5. **Accès à l'Application :** Ouvrez votre navigateur et accédez à http://localhost:8000 pour l'application Symfony et à http://localhost:3000 pour l'application React.

Une documentation détaillée de chaque requête est disponible à [http://localhost:3000/api/doc](http://localhost:3000/api/doc) et [http://localhost:8000/api/doc](http://localhost:8000/api/doc).


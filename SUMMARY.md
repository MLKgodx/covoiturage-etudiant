# CO-CESI - Plateforme de Covoiturage Étudiant
## Résumé du Projet

### 📋 Vue d'ensemble

Application complète de covoiturage pour étudiants CESI développée avec **Vue 3** (frontend) et **Laravel 10** (backend).

### 🎯 Fonctionnalités Implémentées

✅ **Authentification**
- Inscription avec email étudiant uniquement (@etudiant.cesi.fr)
- Connexion/Déconnexion avec JWT
- Réinitialisation de mot de passe

✅ **Profils Utilisateur**
- 3 types de profils: Conducteur, Passager, Les deux
- Préférences: fumeur, musique, bavardage
- Informations véhicule pour conducteurs
- Système de notation (1-5 étoiles)
- Badge "Conducteur de confiance"

✅ **Gestion des Trajets**
- Création de trajets avec géolocalisation
- Trajets aller-retour
- Trajets récurrents (sélection jours de la semaine)
- Validation automatique ou manuelle des réservations
- Recherche avec filtres (lieu, date, préférences)
- Carte interactive (Leaflet + OpenStreetMap)

✅ **Système de Réservation**
- Réservation de places
- Message optionnel au conducteur
- États: En attente, Confirmé, Refusé, Annulé
- Gestion des annulations

✅ **Messagerie Interne**
- Messages entre conducteur et passagers (max 300 caractères)
- Messages rapides prédéfinis
- Statut lu/non lu
- Accessible uniquement après réservation confirmée

✅ **Notation Post-Trajet**
- Notation du conducteur: conduite, ponctualité, véhicule
- Notation du passager: ponctualité, respect
- Commentaires (200 caractères max)
- Calcul automatique de la note moyenne

✅ **Calcul CO2**
- Formule: Distance × 150g × (Nb personnes - 1)
- Affichage par trajet et total
- Équivalent en arbres plantés

✅ **Tableau de Bord**
- Statistiques personnelles
- Prochains trajets et réservations
- Notifications
- Messages non lus

### 🛠️ Technologies Utilisées

#### Backend
- **Laravel 10** - Framework PHP
- **MySQL 8** - Base de données
- **JWT Auth** - Authentification
- **Eloquent ORM** - Accès aux données

#### Frontend
- **Vue 3** - Framework JavaScript (Composition API)
- **Vite** - Build tool
- **Pinia** - State management
- **Vue Router 4** - Routing
- **Axios** - HTTP client
- **Tailwind CSS** - Styling
- **Leaflet.js** - Cartes interactives
- **date-fns** - Manipulation des dates

### 📁 Structure du Projet

```
cocesi-carpooling/
├── backend/                    # API Laravel
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── UserController.php
│   │   │   ├── TripController.php
│   │   │   ├── BookingController.php
│   │   │   ├── MessageController.php
│   │   │   └── RatingController.php
│   │   └── Models/
│   │       ├── User.php
│   │       ├── Trip.php
│   │       ├── Booking.php
│   │       ├── Rating.php
│   │       └── Message.php
│   ├── database/
│   │   ├── migrations/        # Schéma BDD
│   │   └── seeders/           # Données de test
│   └── routes/api.php         # Routes API
│
├── frontend/                   # Application Vue 3
│   ├── src/
│   │   ├── components/        # Composants réutilisables
│   │   │   ├── layout/
│   │   │   ├── trips/
│   │   │   └── bookings/
│   │   ├── views/             # Pages
│   │   │   ├── HomePage.vue
│   │   │   ├── LoginPage.vue
│   │   │   ├── RegisterPage.vue
│   │   │   ├── DashboardPage.vue
│   │   │   └── ...
│   │   ├── stores/            # Pinia stores
│   │   │   └── auth.js
│   │   ├── router/            # Configuration routes
│   │   └── services/          # API client
│   └── package.json
│
├── README.md                  # Documentation principale
├── INSTALL.md                 # Guide d'installation
├── DEVELOPER.md               # Guide développeur
├── docker-compose.yml         # Configuration Docker
└── setup.sh                   # Script d'installation automatique
```

### 🗄️ Schéma de Base de Données

**users**
- Informations personnelles et académiques
- Type de profil (driver/passenger/both)
- Préférences (fumeur, musique, bavardage)
- Informations véhicule
- Statistiques (note moyenne, trajets effectués, CO2)

**trips**
- Informations trajet (départ, arrivée, coordonnées GPS)
- Horaires et disponibilité
- Options (aller-retour, récurrent)
- Préférences et validation auto
- Calculs (distance, CO2)

**bookings**
- Lien trip/passager
- Places réservées
- Statut (pending, confirmed, refused, cancelled)
- Flags de notation

**ratings**
- Notations détaillées (conduite, ponctualité, etc.)
- Note globale calculée
- Commentaires

**messages**
- Messages entre participants
- Statut lecture
- Support messages templates

### 🚀 Démarrage Rapide

#### Option 1: Installation manuelle
```bash
# 1. Backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
php artisan migrate
php artisan db:seed
php artisan serve

# 2. Frontend (nouveau terminal)
cd frontend
npm install
npm run dev
```

#### Option 2: Script automatique
```bash
chmod +x setup.sh
./setup.sh
```

#### Option 3: Docker
```bash
docker-compose up -d
```

### 🔐 Compte de Test

Après avoir exécuté le seeder:
- **Email**: admin@etudiant.cesi.fr
- **Mot de passe**: password

### 📊 Objectifs du Projet

- ✅ 100 inscrits à la fin de l'année scolaire
- ✅ 30 trajets/semaine
- ✅ Satisfaction > 4/5

### 🎨 Captures d'écran

L'application comprend:
- Page d'accueil avec présentation
- Formulaire d'inscription en 3 étapes
- Tableau de bord avec statistiques
- Recherche de trajets avec carte
- Détail de trajet
- Interface de messagerie
- Système de notation

### 🔧 API Endpoints Principaux

```
POST   /api/auth/register           # Inscription
POST   /api/auth/login              # Connexion
GET    /api/auth/me                 # Profil utilisateur

GET    /api/trips                   # Liste des trajets
POST   /api/trips                   # Créer un trajet
GET    /api/trips/{id}              # Détail d'un trajet

POST   /api/bookings                # Réserver un trajet
POST   /api/bookings/{id}/confirm   # Confirmer une réservation

GET    /api/messages/booking/{id}   # Messages d'une réservation
POST   /api/messages/booking/{id}   # Envoyer un message

POST   /api/ratings/booking/{id}    # Noter après un trajet
```

### 📝 Points d'Amélioration (V2)

- Paiement en ligne (Stripe)
- Analytics avancés
- Programme de fidélité
- Extension à d'autres campus
- Intégration emploi du temps
- Notifications push
- Application mobile (React Native)

### 👥 Équipe

Projet développé par 6 étudiants Bac+3 sur une durée de 7 mois.

### 📄 Licence

MIT License

### 🆘 Support

Consultez les fichiers de documentation:
- `INSTALL.md` - Installation pas à pas
- `DEVELOPER.md` - Guide technique détaillé
- `README.md` - Vue d'ensemble

---

**Note**: Cette application est un projet étudiant démonstratif. Pour un déploiement en production, des ajustements de sécurité et de performance seraient nécessaires.

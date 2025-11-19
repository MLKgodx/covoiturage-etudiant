# CO-CESI - Plateforme de Covoiturage Étudiant

## 🚗 Description
Plateforme web de covoiturage entre étudiants avec système de match, géolocalisation et calcul d'impact CO2.

## 📋 Prérequis
- PHP 8.1+
- Composer
- Node.js 16+
- MySQL 8.0+

## 🛠️ Installation

### Backend (Laravel)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### Frontend (Vue 3)
```bash
cd frontend
npm install
npm run dev
```

## 📁 Structure du Projet
```
cocesi-carpooling/
├── backend/           # Laravel API
│   ├── app/
│   │   ├── Models/
│   │   ├── Http/Controllers/
│   │   └── Services/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── routes/
└── frontend/          # Vue 3 + Vite
    ├── src/
    │   ├── components/
    │   ├── views/
    │   ├── router/
    │   ├── stores/
    │   └── services/
    └── public/
```

## 🎯 Fonctionnalités V1
- ✅ Authentification étudiante
- ✅ Profils conducteur/passager
- ✅ Création de trajets
- ✅ Recherche et réservation
- ✅ Messagerie interne
- ✅ Géolocalisation (OpenStreetMap)
- ✅ Système de notation
- ✅ Calcul CO2
- ✅ Tableau de bord

## 🔧 Technologies
- **Backend**: Laravel 10, MySQL, JWT
- **Frontend**: Vue 3, Vite, Pinia, Vue Router
- **Maps**: Leaflet.js + OpenStreetMap
- **UI**: Tailwind CSS

## 👥 Équipe
6 étudiants Bac+3 - Durée: 7 mois

## 📊 Objectifs
- 100 inscrits à la fin de l'année
- 30 trajets/semaine
- Satisfaction > 4/5

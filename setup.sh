#!/bin/bash

# CO-CESI Setup Script
# Ce script automatise l'installation et la configuration du projet

set -e  # Arrêter en cas d'erreur

echo "🚗 CO-CESI - Installation automatique"
echo "======================================"
echo ""

# Couleurs pour les messages
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Fonction pour afficher les messages
info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
    exit 1
}

# Vérifier les prérequis
info "Vérification des prérequis..."

command -v php >/dev/null 2>&1 || error "PHP n'est pas installé"
command -v composer >/dev/null 2>&1 || error "Composer n'est pas installé"
command -v node >/dev/null 2>&1 || error "Node.js n'est pas installé"
command -v npm >/dev/null 2>&1 || error "npm n'est pas installé"
command -v mysql >/dev/null 2>&1 || warn "MySQL n'est pas dans le PATH (peut être normal)"

info "✓ Prérequis vérifiés"
echo ""

# Configuration Backend
info "Configuration du backend (Laravel)..."
cd backend

if [ ! -f composer.lock ]; then
    info "Installation des dépendances Composer..."
    composer install
else
    info "✓ Dépendances Composer déjà installées"
fi

if [ ! -f .env ]; then
    info "Création du fichier .env..."
    cp .env.example .env
    
    info "Génération de la clé d'application..."
    php artisan key:generate
    
    info "Génération de la clé JWT..."
    php artisan jwt:secret
    
    warn "⚠️  Veuillez configurer la base de données dans backend/.env"
    warn "   DB_DATABASE=cocesi_db"
    warn "   DB_USERNAME=votre_username"
    warn "   DB_PASSWORD=votre_password"
    echo ""
    
    read -p "Appuyez sur Entrée après avoir configuré la base de données..."
else
    info "✓ Fichier .env déjà configuré"
fi

# Demander si on doit créer la base de données
echo ""
read -p "Voulez-vous que le script crée la base de données ? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    read -p "Nom de la base de données [cocesi_db]: " DB_NAME
    DB_NAME=${DB_NAME:-cocesi_db}
    
    read -p "Utilisateur MySQL [root]: " DB_USER
    DB_USER=${DB_USER:-root}
    
    read -sp "Mot de passe MySQL: " DB_PASS
    echo
    
    info "Création de la base de données..."
    mysql -u "$DB_USER" -p"$DB_PASS" -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" || warn "Erreur lors de la création de la base"
fi

echo ""
info "Exécution des migrations..."
php artisan migrate --force

echo ""
read -p "Voulez-vous charger des données de test ? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    info "Chargement des données de test..."
    php artisan db:seed
    info "✓ Données de test chargées"
    info "  Compte test: admin@etudiant.cesi.fr / password"
fi

info "Création du lien symbolique pour le stockage..."
php artisan storage:link

info "✓ Backend configuré avec succès!"
cd ..

echo ""
echo ""

# Configuration Frontend
info "Configuration du frontend (Vue 3)..."
cd frontend

if [ ! -d node_modules ]; then
    info "Installation des dépendances npm..."
    npm install
else
    info "✓ Dépendances npm déjà installées"
fi

info "✓ Frontend configuré avec succès!"
cd ..

echo ""
echo ""
echo "================================================"
info "✅ Installation terminée avec succès!"
echo "================================================"
echo ""
echo "Pour démarrer l'application:"
echo ""
echo "Terminal 1 - Backend:"
echo "  cd backend"
echo "  php artisan serve"
echo ""
echo "Terminal 2 - Frontend:"
echo "  cd frontend"
echo "  npm run dev"
echo ""
echo "Puis ouvrez votre navigateur sur:"
echo "  - Frontend: http://localhost:3000"
echo "  - API:      http://localhost:8000"
echo ""
info "Bon développement! 🚀"

# Guide d'Installation - CO-CESI

## 📋 Prérequis

Avant de commencer, assurez-vous d'avoir installé:

- **PHP 8.1+** avec les extensions:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
- **Composer** (gestionnaire de dépendances PHP)
- **Node.js 16+** et **npm**
- **MySQL 8.0+** (ou MariaDB 10.3+)
- **Git**

## 🚀 Installation

### 1. Cloner le projet

```bash
git clone https://github.com/votre-repo/cocesi-carpooling.git
cd cocesi-carpooling
```

### 2. Configuration Backend (Laravel)

```bash
cd backend

# Installer les dépendances PHP
composer install

# Copier le fichier .env
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Générer la clé JWT
php artisan jwt:secret
```

### 3. Configuration Base de Données

Éditez le fichier `.env` et configurez votre base de données:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cocesi_db
DB_USERNAME=votre_username
DB_PASSWORD=votre_password
```

Créez la base de données:

```bash
# Sous MySQL
mysql -u root -p
CREATE DATABASE cocesi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

Exécutez les migrations:

```bash
php artisan migrate
```

### 4. Seed de la Base de Données (Optionnel)

Pour ajouter des données de test:

```bash
php artisan db:seed
```

### 5. Créer le lien symbolique pour le stockage

```bash
php artisan storage:link
```

### 6. Configuration Frontend (Vue 3)

```bash
cd ../frontend

# Installer les dépendances Node.js
npm install
```

### 7. Lancer l'Application

#### Backend (Terminal 1)
```bash
cd backend
php artisan serve
# L'API sera accessible sur http://localhost:8000
```

#### Frontend (Terminal 2)
```bash
cd frontend
npm run dev
# L'application sera accessible sur http://localhost:3000
```

## 📧 Configuration Email (Optionnel)

Pour activer l'envoi d'emails (vérification, réinitialisation mot de passe), configurez dans `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre_email@gmail.com
MAIL_PASSWORD=votre_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@cocesi.com
MAIL_FROM_NAME="CO-CESI"
```

## 🧪 Tests

### Backend Tests
```bash
cd backend
php artisan test
```

### Frontend Tests
```bash
cd frontend
npm run test
```

## 🏗️ Build Production

### Backend
```bash
cd backend
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Frontend
```bash
cd frontend
npm run build
# Les fichiers compilés seront dans /frontend/dist
```

## 🐛 Dépannage

### Erreur "No application encryption key has been specified"
```bash
php artisan key:generate
```

### Erreur de permissions (Linux/Mac)
```bash
chmod -R 755 storage bootstrap/cache
```

### Erreur JWT Token
```bash
php artisan jwt:secret
php artisan cache:clear
```

### Port déjà utilisé
```bash
# Backend - changer le port
php artisan serve --port=8001

# Frontend - éditer vite.config.js et changer le port
```

## 📚 Documentation API

Une fois le backend lancé, la documentation API est accessible sur:
- http://localhost:8000/api/health (Health check)

## 🔒 Sécurité

Pour la production:

1. Changez `APP_ENV=production` dans `.env`
2. Désactivez le debug: `APP_DEBUG=false`
3. Utilisez HTTPS
4. Configurez un pare-feu
5. Mettez en place des sauvegardes régulières
6. Utilisez des mots de passe forts pour la base de données

## 📞 Support

En cas de problème, contactez l'équipe de développement ou consultez la documentation:
- Laravel: https://laravel.com/docs
- Vue 3: https://vuejs.org/guide
- Vite: https://vitejs.dev/guide

## ✅ Vérification de l'Installation

Vérifiez que tout fonctionne:

1. Backend: http://localhost:8000/api/health (devrait retourner un JSON avec success: true)
2. Frontend: http://localhost:3000 (devrait afficher la page d'accueil)
3. Créez un compte test et testez les fonctionnalités principales

Bon développement ! 🚀

# 🚀 Démarrage Rapide - CO-CESI

## ⚡ Installation Express (5 minutes)

### Prérequis
- PHP 8.1+ avec MySQL
- Composer
- Node.js 16+ et npm
- Git

### Étape 1: Cloner le projet
```bash
git clone <votre-repo>
cd cocesi-carpooling
```

### Étape 2: Configuration automatique
```bash
chmod +x setup.sh
./setup.sh
```

Le script va:
- ✅ Installer les dépendances backend et frontend
- ✅ Configurer les fichiers d'environnement
- ✅ Créer la base de données
- ✅ Exécuter les migrations
- ✅ (Optionnel) Charger des données de test

### Étape 3: Lancer l'application

**Terminal 1 - Backend:**
```bash
cd backend
php artisan serve
```
→ API disponible sur http://localhost:8000

**Terminal 2 - Frontend:**
```bash
cd frontend
npm run dev
```
→ Application disponible sur http://localhost:3000

### Étape 4: Tester l'application

1. Ouvrez http://localhost:3000
2. Créez un compte avec un email @etudiant.cesi.fr
3. Ou utilisez le compte de test:
   - Email: `admin@etudiant.cesi.fr`
   - Mot de passe: `password`

---

## 🐳 Alternative: Docker (Encore plus rapide!)

```bash
# Lancer tous les services
docker-compose up -d

# Première fois seulement: migrations
docker-compose exec backend php artisan migrate --seed

# Accéder à l'application
# Frontend: http://localhost:3000
# Backend:  http://localhost:8000
# phpMyAdmin: http://localhost:8080
```

Arrêter:
```bash
docker-compose down
```

---

## 🎯 Premiers Pas

### Créer un compte conducteur
1. S'inscrire → Choisir "Conducteur" ou "Les deux"
2. Remplir les infos véhicule
3. Créer votre premier trajet

### Créer un compte passager
1. S'inscrire → Choisir "Passager" ou "Les deux"
2. Rechercher un trajet disponible
3. Réserver des places

### Tester la messagerie
1. Créer ou rejoindre un trajet
2. Une fois confirmé, accéder aux messages
3. Utiliser les messages rapides

### Tester le système de notation
1. Attendre qu'un trajet soit passé
2. Noter le conducteur/passager
3. Voir la note mise à jour sur le profil

---

## 📚 Documentation Complète

- **Installation détaillée**: Voir `INSTALL.md`
- **Guide développeur**: Voir `DEVELOPER.md`
- **Vue d'ensemble**: Voir `README.md`
- **Résumé complet**: Voir `SUMMARY.md`

---

## 🐛 Problèmes Courants

### Port déjà utilisé
```bash
# Backend - changer le port
php artisan serve --port=8001

# Frontend - éditer vite.config.js
server: { port: 3001 }
```

### Erreur de base de données
```bash
# Vérifier que MySQL est démarré
# Vérifier les credentials dans backend/.env
# Recréer la BDD:
mysql -u root -p
DROP DATABASE IF EXISTS cocesi_db;
CREATE DATABASE cocesi_db;
exit
php artisan migrate --seed
```

### Erreur JWT
```bash
cd backend
php artisan jwt:secret
php artisan cache:clear
```

### Modules npm manquants
```bash
cd frontend
rm -rf node_modules package-lock.json
npm install
```

---

## 💡 Astuces

### Réinitialiser la base de données
```bash
cd backend
php artisan migrate:fresh --seed
```

### Voir les logs en temps réel
```bash
# Backend
tail -f backend/storage/logs/laravel.log

# Frontend (dans le navigateur)
Console → Network tab
```

### Tester l'API directement
```bash
# Health check
curl http://localhost:8000/api/health

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@etudiant.cesi.fr","password":"password"}'
```

### Générer plus de données de test
```bash
cd backend
php artisan db:seed --class=DatabaseSeeder
```

---

## 🎨 Personnalisation

### Changer les couleurs (Tailwind)
Éditer `frontend/tailwind.config.js`:
```javascript
colors: {
  primary: {
    600: '#votre-couleur',
    // ...
  }
}
```

### Changer le nom de l'application
1. Backend: `backend/.env` → `APP_NAME`
2. Frontend: `frontend/index.html` → `<title>`

### Modifier le domaine email étudiant
Backend: `backend/.env` → `STUDENT_EMAIL_DOMAIN=@votre-domaine.fr`

---

## ✅ Checklist Avant Production

- [ ] Changer `APP_ENV=production` dans `.env`
- [ ] Désactiver `APP_DEBUG=false`
- [ ] Configurer HTTPS
- [ ] Changer tous les mots de passe par défaut
- [ ] Configurer l'envoi d'emails réels
- [ ] Mettre en place des sauvegardes automatiques
- [ ] Configurer un monitoring
- [ ] Tester sur mobile et différents navigateurs
- [ ] Activer la compression des assets
- [ ] Configurer un CDN pour les assets statiques

---

## 🚀 C'est Parti !

Vous êtes prêt à utiliser CO-CESI ! 

**Questions ?** Consultez la documentation complète ou contactez l'équipe.

**Bon covoiturage ! 🚗💨**

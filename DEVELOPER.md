# Guide du Développeur - CO-CESI

## 🏗️ Architecture

### Backend (Laravel 10)
```
backend/
├── app/
│   ├── Http/
│   │   └── Controllers/Api/    # Contrôleurs REST API
│   ├── Models/                  # Modèles Eloquent
│   └── Services/                # Logique métier
├── database/
│   ├── migrations/              # Schéma base de données
│   └── seeders/                 # Données de test
└── routes/
    └── api.php                  # Routes API
```

### Frontend (Vue 3)
```
frontend/
├── src/
│   ├── components/              # Composants réutilisables
│   ├── views/                   # Pages/Vues
│   ├── stores/                  # Pinia stores (état global)
│   ├── router/                  # Configuration routes
│   ├── services/                # Services API
│   └── assets/                  # Styles, images
└── public/                      # Fichiers statiques
```

## 🔧 Stack Technique

### Backend
- **Framework**: Laravel 10
- **Base de données**: MySQL 8.0
- **Authentification**: JWT (tymon/jwt-auth)
- **API**: RESTful

### Frontend
- **Framework**: Vue 3 (Composition API)
- **Build tool**: Vite
- **State Management**: Pinia
- **Routing**: Vue Router 4
- **HTTP Client**: Axios
- **Styling**: Tailwind CSS
- **Maps**: Leaflet.js + OpenStreetMap

## 📝 Conventions de Code

### Backend (Laravel)

#### Nommage
- Controllers: `PascalCase` + `Controller` suffix (ex: `TripController`)
- Models: `PascalCase`, singular (ex: `Trip`, `User`)
- Migrations: `snake_case` (ex: `create_trips_table`)
- Routes: `kebab-case` (ex: `/trips/my-trips`)

#### Structure des Contrôleurs
```php
public function index(Request $request)  // Liste
public function store(Request $request)  // Créer
public function show($id)                // Détail
public function update(Request $request, $id)  // Modifier
public function destroy($id)             // Supprimer
```

#### Réponses API
```php
// Succès
return response()->json([
    'success' => true,
    'data' => $data,
    'message' => 'Message optionnel'
], 200);

// Erreur
return response()->json([
    'success' => false,
    'message' => 'Message d\'erreur',
    'errors' => $errors  // Optionnel
], 400);
```

### Frontend (Vue 3)

#### Nommage
- Components: `PascalCase` (ex: `TripCard.vue`)
- Composables: `use` prefix + `camelCase` (ex: `useAuth`)
- Props: `camelCase`
- Events: `kebab-case`

#### Structure des Composants
```vue
<template>
  <!-- HTML -->
</template>

<script setup>
// Imports
import { ref, computed, onMounted } from 'vue'

// Props
const props = defineProps({
  // ...
})

// Emits
const emit = defineEmits(['event-name'])

// Reactive state
const data = ref(null)

// Computed
const computed Value = computed(() => {
  // ...
})

// Methods
const method = () => {
  // ...
}

// Lifecycle
onMounted(() => {
  // ...
})
</script>

<style scoped>
/* CSS local au composant */
</style>
```

## 🔐 Authentification

### JWT Flow
1. Login: `POST /api/auth/login` → retourne token
2. Stockage: Token dans `localStorage`
3. Requêtes: Header `Authorization: Bearer {token}`
4. Refresh: `POST /api/auth/refresh` si token expiré

### Protéger les Routes

Backend:
```php
Route::middleware('auth:api')->group(function () {
    // Routes protégées
});
```

Frontend:
```javascript
{
  path: '/dashboard',
  meta: { requiresAuth: true }
}
```

## 📡 API Endpoints

### Authentification
- `POST /api/auth/register` - Inscription
- `POST /api/auth/login` - Connexion
- `POST /api/auth/logout` - Déconnexion
- `GET /api/auth/me` - Utilisateur connecté

### Trajets
- `GET /api/trips` - Liste trajets (avec filtres)
- `POST /api/trips` - Créer trajet
- `GET /api/trips/{id}` - Détail trajet
- `PUT /api/trips/{id}` - Modifier trajet
- `DELETE /api/trips/{id}` - Annuler trajet

### Réservations
- `POST /api/bookings` - Créer réservation
- `GET /api/bookings/my-bookings` - Mes réservations
- `POST /api/bookings/{id}/confirm` - Confirmer réservation
- `POST /api/bookings/{id}/refuse` - Refuser réservation
- `POST /api/bookings/{id}/cancel` - Annuler réservation

### Messages
- `GET /api/messages/booking/{id}` - Messages d'une réservation
- `POST /api/messages/booking/{id}` - Envoyer message

### Notations
- `POST /api/ratings/booking/{id}` - Noter après trajet
- `GET /api/ratings/pending` - Notations en attente

## 🧪 Tests

### Backend
```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter=TripTest

# Avec couverture
php artisan test --coverage
```

### Frontend
```bash
# Tests unitaires
npm run test:unit

# Tests E2E
npm run test:e2e
```

## 🐛 Debugging

### Backend
- Logs: `storage/logs/laravel.log`
- Debug bar: Installer `barryvdh/laravel-debugbar`
- Tinker: `php artisan tinker`

### Frontend
- Vue DevTools (extension Chrome/Firefox)
- Console: `console.log()`
- Network tab: Inspecter requêtes API

## 🚀 Workflow Git

### Branches
- `main`: Production
- `develop`: Développement
- `feature/nom-feature`: Nouvelles fonctionnalités
- `fix/nom-bug`: Corrections de bugs

### Commits
Format: `type(scope): message`

Types:
- `feat`: Nouvelle fonctionnalité
- `fix`: Correction de bug
- `docs`: Documentation
- `style`: Formatage
- `refactor`: Refactoring
- `test`: Tests
- `chore`: Tâches diverses

Exemple: `feat(trips): add recurring trips feature`

## 📦 Déploiement

### Backend
```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

### Frontend
```bash
npm run build
# Déployer le contenu de /dist
```

## 🔒 Sécurité

### Checklist
- [ ] Variables sensibles dans `.env`
- [ ] Validation des entrées utilisateur
- [ ] Protection CSRF (Laravel)
- [ ] Sanitization XSS
- [ ] Rate limiting sur API
- [ ] HTTPS en production
- [ ] Tokens JWT sécurisés

## 📚 Ressources

- [Laravel Documentation](https://laravel.com/docs)
- [Vue 3 Documentation](https://vuejs.org)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Pinia](https://pinia.vuejs.org)
- [Leaflet.js](https://leafletjs.com)

## 💡 Bonnes Pratiques

1. **DRY** (Don't Repeat Yourself): Factoriser le code répétitif
2. **Composants réutilisables**: Créer des composants génériques
3. **Gestion d'erreurs**: Toujours gérer les erreurs API
4. **Loading states**: Afficher des indicateurs de chargement
5. **Responsive**: Tester sur mobile et desktop
6. **Accessibilité**: Labels, ARIA, navigation clavier
7. **Performance**: Lazy loading, pagination
8. **SEO**: Meta tags, titres descriptifs

## 🤝 Contribuer

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit vos changements (`git commit -m 'feat: Add amazing feature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📞 Contact

Pour toute question, contactez l'équipe de développement.

Happy coding! 🚀

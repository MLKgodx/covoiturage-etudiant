<template>
  <div class="min-h-screen">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-800 text-white py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-5xl font-bold mb-6">CO-CESI</h1>
        <p class="text-2xl mb-8">Plateforme de Covoiturage Étudiant</p>
        <p class="text-xl mb-12 max-w-2xl mx-auto">
          Partagez vos trajets, économisez de l'argent et réduisez votre empreinte carbone avec d'autres étudiants CESI
        </p>
        <div class="flex justify-center space-x-4">
          <router-link to="/register" class="btn bg-white text-primary-600 hover:bg-gray-100 px-8 py-3 text-lg">
            S'inscrire
          </router-link>
          <router-link to="/login" class="btn border-2 border-white text-white hover:bg-white hover:text-primary-600 px-8 py-3 text-lg">
            Se connecter
          </router-link>
        </div>
      </div>
    </div>

    <!-- Features Section -->
    <div class="py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold text-center mb-12">Pourquoi CO-CESI ?</h2>
        
        <div class="grid md:grid-cols-3 gap-8">
          <!-- Feature 1 -->
          <div class="text-center">
            <div class="text-5xl mb-4">💰</div>
            <h3 class="text-xl font-semibold mb-2">Économies</h3>
            <p class="text-gray-600">
              Partagez les frais de carburant et réduisez vos dépenses de transport quotidiennes
            </p>
          </div>

          <!-- Feature 2 -->
          <div class="text-center">
            <div class="text-5xl mb-4">🌱</div>
            <h3 class="text-xl font-semibold mb-2">Écologique</h3>
            <p class="text-gray-600">
              Réduisez votre empreinte CO2 et contribuez à un campus plus vert
            </p>
          </div>

          <!-- Feature 3 -->
          <div class="text-center">
            <div class="text-5xl mb-4">👥</div>
            <h3 class="text-xl font-semibold mb-2">Convivial</h3>
            <p class="text-gray-600">
              Rencontrez d'autres étudiants et élargissez votre réseau
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Stats Section -->
    <div class="bg-gray-100 py-16">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-8 text-center">
          <div>
            <div class="text-4xl font-bold text-primary-600 mb-2">100+</div>
            <div class="text-gray-600">Étudiants inscrits</div>
          </div>
          <div>
            <div class="text-4xl font-bold text-primary-600 mb-2">30/sem</div>
            <div class="text-gray-600">Trajets partagés</div>
          </div>
          <div>
            <div class="text-4xl font-bold text-primary-600 mb-2">4.5/5</div>
            <div class="text-gray-600">Satisfaction</div>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="py-20">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl font-bold mb-6">Prêt à démarrer ?</h2>
        <p class="text-xl text-gray-600 mb-8">
          Rejoignez la communauté CO-CESI et commencez à partager vos trajets dès aujourd'hui
        </p>
        <router-link to="/register" class="btn btn-primary px-8 py-3 text-lg">
          Créer mon compte
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
// Page d'accueil simple avec call-to-action
</script>

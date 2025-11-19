<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Tableau de bord</h1>
        <p class="text-gray-600">Bienvenue, {{ authStore.fullName }}</p>
      </div>
      <router-link 
        v-if="authStore.canCreateTrip" 
        to="/trips/create" 
        class="btn btn-primary"
      >
        + Créer un trajet
      </router-link>
    </div>

    <!-- Statistics Cards -->
    <div class="grid md:grid-cols-4 gap-6">
      <div class="card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm">Trajets effectués</p>
            <p class="text-3xl font-bold text-primary-600">{{ dashboard?.stats?.total_trips || 0 }}</p>
          </div>
          <div class="text-4xl">🚗</div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm">CO2 économisé</p>
            <p class="text-3xl font-bold text-green-600">{{ dashboard?.stats?.total_co2_saved || 0 }} kg</p>
          </div>
          <div class="text-4xl">🌱</div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm">Note moyenne</p>
            <p class="text-3xl font-bold text-yellow-600">{{ dashboard?.stats?.average_rating || 0 }}/5</p>
          </div>
          <div class="text-4xl">⭐</div>
        </div>
      </div>

      <div class="card">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-gray-600 text-sm">Arbres équivalents</p>
            <p class="text-3xl font-bold text-green-700">{{ dashboard?.stats?.trees_equivalent || 0 }}</p>
          </div>
          <div class="text-4xl">🌳</div>
        </div>
      </div>
    </div>

    <!-- Trusted Driver Badge -->
    <div v-if="dashboard?.stats?.is_trusted_driver" class="card bg-gradient-to-r from-yellow-50 to-yellow-100 border-2 border-yellow-400">
      <div class="flex items-center space-x-3">
        <span class="text-4xl">🏆</span>
        <div>
          <h3 class="font-bold text-yellow-900">Conducteur de confiance</h3>
          <p class="text-sm text-yellow-800">Félicitations ! Vous avez débloqué le badge de conducteur de confiance</p>
        </div>
      </div>
    </div>

    <!-- Notifications -->
    <div v-if="dashboard?.unread_messages_count > 0 || dashboard?.pending_bookings_count > 0" class="card bg-blue-50 border-l-4 border-blue-500">
      <div class="space-y-2">
        <div v-if="dashboard.unread_messages_count > 0" class="flex items-center text-blue-800">
          <span class="mr-2">💬</span>
          <span>Vous avez {{ dashboard.unread_messages_count }} message(s) non lu(s)</span>
        </div>
        <div v-if="dashboard.pending_bookings_count > 0" class="flex items-center text-blue-800">
          <span class="mr-2">⏳</span>
          <router-link to="/bookings/pending" class="underline hover:text-blue-600">
            {{ dashboard.pending_bookings_count }} réservation(s) en attente de validation
          </router-link>
        </div>
      </div>
    </div>

    <!-- Upcoming Trips (as Driver) -->
    <div v-if="dashboard?.upcoming_trips?.length > 0" class="card">
      <h2 class="text-xl font-bold mb-4">Mes prochains trajets (Conducteur)</h2>
      <div class="space-y-4">
        <TripCard 
          v-for="trip in dashboard.upcoming_trips" 
          :key="trip.id" 
          :trip="trip"
          @click="$router.push(`/trips/${trip.id}`)"
        />
      </div>
    </div>

    <!-- Upcoming Bookings (as Passenger) -->
    <div v-if="dashboard?.upcoming_bookings?.length > 0" class="card">
      <h2 class="text-xl font-bold mb-4">Mes prochaines réservations (Passager)</h2>
      <div class="space-y-4">
        <BookingCard 
          v-for="booking in dashboard.upcoming_bookings" 
          :key="booking.id" 
          :booking="booking"
        />
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="!loading && !dashboard?.upcoming_trips?.length && !dashboard?.upcoming_bookings?.length" class="card text-center py-12">
      <div class="text-6xl mb-4">🚗</div>
      <h3 class="text-xl font-semibold mb-2">Aucun trajet prévu</h3>
      <p class="text-gray-600 mb-6">Commencez par rechercher un trajet ou en créer un</p>
      <div class="flex justify-center space-x-4">
        <router-link to="/trips" class="btn btn-primary">
          Rechercher un trajet
        </router-link>
        <router-link v-if="authStore.canCreateTrip" to="/trips/create" class="btn btn-outline">
          Créer un trajet
        </router-link>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="card text-center py-12">
      <div class="text-4xl mb-4">⏳</div>
      <p class="text-gray-600">Chargement...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import api from '../services/api'
import TripCard from '../components/trips/TripCard.vue'
import BookingCard from '../components/bookings/BookingCard.vue'

const authStore = useAuthStore()
const dashboard = ref(null)
const loading = ref(true)

const fetchDashboard = async () => {
  try {
    const response = await api.user.dashboard()
    dashboard.value = response.data.data
  } catch (error) {
    console.error('Error fetching dashboard:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboard()
})
</script>

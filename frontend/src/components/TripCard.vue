<template>
  <div class="border rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer" @click="$emit('click')">
    <div class="flex justify-between items-start mb-3">
      <div class="flex items-center space-x-3">
        <!-- Driver Photo -->
        <div class="w-12 h-12 bg-primary-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
          {{ driverInitials }}
        </div>
        <div>
          <h3 class="font-semibold">{{ trip.driver.first_name }} {{ trip.driver.last_name }}</h3>
          <div class="flex items-center space-x-2 text-sm text-gray-600">
            <span>⭐ {{ trip.driver.average_rating.toFixed(1) }}</span>
            <span v-if="trip.driver.total_trips >= 10 && trip.driver.average_rating >= 4.5" class="badge badge-yellow">
              🏆 Conducteur de confiance
            </span>
          </div>
        </div>
      </div>

      <!-- Status Badge -->
      <span class="badge" :class="statusClass">
        {{ statusText }}
      </span>
    </div>

    <!-- Trip Details -->
    <div class="space-y-2 mb-3">
      <div class="flex items-start space-x-2">
        <span class="text-green-600 mt-1">📍</span>
        <div>
          <p class="font-medium">{{ trip.departure_address }}</p>
          <p class="text-sm text-gray-600">{{ formatDateTime(trip.departure_time) }}</p>
        </div>
      </div>

      <div class="flex items-start space-x-2">
        <span class="text-red-600 mt-1">📍</span>
        <div>
          <p class="font-medium">{{ trip.arrival_address }}</p>
          <p v-if="trip.arrival_time" class="text-sm text-gray-600">{{ formatDateTime(trip.arrival_time) }}</p>
        </div>
      </div>
    </div>

    <!-- Trip Info -->
    <div class="flex flex-wrap gap-2 mb-3">
      <span class="badge badge-blue">
        {{ trip.available_seats - trip.occupied_seats }} place(s) disponible(s)
      </span>
      <span class="badge badge-green">
        {{ trip.distance_km }} km
      </span>
      <span v-if="trip.estimated_co2_saved > 0" class="badge badge-green">
        🌱 {{ trip.estimated_co2_saved }} kg CO2
      </span>
    </div>

    <!-- Preferences Tags -->
    <div class="flex flex-wrap gap-2">
      <span v-if="trip.smoker_allowed" class="text-xs bg-gray-100 px-2 py-1 rounded">🚬 Fumeur OK</span>
      <span v-if="trip.music_allowed" class="text-xs bg-gray-100 px-2 py-1 rounded">🎵 Musique OK</span>
      <span v-if="trip.chattiness_preference" class="text-xs bg-gray-100 px-2 py-1 rounded">
        {{ chattinessLabel(trip.chattiness_preference) }}
      </span>
      <span v-if="trip.is_recurring" class="text-xs bg-gray-100 px-2 py-1 rounded">🔄 Récurrent</span>
      <span v-if="trip.is_round_trip" class="text-xs bg-gray-100 px-2 py-1 rounded">↔️ Aller-retour</span>
    </div>

    <!-- Confirmed Passengers (if driver view) -->
    <div v-if="trip.confirmed_bookings && trip.confirmed_bookings.length > 0" class="mt-3 pt-3 border-t">
      <p class="text-sm text-gray-600 mb-2">Passagers confirmés:</p>
      <div class="flex -space-x-2">
        <div 
          v-for="booking in trip.confirmed_bookings" 
          :key="booking.id"
          class="w-8 h-8 bg-primary-400 rounded-full flex items-center justify-center text-white text-xs font-bold border-2 border-white"
          :title="`${booking.passenger.first_name} ${booking.passenger.last_name}`"
        >
          {{ booking.passenger.first_name[0] }}{{ booking.passenger.last_name[0] }}
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { format, parseISO } from 'date-fns'
import { fr } from 'date-fns/locale'

const props = defineProps({
  trip: {
    type: Object,
    required: true
  }
})

defineEmits(['click'])

const driverInitials = computed(() => {
  return `${props.trip.driver.first_name[0]}${props.trip.driver.last_name[0]}`.toUpperCase()
})

const statusClass = computed(() => {
  switch (props.trip.status) {
    case 'active': return 'badge-green'
    case 'full': return 'badge-yellow'
    case 'completed': return 'badge-blue'
    case 'cancelled': return 'badge-red'
    default: return 'badge-blue'
  }
})

const statusText = computed(() => {
  switch (props.trip.status) {
    case 'active': return 'Actif'
    case 'full': return 'Complet'
    case 'completed': return 'Terminé'
    case 'cancelled': return 'Annulé'
    default: return props.trip.status
  }
})

const formatDateTime = (dateString) => {
  return format(parseISO(dateString), 'PPp', { locale: fr })
}

const chattinessLabel = (value) => {
  const labels = {
    quiet: '🤫 Silencieux',
    normal: '💬 Normal',
    chatty: '😄 Bavard'
  }
  return labels[value] || value
}
</script>

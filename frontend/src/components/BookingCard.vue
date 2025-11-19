<template>
  <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
    <div class="flex justify-between items-start mb-3">
      <div class="flex items-center space-x-3">
        <!-- Driver/Passenger Photo -->
        <div class="w-10 h-10 bg-primary-600 rounded-full flex items-center justify-center text-white font-bold">
          {{ userInitials }}
        </div>
        <div>
          <h4 class="font-semibold">{{ userName }}</h4>
          <p class="text-sm text-gray-600">{{ userRole }}</p>
        </div>
      </div>

      <!-- Status Badge -->
      <span class="badge" :class="statusClass">
        {{ statusText }}
      </span>
    </div>

    <!-- Trip Info -->
    <div class="space-y-2 mb-3">
      <div class="flex items-center space-x-2 text-sm">
        <span>📍</span>
        <span class="font-medium">{{ booking.trip.departure_address }}</span>
        <span class="text-gray-400">→</span>
        <span class="font-medium">{{ booking.trip.arrival_address }}</span>
      </div>
      
      <div class="flex items-center space-x-2 text-sm text-gray-600">
        <span>🕐</span>
        <span>{{ formatDateTime(booking.trip.departure_time) }}</span>
      </div>

      <div class="flex items-center space-x-2 text-sm">
        <span>👥</span>
        <span>{{ booking.seats_booked }} place(s) réservée(s)</span>
      </div>

      <div v-if="booking.co2_saved > 0" class="flex items-center space-x-2 text-sm text-green-600">
        <span>🌱</span>
        <span>{{ booking.co2_saved }} kg CO2 économisés</span>
      </div>
    </div>

    <!-- Message -->
    <div v-if="booking.message" class="bg-gray-50 rounded p-2 mb-3 text-sm">
      <p class="text-gray-700">💬 {{ booking.message }}</p>
    </div>

    <!-- Actions -->
    <div class="flex flex-wrap gap-2">
      <button 
        v-if="canViewMessages"
        @click="$emit('view-messages', booking.id)"
        class="btn btn-outline text-sm py-1"
      >
        💬 Messages
      </button>

      <button 
        v-if="canConfirm"
        @click="$emit('confirm', booking.id)"
        class="btn bg-green-600 text-white hover:bg-green-700 text-sm py-1"
      >
        ✓ Accepter
      </button>

      <button 
        v-if="canRefuse"
        @click="$emit('refuse', booking.id)"
        class="btn bg-red-600 text-white hover:bg-red-700 text-sm py-1"
      >
        ✗ Refuser
      </button>

      <button 
        v-if="canCancel"
        @click="$emit('cancel', booking.id)"
        class="btn bg-red-100 text-red-700 hover:bg-red-200 text-sm py-1"
      >
        Annuler
      </button>

      <button 
        v-if="canRate"
        @click="$emit('rate', booking.id)"
        class="btn bg-yellow-100 text-yellow-700 hover:bg-yellow-200 text-sm py-1"
      >
        ⭐ Noter
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { format, parseISO, isFuture, isPast } from 'date-fns'
import { fr } from 'date-fns/locale'
import { useAuthStore } from '../../stores/auth'

const props = defineProps({
  booking: {
    type: Object,
    required: true
  }
})

defineEmits(['view-messages', 'confirm', 'refuse', 'cancel', 'rate'])

const authStore = useAuthStore()

const isDriver = computed(() => {
  return props.booking.trip.driver_id === authStore.user?.id
})

const userInitials = computed(() => {
  const user = isDriver.value ? props.booking.passenger : props.booking.trip.driver
  return `${user.first_name[0]}${user.last_name[0]}`.toUpperCase()
})

const userName = computed(() => {
  const user = isDriver.value ? props.booking.passenger : props.booking.trip.driver
  return `${user.first_name} ${user.last_name}`
})

const userRole = computed(() => {
  return isDriver.value ? 'Passager' : 'Conducteur'
})

const statusClass = computed(() => {
  switch (props.booking.status) {
    case 'pending': return 'badge-yellow'
    case 'confirmed': return 'badge-green'
    case 'refused': return 'badge-red'
    case 'cancelled_by_passenger':
    case 'cancelled_by_driver': return 'badge-red'
    default: return 'badge-blue'
  }
})

const statusText = computed(() => {
  switch (props.booking.status) {
    case 'pending': return 'En attente'
    case 'confirmed': return 'Confirmé'
    case 'refused': return 'Refusé'
    case 'cancelled_by_passenger': return 'Annulé (passager)'
    case 'cancelled_by_driver': return 'Annulé (conducteur)'
    default: return props.booking.status
  }
})

const canViewMessages = computed(() => {
  return props.booking.status === 'confirmed'
})

const canConfirm = computed(() => {
  return isDriver.value && props.booking.status === 'pending'
})

const canRefuse = computed(() => {
  return isDriver.value && props.booking.status === 'pending'
})

const canCancel = computed(() => {
  return ['pending', 'confirmed'].includes(props.booking.status) && 
         isFuture(parseISO(props.booking.trip.departure_time))
})

const canRate = computed(() => {
  return props.booking.status === 'confirmed' && 
         isPast(parseISO(props.booking.trip.departure_time)) &&
         (isDriver.value ? !props.booking.passenger_rated : !props.booking.driver_rated)
})

const formatDateTime = (dateString) => {
  return format(parseISO(dateString), 'PPp', { locale: fr })
}
</script>

<template>
  <nav class="bg-white shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <div class="flex">
          <!-- Logo -->
          <router-link to="/dashboard" class="flex items-center">
            <span class="text-2xl font-bold text-primary-600">🚗 CO-CESI</span>
          </router-link>

          <!-- Navigation Links -->
          <div class="hidden md:ml-8 md:flex md:space-x-4">
            <router-link 
              to="/dashboard" 
              class="nav-link"
              active-class="nav-link-active"
            >
              Tableau de bord
            </router-link>
            
            <router-link 
              to="/trips" 
              class="nav-link"
              active-class="nav-link-active"
            >
              Rechercher
            </router-link>

            <router-link 
              v-if="authStore.canCreateTrip"
              to="/trips/create" 
              class="nav-link"
              active-class="nav-link-active"
            >
              Créer un trajet
            </router-link>

            <router-link 
              to="/trips/my-trips" 
              class="nav-link"
              active-class="nav-link-active"
            >
              Mes trajets
            </router-link>

            <router-link 
              to="/bookings" 
              class="nav-link"
              active-class="nav-link-active"
            >
              Réservations
              <span v-if="unreadCount > 0" class="ml-1 badge badge-red">
                {{ unreadCount }}
              </span>
            </router-link>
          </div>
        </div>

        <!-- User Menu -->
        <div class="flex items-center space-x-4">
          <!-- Notifications -->
          <button class="relative p-2 text-gray-600 hover:text-gray-900">
            <span class="text-xl">🔔</span>
            <span v-if="unreadMessagesCount > 0" 
                  class="absolute top-0 right-0 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
              {{ unreadMessagesCount }}
            </span>
          </button>

          <!-- User Dropdown -->
          <div class="relative" ref="dropdown">
            <button 
              @click="toggleDropdown" 
              class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100"
            >
              <div class="w-8 h-8 bg-primary-600 rounded-full flex items-center justify-center text-white font-bold">
                {{ userInitials }}
              </div>
              <span class="hidden md:block text-sm font-medium">{{ authStore.fullName }}</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            <!-- Dropdown Menu -->
            <div 
              v-show="isDropdownOpen" 
              class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50"
            >
              <router-link 
                to="/profile" 
                class="dropdown-item"
                @click="closeDropdown"
              >
                Mon profil
              </router-link>
              <router-link 
                to="/ratings/pending" 
                class="dropdown-item"
                @click="closeDropdown"
              >
                Notations en attente
              </router-link>
              <hr class="my-2">
              <button 
                @click="handleLogout" 
                class="dropdown-item w-full text-left text-red-600"
              >
                Déconnexion
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useAuthStore } from '../../stores/auth'
import api from '../../services/api'

const authStore = useAuthStore()
const isDropdownOpen = ref(false)
const dropdown = ref(null)
const unreadCount = ref(0)
const unreadMessagesCount = ref(0)

const userInitials = computed(() => {
  if (!authStore.user) return '?'
  return `${authStore.user.first_name[0]}${authStore.user.last_name[0]}`.toUpperCase()
})

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value
}

const closeDropdown = () => {
  isDropdownOpen.value = false
}

const handleLogout = () => {
  authStore.logout()
  closeDropdown()
}

const fetchUnreadCounts = async () => {
  try {
    const response = await api.messages.unreadCount()
    unreadMessagesCount.value = response.data.data.count
  } catch (error) {
    console.error('Error fetching unread counts:', error)
  }
}

// Fermer le dropdown en cliquant à l'extérieur
const handleClickOutside = (event) => {
  if (dropdown.value && !dropdown.value.contains(event.target)) {
    closeDropdown()
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  fetchUnreadCounts()
  // Actualiser toutes les 30 secondes
  setInterval(fetchUnreadCounts, 30000)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>

<style scoped>
.nav-link {
  @apply inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-50 rounded-md transition-colors;
}

.nav-link-active {
  @apply text-primary-600 bg-primary-50;
}

.dropdown-item {
  @apply block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors;
}
</style>

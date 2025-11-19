import { defineStore } from 'pinia'
import api from '../services/api'
import router from '../router'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('user')) || null,
    token: localStorage.getItem('token') || null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isDriver: (state) => state.user?.profile_type === 'driver' || state.user?.profile_type === 'both',
    isPassenger: (state) => state.user?.profile_type === 'passenger' || state.user?.profile_type === 'both',
    canCreateTrip: (state) => {
      if (!state.user) return false
      const isDriverType = state.user.profile_type === 'driver' || state.user.profile_type === 'both'
      const hasVehicle = state.user.vehicle_brand && state.user.vehicle_model && state.user.vehicle_seats
      return isDriverType && hasVehicle
    },
    fullName: (state) => state.user ? `${state.user.first_name} ${state.user.last_name}` : '',
  },

  actions: {
    async register(userData) {
      this.loading = true
      this.error = null
      try {
        const response = await api.auth.register(userData)
        this.user = response.data.data.user
        this.token = response.data.data.token
        localStorage.setItem('user', JSON.stringify(this.user))
        localStorage.setItem('token', this.token)
        router.push('/dashboard')
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de l\'inscription'
        throw error
      } finally {
        this.loading = false
      }
    },

    async login(credentials) {
      this.loading = true
      this.error = null
      try {
        const response = await api.auth.login(credentials)
        this.user = response.data.data.user
        this.token = response.data.data.token
        localStorage.setItem('user', JSON.stringify(this.user))
        localStorage.setItem('token', this.token)
        router.push('/dashboard')
        return response.data
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la connexion'
        throw error
      } finally {
        this.loading = false
      }
    },

    async logout() {
      try {
        await api.auth.logout()
      } catch (error) {
        console.error('Logout error:', error)
      } finally {
        this.user = null
        this.token = null
        localStorage.removeItem('user')
        localStorage.removeItem('token')
        router.push('/login')
      }
    },

    async fetchUser() {
      try {
        const response = await api.auth.me()
        this.user = response.data.data
        localStorage.setItem('user', JSON.stringify(this.user))
        return response.data
      } catch (error) {
        console.error('Fetch user error:', error)
        throw error
      }
    },

    async updateUser(userData) {
      try {
        const response = await api.user.update(userData)
        this.user = response.data.data
        localStorage.setItem('user', JSON.stringify(this.user))
        return response.data
      } catch (error) {
        throw error
      }
    },

    clearError() {
      this.error = null
    },
  },
})

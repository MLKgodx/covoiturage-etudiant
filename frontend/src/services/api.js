import axios from 'axios'
import router from '../router'

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json'
  }
})

// Intercepteur pour ajouter le token JWT
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Intercepteur pour gérer les erreurs
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      router.push('/login')
    }
    return Promise.reject(error)
  }
)

export default {
  // Auth
  auth: {
    register: (data) => api.post('/auth/register', data),
    login: (data) => api.post('/auth/login', data),
    logout: () => api.post('/auth/logout'),
    me: () => api.get('/auth/me'),
    refresh: () => api.post('/auth/refresh'),
    forgotPassword: (data) => api.post('/auth/forgot-password', data),
    resetPassword: (data) => api.post('/auth/reset-password', data),
  },

  // User
  user: {
    dashboard: () => api.get('/user/dashboard'),
    show: (id) => api.get(`/user/${id}`),
    update: (data) => api.put('/user/profile', data),
    updatePhoto: (formData) => api.post('/user/photo', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    }),
    updatePassword: (data) => api.put('/user/password', data),
  },

  // Trips
  trips: {
    list: (params) => api.get('/trips', { params }),
    create: (data) => api.post('/trips', data),
    show: (id) => api.get(`/trips/${id}`),
    update: (id, data) => api.put(`/trips/${id}`, data),
    delete: (id) => api.delete(`/trips/${id}`),
    myTrips: () => api.get('/trips/my-trips'),
  },

  // Bookings
  bookings: {
    create: (data) => api.post('/bookings', data),
    myBookings: () => api.get('/bookings/my-bookings'),
    pending: () => api.get('/bookings/pending'),
    confirm: (id) => api.post(`/bookings/${id}/confirm`),
    refuse: (id) => api.post(`/bookings/${id}/refuse`),
    cancel: (id) => api.post(`/bookings/${id}/cancel`),
  },

  // Messages
  messages: {
    list: (bookingId) => api.get(`/messages/booking/${bookingId}`),
    send: (bookingId, data) => api.post(`/messages/booking/${bookingId}`, data),
    templates: () => api.get('/messages/templates'),
    unreadCount: () => api.get('/messages/unread-count'),
  },

  // Ratings
  ratings: {
    create: (bookingId, data) => api.post(`/ratings/booking/${bookingId}`, data),
    pending: () => api.get('/ratings/pending'),
    userRatings: (userId) => api.get(`/ratings/user/${userId}`),
  },

  // Health check
  health: () => api.get('/health'),
}

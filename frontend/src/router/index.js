import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('../views/HomePage.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginPage.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/RegisterPage.vue'),
    meta: { requiresAuth: false }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('../views/DashboardPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import('../views/ProfilePage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/trips',
    children: [
      {
        path: '',
        name: 'trips.search',
        component: () => import('../views/trips/SearchTrips.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'create',
        name: 'trips.create',
        component: () => import('../views/trips/CreateTrip.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'my-trips',
        name: 'trips.myTrips',
        component: () => import('../views/trips/MyTrips.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: ':id',
        name: 'trips.detail',
        component: () => import('../views/trips/TripDetail.vue'),
        meta: { requiresAuth: true }
      },
    ]
  },
  {
    path: '/bookings',
    children: [
      {
        path: '',
        name: 'bookings.list',
        component: () => import('../views/bookings/MyBookings.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'pending',
        name: 'bookings.pending',
        component: () => import('../views/bookings/PendingBookings.vue'),
        meta: { requiresAuth: true }
      },
    ]
  },
  {
    path: '/messages/:bookingId',
    name: 'messages',
    component: () => import('../views/MessagesPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/ratings',
    children: [
      {
        path: 'pending',
        name: 'ratings.pending',
        component: () => import('../views/ratings/PendingRatings.vue'),
        meta: { requiresAuth: true }
      },
      {
        path: 'user/:userId',
        name: 'ratings.user',
        component: () => import('../views/ratings/UserRatings.vue'),
        meta: { requiresAuth: true }
      },
    ]
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Guard pour vérifier l'authentification
router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)

  if (requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } else if ((to.name === 'login' || to.name === 'register') && authStore.isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router

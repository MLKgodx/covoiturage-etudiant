<template>
  <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h2 class="text-4xl font-bold text-gray-900">Créer un compte</h2>
        <p class="mt-2 text-sm text-gray-600">Rejoignez la communauté CO-CESI</p>
      </div>

      <!-- Error Message -->
      <div v-if="authStore.error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
        {{ authStore.error }}
      </div>

      <!-- Form -->
      <form @submit.prevent="handleRegister" class="card space-y-6">
        <!-- Étape 1: Informations personnelles -->
        <div v-show="step === 1">
          <h3 class="text-xl font-semibold mb-4">Informations personnelles</h3>
          
          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
              <input v-model="form.first_name" type="text" required class="input" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
              <input v-model="form.last_name" type="text" required class="input" />
            </div>
          </div>

          <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Email étudiant</label>
            <input 
              v-model="form.email" 
              type="email" 
              required 
              class="input" 
              placeholder="prenom.nom@etudiant.cesi.fr"
            />
            <p class="mt-1 text-xs text-gray-500">Vous devez utiliser votre adresse email CESI</p>
          </div>

          <div class="grid md:grid-cols-2 gap-4 mt-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
              <input v-model="form.password" type="password" required minlength="8" class="input" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer</label>
              <input v-model="form.password_confirmation" type="password" required class="input" />
            </div>
          </div>

          <div class="grid md:grid-cols-2 gap-4 mt-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Filière</label>
              <select v-model="form.field_of_study" required class="input">
                <option value="">Sélectionnez...</option>
                <option value="Informatique">Informatique</option>
                <option value="BTP">BTP</option>
                <option value="Généraliste">Généraliste</option>
                <option value="Marketing">Marketing</option>
                <option value="RH">RH</option>
                <option value="Autre">Autre</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Année</label>
              <select v-model="form.year" required class="input">
                <option value="">Sélectionnez...</option>
                <option :value="1">1ère année</option>
                <option :value="2">2ème année</option>
                <option :value="3">3ème année</option>
                <option :value="4">4ème année</option>
                <option :value="5">5ème année</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Étape 2: Type de profil et préférences -->
        <div v-show="step === 2">
          <h3 class="text-xl font-semibold mb-4">Profil et préférences</h3>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Type de profil</label>
            <div class="space-y-2">
              <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                <input v-model="form.profile_type" type="radio" value="passenger" class="mr-3" />
                <div>
                  <div class="font-medium">Passager uniquement</div>
                  <div class="text-sm text-gray-500">Je cherche des trajets</div>
                </div>
              </label>
              <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                <input v-model="form.profile_type" type="radio" value="driver" class="mr-3" />
                <div>
                  <div class="font-medium">Conducteur uniquement</div>
                  <div class="text-sm text-gray-500">Je propose des trajets</div>
                </div>
              </label>
              <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                <input v-model="form.profile_type" type="radio" value="both" class="mr-3" />
                <div>
                  <div class="font-medium">Les deux</div>
                  <div class="text-sm text-gray-500">Je conduis et je cherche des trajets</div>
                </div>
              </label>
            </div>
          </div>

          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Préférences</label>
            <div class="space-y-3">
              <label class="flex items-center">
                <input v-model="form.smoker" type="checkbox" class="mr-2" />
                <span>Fumeur</span>
              </label>
              <label class="flex items-center">
                <input v-model="form.music" type="checkbox" class="mr-2" />
                <span>Musique autorisée</span>
              </label>
              <div>
                <label class="block text-sm mb-2">Bavardage</label>
                <select v-model="form.chattiness" class="input">
                  <option value="quiet">Silencieux</option>
                  <option value="normal">Normal</option>
                  <option value="chatty">Bavard</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Étape 3: Informations véhicule (si conducteur) -->
        <div v-show="step === 3 && (form.profile_type === 'driver' || form.profile_type === 'both')">
          <h3 class="text-xl font-semibold mb-4">Informations du véhicule</h3>
          <p class="text-sm text-gray-600 mb-4">
            Ces informations sont obligatoires pour créer des trajets en tant que conducteur
          </p>

          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Marque</label>
              <input v-model="form.vehicle_brand" type="text" class="input" placeholder="Peugeot" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Modèle</label>
              <input v-model="form.vehicle_model" type="text" class="input" placeholder="208" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Couleur</label>
              <input v-model="form.vehicle_color" type="text" class="input" placeholder="Bleu" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de places disponibles</label>
              <input v-model.number="form.vehicle_seats" type="number" min="1" max="8" class="input" />
            </div>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between pt-6 border-t">
          <button
            v-if="step > 1"
            type="button"
            @click="step--"
            class="btn btn-secondary"
          >
            Précédent
          </button>
          <div v-else></div>

          <button
            v-if="step < maxStep"
            type="button"
            @click="nextStep"
            class="btn btn-primary"
          >
            Suivant
          </button>
          <button
            v-else
            type="submit"
            :disabled="authStore.loading"
            class="btn btn-primary disabled:opacity-50"
          >
            <span v-if="authStore.loading">Inscription...</span>
            <span v-else>S'inscrire</span>
          </button>
        </div>

        <!-- Progress Indicators -->
        <div class="flex justify-center space-x-2 pt-4">
          <div 
            v-for="i in maxStep" 
            :key="i" 
            class="w-2 h-2 rounded-full"
            :class="i === step ? 'bg-primary-600' : 'bg-gray-300'"
          ></div>
        </div>
      </form>

      <div class="text-center mt-6 text-sm">
        <span class="text-gray-600">Vous avez déjà un compte ?</span>
        <router-link to="/login" class="font-medium text-primary-600 hover:text-primary-500 ml-1">
          Se connecter
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const step = ref(1)

const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  password_confirmation: '',
  field_of_study: '',
  year: '',
  profile_type: 'both',
  smoker: false,
  music: true,
  chattiness: 'normal',
  vehicle_brand: '',
  vehicle_model: '',
  vehicle_color: '',
  vehicle_seats: null,
})

const maxStep = computed(() => {
  return (form.value.profile_type === 'driver' || form.value.profile_type === 'both') ? 3 : 2
})

const nextStep = () => {
  if (step.value < maxStep.value) {
    step.value++
  }
}

const handleRegister = async () => {
  if (form.value.password !== form.value.password_confirmation) {
    authStore.error = 'Les mots de passe ne correspondent pas'
    return
  }

  authStore.clearError()
  try {
    await authStore.register(form.value)
  } catch (error) {
    // L'erreur est gérée dans le store
  }
}
</script>

<script setup>
import { ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { login } from '../services/authService.js'

const router = useRouter()
const route = useRoute()

const username = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function handleSubmit() {
  if (!username.value || !password.value) {
    error.value = 'Bitte Benutzername und Passwort eingeben.'
    return
  }

  try {
    loading.value = true
    error.value = ''
    await login(username.value, password.value)
    const redirect = route.query.redirect || '/admin'
    router.push(redirect)
  } catch (err) {
    error.value = err.message || 'Fehler beim Anmelden'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-[var(--color-cream)]">
    <div class="max-w-md w-full">
      <div class="bg-white rounded-xl shadow-lg p-8">
        <h1 class="font-heading text-3xl font-semibold text-[var(--color-forest)] text-center mb-6">
          Anmelden
        </h1>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <div v-if="error" class="bg-red-100 text-red-800 p-4 rounded-lg text-sm">
            {{ error }}
          </div>

          <div>
            <label for="username" class="block text-sm font-medium text-[var(--color-forest)] mb-2">
              Benutzername
            </label>
            <input
              id="username"
              v-model="username"
              type="text"
              required
              autocomplete="username"
              class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
            />
          </div>

          <div>
            <label for="password" class="block text-sm font-medium text-[var(--color-forest)] mb-2">
              Passwort
            </label>
            <input
              id="password"
              v-model="password"
              type="password"
              required
              autocomplete="current-password"
              class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full px-6 py-3 bg-[var(--color-forest)] text-white font-medium rounded-lg hover:bg-[var(--color-terracotta)] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-forest)] disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ loading ? 'Wird angemeldet...' : 'Anmelden' }}
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

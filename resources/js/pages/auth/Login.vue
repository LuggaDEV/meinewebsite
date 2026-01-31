<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3'
import { store } from '@/routes/login'
import { request } from '@/routes/password'

defineProps<{
    status?: string
    canResetPassword: boolean
    canRegister: boolean
}>()
</script>

<template>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-[var(--color-cream)]">
        <Head title="Anmelden" />

        <div class="max-w-md w-full">
            <div class="bg-white rounded-xl shadow-lg p-8">
                <h1 class="font-heading text-3xl font-semibold text-[var(--color-forest)] text-center mb-6">
                    Anmelden
                </h1>

                <div
                    v-if="status"
                    class="mb-4 text-center text-sm font-medium text-green-600"
                >
                    {{ status }}
                </div>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                >
                    <div v-if="errors.email || errors.password" class="bg-red-100 text-red-800 p-4 rounded-lg text-sm">
                        {{ errors.email || errors.password || 'Fehler beim Anmelden' }}
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-[var(--color-forest)] mb-2">
                            E-Mail-Adresse
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            autocomplete="email"
                            class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                        />
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-sm font-medium text-[var(--color-forest)]">
                                Passwort
                            </label>
                            <a
                                v-if="canResetPassword"
                                :href="request().url"
                                class="text-sm text-[var(--color-terracotta)] hover:text-[var(--color-forest)]"
                            >
                                Passwort vergessen?
                            </a>
                        </div>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full px-4 py-2 border border-[var(--color-forest)]/20 rounded-lg focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                        />
                    </div>

                    <div class="flex items-center">
                        <input
                            id="remember"
                            name="remember"
                            type="checkbox"
                            class="h-4 w-4 text-[var(--color-terracotta)] focus:ring-[var(--color-terracotta)] border-[var(--color-forest)]/20 rounded"
                        />
                        <label for="remember" class="ml-2 block text-sm text-[var(--color-forest)]">
                            Angemeldet bleiben
                        </label>
                    </div>

                    <button
                        type="submit"
                        :disabled="processing"
                        class="w-full px-6 py-3 bg-[var(--color-forest)] text-white font-medium rounded-lg hover:bg-[var(--color-terracotta)] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-forest)] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ processing ? 'Wird angemeldet...' : 'Anmelden' }}
                    </button>
                </Form>
            </div>
        </div>
    </div>
</template>

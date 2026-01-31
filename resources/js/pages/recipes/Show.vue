<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import RecipeLayout from '@/layouts/RecipeLayout.vue'
import { computed, ref, onUnmounted, onBeforeUnmount } from 'vue'

const props = defineProps<{
    recipe: {
        id: number
        title: string
        description: string
        image: string | null
        servings: number | null
        prep_time: number | null
        cook_time: number | null
        rest_time: number | null
        ingredients: string[]
        instructions: string[]
    }
}>()

// Formatiere Minuten zu Stunden/Minuten für die Anzeige
function formatTime(minutes: number | null): string {
    if (!minutes || minutes === 0) {
        return ''
    }
    if (minutes >= 60 && minutes % 60 === 0) {
        const hours = minutes / 60
        return `${hours} ${hours === 1 ? 'Std.' : 'Std.'}`
    }
    if (minutes >= 60) {
        const hours = Math.floor(minutes / 60)
        const mins = minutes % 60
        return `${hours} ${hours === 1 ? 'Std.' : 'Std.'} ${mins} Min.`
    }
    return `${minutes} Min.`
}

const prepTimeFormatted = computed(() => formatTime(props.recipe.prep_time))
const cookTimeFormatted = computed(() => formatTime(props.recipe.cook_time))
const restTimeFormatted = computed(() => formatTime(props.recipe.rest_time))

// Kochmodus - Wake Lock API
const cookModeEnabled = ref(false)
let wakeLock: WakeLockSentinel | null = null

async function toggleCookMode(): Promise<void> {
    if (cookModeEnabled.value) {
        // Kochmodus aktivieren
        try {
            if ('wakeLock' in navigator) {
                wakeLock = await (navigator as any).wakeLock.request('screen')
                wakeLock.addEventListener('release', () => {
                    cookModeEnabled.value = false
                })
            } else {
                alert('Kochmodus wird von Ihrem Browser nicht unterstützt.')
                cookModeEnabled.value = false
            }
        } catch (err: any) {
            console.error('Wake Lock Fehler:', err)
            alert('Kochmodus konnte nicht aktiviert werden: ' + (err.message || 'Unbekannter Fehler'))
            cookModeEnabled.value = false
        }
    } else {
        // Kochmodus deaktivieren
        if (wakeLock) {
            await wakeLock.release()
            wakeLock = null
        }
    }
}

// Wake Lock beim Verlassen der Seite freigeben
onBeforeUnmount(async () => {
    if (wakeLock) {
        await wakeLock.release()
        wakeLock = null
    }
})

// Auch beim Unmount freigeben (falls onBeforeUnmount nicht ausgelöst wird)
onUnmounted(async () => {
    if (wakeLock) {
        await wakeLock.release()
        wakeLock = null
    }
})

// Wake Lock wiederherstellen, wenn die Seite wieder sichtbar wird
document.addEventListener('visibilitychange', async () => {
    if (cookModeEnabled.value && wakeLock === null && document.visibilityState === 'visible') {
        try {
            if ('wakeLock' in navigator) {
                wakeLock = await (navigator as any).wakeLock.request('screen')
            }
        } catch (err) {
            console.error('Wake Lock Wiederherstellung fehlgeschlagen:', err)
        }
    }
})
</script>

<template>
    <RecipeLayout>
        <div class="py-12 md:py-16">
            <Motion
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ duration: 0.4 }"
                class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8"
            >
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ duration: 0.4, delay: 0.1 }"
                >
                    <Link
                        href="/"
                        class="mb-8 inline-flex items-center gap-2 text-[var(--color-forest)] hover:text-[var(--color-terracotta)] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2 rounded"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Zurück zu den Rezepten
                    </Link>
                </Motion>

                <article>
                    <!-- Bild -->
                    <Motion
                        v-if="recipe.image"
                        :initial="{ opacity: 0, scale: 0.95 }"
                        :animate="{ opacity: 1, scale: 1 }"
                        :transition="{ duration: 0.5, delay: 0.2 }"
                    >
                        <div class="aspect-[16/9] overflow-hidden rounded-xl mb-8 bg-[var(--color-forest)]/5">
                            <img
                                :src="recipe.image"
                                :alt="recipe.title"
                                class="w-full h-full object-cover"
                            />
                        </div>
                    </Motion>

                    <!-- Kochmodus Toggle - Nur auf mobilen Geräten -->
                    <Motion
                        :initial="{ opacity: 0, y: 10 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.3, delay: 0.25 }"
                        class="mb-6 md:hidden"
                    >
                        <label class="flex items-center gap-3 p-4 bg-[var(--color-cream)] rounded-lg border-2 border-[var(--color-terracotta)]/30 cursor-pointer hover:border-[var(--color-terracotta)]/50 transition-colors">
                            <input
                                v-model="cookModeEnabled"
                                @change="toggleCookMode"
                                type="checkbox"
                                class="w-5 h-5 text-[var(--color-terracotta)] border-[var(--color-forest)]/30 rounded focus:ring-2 focus:ring-[var(--color-terracotta)] focus:ring-offset-2 cursor-pointer"
                            />
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <svg class="w-5 h-5 text-[var(--color-terracotta)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <span class="font-semibold text-[var(--color-forest)]">Kochmodus</span>
                                    <span v-if="cookModeEnabled" class="ml-2 px-2 py-0.5 bg-[var(--color-terracotta)] text-white text-xs font-medium rounded-full">
                                        Aktiv
                                    </span>
                                </div>
                                <p class="text-sm text-[var(--color-warm-gray)] mt-1">
                                    Verhindert, dass der Bildschirm ausgeht
                                </p>
                            </div>
                        </label>
                    </Motion>

                    <!-- Header -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.5, delay: 0.3 }"
                    >
                        <header class="mb-8">
                            <h1 class="font-heading text-3xl md:text-4xl lg:text-5xl font-semibold text-[var(--color-forest)] mb-4">
                                {{ recipe.title }}
                            </h1>
                            <p class="text-lg text-[var(--color-warm-gray)] mb-6">
                                {{ recipe.description }}
                            </p>
                            <div class="flex flex-wrap gap-4 text-sm text-[var(--color-warm-gray)]">
                                <span v-if="recipe.servings" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ recipe.servings }} Portionen
                                </span>
                                <span v-if="prepTimeFormatted" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Vorbereitung: {{ prepTimeFormatted }}
                                </span>
                                <span v-if="cookTimeFormatted" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Koch-/Backzeit: {{ cookTimeFormatted }}
                                </span>
                                <span v-if="restTimeFormatted" class="flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Ruhezeit: {{ restTimeFormatted }}
                                </span>
                            </div>
                        </header>
                    </Motion>

                    <!-- Zutaten und Zubereitung -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.5, delay: 0.4 }"
                    >
                        <div class="grid md:grid-cols-2 gap-8 md:gap-12">
                            <!-- Zutaten -->
                            <section v-if="recipe.ingredients && recipe.ingredients.length > 0">
                                <h2 class="font-heading text-2xl md:text-3xl font-semibold text-[var(--color-forest)] mb-6">
                                    Zutaten
                                </h2>
                                <ul class="space-y-3">
                                    <li
                                        v-for="(ingredient, index) in recipe.ingredients"
                                        :key="index"
                                        class="flex items-start gap-3 text-[var(--color-warm-gray)]"
                                    >
                                        <span class="text-[var(--color-terracotta)] mt-1">•</span>
                                        <span>{{ ingredient }}</span>
                                    </li>
                                </ul>
                            </section>

                            <!-- Zubereitung -->
                            <section v-if="recipe.instructions && recipe.instructions.length > 0">
                                <h2 class="font-heading text-2xl md:text-3xl font-semibold text-[var(--color-forest)] mb-6">
                                    Zubereitung
                                </h2>
                                <ol class="space-y-4">
                                    <li
                                        v-for="(instruction, index) in recipe.instructions"
                                        :key="index"
                                        class="flex gap-4 text-[var(--color-warm-gray)]"
                                    >
                                        <span class="flex-shrink-0 w-8 h-8 rounded-full bg-[var(--color-terracotta)] text-white font-semibold flex items-center justify-center text-sm">
                                            {{ index + 1 }}
                                        </span>
                                        <span class="flex-1">{{ instruction }}</span>
                                    </li>
                                </ol>
                            </section>
                        </div>
                    </Motion>
                </article>
            </Motion>
        </div>
    </RecipeLayout>
</template>

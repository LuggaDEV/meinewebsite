<script setup lang="ts">
import { Link, router, useForm, usePage } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import SeoHead from '@/components/SeoHead.vue'
import RecipeLayout from '@/layouts/RecipeLayout.vue'
import { index as recipesIndex } from '@/routes/recipes'
import { store as storeReview, update as updateReview, destroy as destroyReview } from '@/routes/recipes/reviews'
import { computed, ref, onUnmounted, onBeforeUnmount } from 'vue'
import type { RecipeReview } from '@/types/recipe'
import type { SeoPageMeta } from '@/types'

const page = usePage()

const props = defineProps<{
    seo: SeoPageMeta
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
        average_rating?: number
        reviews_count?: number
        reviews?: RecipeReview[]
        user_review?: RecipeReview | null
    }
}>()

const isAuthenticated = computed(() => Boolean((page.props.auth as { user?: unknown })?.user))

const editingReview = ref(false)
const reviewForm = useForm({
    rating: props.recipe.user_review?.rating ?? 0,
    author_name: props.recipe.user_review?.user?.name ?? '',
    body: props.recipe.user_review?.body ?? '',
})

function openEditReview(): void {
    reviewForm.rating = props.recipe.user_review?.rating ?? 0
    reviewForm.author_name = props.recipe.user_review?.user?.name ?? props.recipe.user_review?.author_name ?? ''
    reviewForm.body = props.recipe.user_review?.body ?? ''
    editingReview.value = true
}

function cancelEditReview(): void {
    editingReview.value = false
    reviewForm.reset()
}

function submitReview(): void {
    if (reviewForm.rating < 1 || reviewForm.rating > 5) return
    if (props.recipe.user_review) {
        reviewForm.put(updateReview.url({ recipe: props.recipe.id, review: props.recipe.user_review.id }), {
            preserveScroll: true,
            onSuccess: () => { editingReview.value = false },
        })
    } else {
        reviewForm.post(storeReview.url(props.recipe.id), {
            preserveScroll: true,
            onSuccess: () => { editingReview.value = false },
        })
    }
}

function deleteReview(): void {
    if (!props.recipe.user_review) return
    if (!confirm('Möchtest du deine Bewertung wirklich löschen?')) return
    router.delete(destroyReview.url({ recipe: props.recipe.id, review: props.recipe.user_review.id }), { preserveScroll: true })
}

function formatReviewDate(iso: string): string {
    return new Date(iso).toLocaleDateString('de-DE', { day: 'numeric', month: 'long', year: 'numeric' })
}

function starClasses(index: number, rating: number): string {
    const r = Math.round(rating)
    return index < r ? 'text-[var(--color-terracotta)]' : 'text-[var(--color-warm-gray)]/30'
}

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

function printRecipe(): void {
    window.print()
}
</script>

<template>
    <SeoHead
        :title="seo.title"
        :description="seo.description"
        :image="seo.image"
        :url="seo.url"
        :type="seo.type"
    />
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
                    class="mb-8 flex flex-wrap items-center gap-4"
                >
                    <Link
                        :href="recipesIndex.url()"
                        class="no-print inline-flex items-center gap-2 text-[var(--color-forest)] hover:text-[var(--color-terracotta)] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2 rounded"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Zurück zu den Rezepten
                    </Link>
                    <div class="no-print flex items-center gap-2">
                        <button
                            type="button"
                            @click="printRecipe"
                            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-[var(--color-forest)] bg-[var(--color-cream)] border border-[var(--color-forest)]/20 rounded-lg hover:border-[var(--color-terracotta)]/50 hover:text-[var(--color-terracotta)] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h12z" />
                            </svg>
                            Drucken
                        </button>
                        <a
                            :href="`/recipe/${recipe.id}/export/json`"
                            download
                            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-[var(--color-forest)] bg-[var(--color-cream)] border border-[var(--color-forest)]/20 rounded-lg hover:border-[var(--color-terracotta)]/50 hover:text-[var(--color-terracotta)] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Als JSON exportieren
                        </a>
                    </div>
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
                        class="no-print mb-6 md:hidden"
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
                            <h1 class="mb-4 font-heading text-3xl font-semibold text-slate-950 md:text-4xl lg:text-5xl dark:text-slate-950">
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

                    <!-- Bewertungen & Rezensionen -->
                    <Motion
                        :initial="{ opacity: 0, y: 20 }"
                        :animate="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.5, delay: 0.5 }"
                        class="no-print mt-12 pt-8 border-t border-[var(--color-forest)]/10"
                    >
                        <h2 class="font-heading text-2xl md:text-3xl font-semibold text-[var(--color-forest)] mb-6">
                            Bewertungen & Rezensionen
                        </h2>

                        <!-- Übersicht -->
                        <div v-if="(recipe.reviews_count ?? 0) > 0" class="flex items-center gap-4 mb-6">
                            <div class="flex items-center gap-1" aria-label="Durchschnitt: {{ recipe.average_rating }} von 5 Sternen">
                                <template v-for="i in 5" :key="i">
                                    <svg
                                        :class="starClasses(i - 1, recipe.average_rating ?? 0)"
                                        class="w-6 h-6"
                                        fill="currentColor"
                                        viewBox="0 0 20 20"
                                    >
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                </template>
                            </div>
                            <span class="text-[var(--color-warm-gray)]">
                                {{ recipe.average_rating?.toFixed(1) }} ({{ recipe.reviews_count }} {{ recipe.reviews_count === 1 ? 'Bewertung' : 'Bewertungen' }})
                            </span>
                        </div>

                        <!-- Formular: für alle (mit und ohne Anmeldung); Bearbeiten/Löschen nur für eingeloggte User mit eigener Bewertung -->
                        <div class="mb-8">
                            <div v-if="isAuthenticated && recipe.user_review && !editingReview" class="p-4 bg-[var(--color-cream)] rounded-lg border border-[var(--color-forest)]/10">
                                <p class="text-sm font-medium text-[var(--color-forest)] mb-1">Deine Bewertung</p>
                                <div class="flex gap-1 mb-2">
                                    <template v-for="i in 5" :key="i">
                                        <span :class="i <= recipe.user_review.rating ? 'text-[var(--color-terracotta)]' : 'text-[var(--color-warm-gray)]/30'" class="text-lg">★</span>
                                    </template>
                                </div>
                                <p v-if="recipe.user_review.body" class="text-[var(--color-warm-gray)] text-sm mb-3">{{ recipe.user_review.body }}</p>
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        @click="openEditReview"
                                        class="text-sm font-medium text-[var(--color-terracotta)] hover:underline"
                                    >
                                        Bearbeiten
                                    </button>
                                    <button
                                        type="button"
                                        @click="deleteReview"
                                        class="text-sm font-medium text-[var(--color-warm-gray)] hover:underline"
                                    >
                                        Löschen
                                    </button>
                                </div>
                            </div>
                            <form v-else @submit.prevent="submitReview" class="p-4 bg-[var(--color-cream)] rounded-lg border border-[var(--color-forest)]/10 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-[var(--color-forest)] mb-2">Sterne *</label>
                                    <div class="flex gap-1">
                                        <button
                                            v-for="i in 5"
                                            :key="i"
                                            type="button"
                                            @click="reviewForm.rating = i"
                                            :class="i <= reviewForm.rating ? 'text-[var(--color-terracotta)]' : 'text-[var(--color-warm-gray)]/30'"
                                            class="text-2xl hover:opacity-80 transition-opacity"
                                            :aria-label="`${i} Sterne`"
                                        >
                                            ★
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    <label for="review-author-name" class="block text-sm font-medium text-[var(--color-forest)] mb-2">Dein Name (optional)</label>
                                    <input
                                        id="review-author-name"
                                        v-model="reviewForm.author_name"
                                        type="text"
                                        maxlength="255"
                                        class="w-full rounded-lg border border-[var(--color-forest)]/20 px-3 py-2 text-[var(--color-forest)] placeholder-[var(--color-warm-gray)] focus:border-[var(--color-terracotta)] focus:ring-1 focus:ring-[var(--color-terracotta)]"
                                        placeholder="Wie soll dein Name angezeigt werden?"
                                    />
                                </div>
                                <div>
                                    <label for="review-body" class="block text-sm font-medium text-[var(--color-forest)] mb-2">Rezension (optional)</label>
                                    <textarea
                                        id="review-body"
                                        v-model="reviewForm.body"
                                        rows="3"
                                        maxlength="2000"
                                        class="w-full rounded-lg border border-[var(--color-forest)]/20 px-3 py-2 text-[var(--color-forest)] placeholder-[var(--color-warm-gray)] focus:border-[var(--color-terracotta)] focus:ring-1 focus:ring-[var(--color-terracotta)]"
                                        placeholder="Wie hat dir das Rezept geschmeckt?"
                                    />
                                </div>
                                <div class="flex gap-2">
                                    <button
                                        type="submit"
                                        :disabled="reviewForm.rating < 1 || reviewForm.processing"
                                        class="px-4 py-2 bg-[var(--color-terracotta)] text-white rounded-lg font-medium hover:opacity-90 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        {{ recipe.user_review ? 'Aktualisieren' : 'Bewertung absenden' }}
                                    </button>
                                    <button
                                        v-if="editingReview"
                                        type="button"
                                        @click="cancelEditReview"
                                        class="px-4 py-2 text-[var(--color-warm-gray)] hover:underline"
                                    >
                                        Abbrechen
                                    </button>
                                </div>
                                <p v-if="reviewForm.errors.rating" class="text-sm text-red-600">{{ reviewForm.errors.rating }}</p>
                            </form>
                        </div>

                        <!-- Liste der Rezensionen -->
                        <ul v-if="recipe.reviews && recipe.reviews.length > 0" class="space-y-4">
                            <li
                                v-for="review in recipe.reviews"
                                :key="review.id"
                                class="p-4 rounded-lg border border-[var(--color-forest)]/10 bg-white"
                            >
                                <div class="flex gap-2 mb-1">
                                    <template v-for="i in 5" :key="i">
                                        <span :class="i <= review.rating ? 'text-[var(--color-terracotta)]' : 'text-[var(--color-warm-gray)]/30'" class="text-sm">★</span>
                                    </template>
                                    <span class="text-sm font-medium text-[var(--color-forest)]">{{ review.user.name }}</span>
                                    <span class="text-sm text-[var(--color-warm-gray)]">{{ formatReviewDate(review.created_at) }}</span>
                                </div>
                                <p v-if="review.body" class="text-[var(--color-warm-gray)] text-sm">{{ review.body }}</p>
                                <div v-if="review.reply" class="mt-3 pl-3 border-l-2 border-[var(--color-terracotta)]/30">
                                    <p class="text-xs font-medium text-[var(--color-forest)] mb-0.5">Antwort</p>
                                    <p class="text-sm text-[var(--color-warm-gray)]">{{ review.reply }}</p>
                                </div>
                            </li>
                        </ul>
                    </Motion>
                </article>
            </Motion>
        </div>
    </RecipeLayout>
</template>

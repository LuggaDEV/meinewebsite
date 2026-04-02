<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Motion } from 'motion-v'

defineProps<{
    recipe: {
        id: number
        title: string
        description: string
        image: string | null
        average_rating?: number
        reviews_count?: number
    }
}>()
</script>

<template>
    <Motion
        :initial="{ opacity: 0, y: 20 }"
        :in-view="{ opacity: 1, y: 0 }"
        :transition="{ duration: 0.5, ease: 'easeOut' }"
        :viewport="{ once: true, margin: '-50px' }"
    >
        <article class="group w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-md transition-shadow duration-300 hover:border-slate-300 hover:shadow-xl">
            <Link
                :href="`/recipe/${recipe.id}`"
                class="block rounded-2xl text-slate-950 no-underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:text-slate-950"
            >
                <div class="aspect-[4/3] overflow-hidden bg-slate-100">
                    <img
                        v-if="recipe.image"
                        :src="recipe.image"
                        :alt="recipe.title"
                        class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                    />
                    <div v-else class="flex h-full w-full items-center justify-center bg-slate-100">
                        <svg class="h-16 w-16 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="border-t border-slate-100 p-5 md:p-6">
                    <h3 class="font-heading text-lg font-semibold text-slate-950 transition-colors group-hover:text-[var(--color-terracotta)] md:text-xl dark:text-slate-950">
                        {{ recipe.title }}
                    </h3>
                    <p class="mt-2 line-clamp-2 text-sm leading-relaxed text-slate-600 dark:text-slate-600">
                        {{ recipe.description }}
                    </p>
                    <div v-if="(recipe.reviews_count ?? 0) > 0" class="mt-2 flex items-center gap-1.5" aria-label="Durchschnitt: {{ recipe.average_rating }} von 5 Sternen">
                        <template v-for="i in 5" :key="i">
                            <svg
                                :class="(i <= Math.round(recipe.average_rating ?? 0)) ? 'text-[var(--color-terracotta)]' : 'text-slate-200'"
                                class="w-4 h-4"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </template>
                        <span class="text-xs text-slate-600 dark:text-slate-600">({{ recipe.reviews_count }})</span>
                    </div>
                    <span class="mt-3 inline-block text-sm font-medium text-[var(--color-terracotta)]">
                        Rezept ansehen →
                    </span>
                </div>
            </Link>
        </article>
    </Motion>
</template>

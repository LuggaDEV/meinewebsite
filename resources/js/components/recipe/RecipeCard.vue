<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Motion } from 'motion-v'

defineProps<{
    recipe: {
        id: number
        title: string
        description: string
        image: string | null
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
        <article class="group bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow overflow-hidden border border-[var(--color-forest)]/5 w-full">
            <Link :href="`/recipe/${recipe.id}`" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2 rounded-xl">
                <div class="aspect-[4/3] overflow-hidden bg-[var(--color-forest)]/5">
                    <img
                        v-if="recipe.image"
                        :src="recipe.image"
                        :alt="recipe.title"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center bg-[var(--color-forest)]/5">
                        <svg class="w-16 h-16 text-[var(--color-forest)]/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="p-5 md:p-6">
                    <h3 class="font-heading text-lg md:text-xl font-semibold text-[var(--color-forest)] group-hover:text-[var(--color-terracotta)] transition-colors">
                        {{ recipe.title }}
                    </h3>
                    <p class="mt-2 text-sm text-[var(--color-warm-gray)] line-clamp-2">
                        {{ recipe.description }}
                    </p>
                    <span class="mt-3 inline-block text-sm font-medium text-[var(--color-terracotta)]">
                        Rezept ansehen →
                    </span>
                </div>
            </Link>
        </article>
    </Motion>
</template>

<script setup>
import { RouterLink } from 'vue-router'
import { Motion } from 'motion-v'

defineProps({
  recipe: {
    type: Object,
    required: true,
  },
})
</script>

<template>
  <Motion
    :initial="{ opacity: 0, y: 20 }"
    :in-view="{ opacity: 1, y: 0 }"
    :transition="{ duration: 0.5, ease: 'easeOut' }"
    :viewport="{ once: true, margin: '-50px' }"
  >
    <article class="group bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow overflow-hidden border border-[var(--color-forest)]/5">
    <RouterLink :to="{ name: 'recipe', params: { id: recipe.id } }" class="block focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2 rounded-xl">
      <div class="aspect-[4/3] overflow-hidden bg-[var(--color-forest)]/5">
        <img
          :src="recipe.image"
          :alt="recipe.title"
          class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
        />
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
    </RouterLink>
    </article>
  </Motion>
</template>

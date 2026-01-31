<script setup>
import { ref, onMounted } from 'vue'
import { Motion } from 'motion-v'
import RecipeCard from './RecipeCard.vue'
import { getRecipes } from '../services/recipeApi.js'

const recipes = ref([])
const loading = ref(true)
const error = ref(null)

onMounted(async () => {
  try {
    recipes.value = await getRecipes()
  } catch (err) {
    error.value = 'Fehler beim Laden der Rezepte'
    console.error(err)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <section id="recipes" class="py-16 md:py-24">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <Motion
        :initial="{ opacity: 0, y: 20 }"
        :in-view="{ opacity: 1, y: 0 }"
        :transition="{ duration: 0.6, ease: 'easeOut' }"
        :viewport="{ once: true }"
      >
        <h2 class="font-heading text-3xl md:text-4xl font-semibold text-[var(--color-forest)] text-center mb-12">
          Rezepte
        </h2>
      </Motion>
      <div v-if="loading" class="text-center text-[var(--color-warm-gray)]">
        Rezepte werden geladen...
      </div>
      <div v-else-if="error" class="text-center text-red-600">
        {{ error }}
      </div>
      <div v-else-if="recipes.length === 0" class="text-center text-[var(--color-warm-gray)]">
        Noch keine Rezepte vorhanden.
      </div>
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
        <RecipeCard
          v-for="recipe in recipes"
          :key="recipe.id"
          :recipe="recipe"
        />
      </div>
    </div>
  </section>
</template>

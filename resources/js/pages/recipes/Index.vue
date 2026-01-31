<script setup lang="ts">
import { computed } from 'vue'
import RecipeLayout from '@/layouts/RecipeLayout.vue'
import HeroSection from '@/components/recipe/HeroSection.vue'
import RecipesSection from '@/components/recipe/RecipesSection.vue'
import AboutSection from '@/components/recipe/AboutSection.vue'
import InstagramSection from '@/components/recipe/InstagramSection.vue'

const props = defineProps<{
    recipes: Array<{
        id: number
        title: string
        description: string
        image: string | null
    }>
    about: {
        id?: number
        title: string
        content: string
        image: string | null
    } | null
}>()

// Für Instagram-Sektion: letzte 6 Rezepte (oder alle, wenn weniger vorhanden)
const instagramRecipes = computed(() => {
    return props.recipes.slice(-6).reverse()
})
</script>

<template>
    <RecipeLayout>
        <HeroSection />
        <RecipesSection :recipes="recipes" />
        <AboutSection :about="about" />
        <InstagramSection :recipes="instagramRecipes" />
    </RecipeLayout>
</template>

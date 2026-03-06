<script setup lang="ts">
import RecipeLayout from '@/layouts/RecipeLayout.vue'
import HeroSection from '@/components/recipe/HeroSection.vue'
import RecipesSection from '@/components/recipe/RecipesSection.vue'
import AboutSection from '@/components/recipe/AboutSection.vue'
import InstagramSection from '@/components/recipe/InstagramSection.vue'
import type { Recipe } from '@/types/recipe'
import type { InstagramPost } from '@/types/instagram'

defineProps<{
    recipes: Recipe[]
    about: {
        id?: number
        title: string
        content: string
        image: string | null
    } | null
    instagramFeed: InstagramPost[]
}>()
</script>

<template>
    <RecipeLayout>
        <HeroSection />
        <RecipesSection :recipes="recipes" />
        <AboutSection :about="about" />
        <InstagramSection :feed="instagramFeed" />
    </RecipeLayout>
</template>

<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue'
import RecipeLayout from '@/layouts/RecipeLayout.vue'
import RecipesSection from '@/components/recipe/RecipesSection.vue'
import type { Recipe } from '@/types/recipe'
import type { SeoPageMeta } from '@/types'

defineProps<{
    recipes: Recipe[]
    seo: SeoPageMeta
}>()
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
        <RecipesSection :recipes="recipes" />
    </RecipeLayout>
</template>

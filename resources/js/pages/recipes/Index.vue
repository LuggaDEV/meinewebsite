<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue'
import RecipeLayout from '@/layouts/RecipeLayout.vue'
import HeroSection from '@/components/recipe/HeroSection.vue'
import RecipesSection from '@/components/recipe/RecipesSection.vue'
import AboutSection from '@/components/recipe/AboutSection.vue'
import CareerTimelineSection from '@/components/recipe/CareerTimelineSection.vue'
import InstagramSection from '@/components/recipe/InstagramSection.vue'
import type { Recipe } from '@/types/recipe'
import type { InstagramPost } from '@/types/instagram'
import type { SeoPageMeta } from '@/types'

defineProps<{
    recipes: Recipe[]
    about: {
        id?: number
        title: string
        content: string
        image: string | null
        career_timeline?: Array<{
            organization: string
            role: string
            period: string
            location?: string | null
        }> | null
    } | null
    instagramFeed: InstagramPost[]
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
        <HeroSection />
        <RecipesSection :recipes="recipes" />
        <AboutSection :about="about" />
        <CareerTimelineSection
            v-if="about?.career_timeline?.length"
            :entries="about.career_timeline"
        />
        <InstagramSection :feed="instagramFeed" />
    </RecipeLayout>
</template>

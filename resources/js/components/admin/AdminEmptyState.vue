<script setup lang="ts">
import type { Component } from 'vue'

defineProps<{
    title: string
    description?: string
    icon?: Component
}>()
</script>

<template>
    <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-[var(--color-forest)]/15 bg-[var(--color-forest)]/[0.02] px-6 py-16 text-center">
        <div
            v-if="icon"
            class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--color-forest)]/10 text-[var(--color-terracotta)]"
            aria-hidden
        >
            <component :is="icon" class="h-8 w-8" />
        </div>
        <h3 class="font-heading text-lg font-semibold text-[var(--color-forest)]">
            {{ title }}
        </h3>
        <p v-if="description" class="mt-2 max-w-sm text-sm text-[var(--color-warm-gray)]">
            {{ description }}
        </p>
        <div class="mt-6">
            <slot />
        </div>
    </div>
</template>

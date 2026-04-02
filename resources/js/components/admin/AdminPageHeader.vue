<script setup lang="ts">
defineProps<{
    title: string
    subtitle?: string
}>()
</script>

<template>
    <div class="mb-8 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between sm:gap-4">
        <div class="min-w-0">
            <p class="mb-1 text-xs font-semibold uppercase tracking-[0.15em] text-[var(--color-terracotta)]">
                Admin
            </p>
            <h1 class="font-heading text-2xl font-semibold tracking-tight text-[var(--color-forest)] sm:text-3xl">
                {{ title }}
            </h1>
            <p v-if="subtitle" class="mt-1 text-sm text-[var(--color-warm-gray)]">
                {{ subtitle }}
            </p>
        </div>
        <div v-if="$slots.actions" class="mt-4 flex shrink-0 flex-wrap items-center gap-2 sm:mt-0">
            <slot name="actions" />
        </div>
    </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
</script>

<template>
    <section id="hero" class="relative overflow-hidden py-20 md:py-28 lg:py-36">
        <div
            class="absolute inset-0 bg-gradient-to-b from-white via-slate-50/80 to-white"
            aria-hidden="true"
        />
        <div
            class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-[var(--color-terracotta)]/[0.06] blur-3xl md:h-96 md:w-96"
            aria-hidden="true"
        />
        <Motion
            :initial="{ opacity: 0, y: 20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.6, ease: 'easeOut' }"
            class="relative mx-auto max-w-6xl px-4 text-center sm:px-6 lg:px-8"
        >
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.6, delay: 0.2, ease: 'easeOut' }"
            >
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-terracotta)]">
                    Kochen
                </p>
                <h1 class="font-heading text-4xl font-semibold tracking-tight text-[var(--color-forest)] md:text-5xl lg:text-6xl">
                    Luca Themann
                </h1>
            </Motion>
            <Motion
                :initial="{ opacity: 0, y: 10 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.6, delay: 0.4, ease: 'easeOut' }"
            >
                <p class="mx-auto mt-4 max-w-2xl text-lg text-[var(--color-warm-gray)] md:text-xl">
                    Rezepte und mehr
                </p>
                <Link
                    href="/#recipes"
                    class="mt-8 inline-flex items-center justify-center rounded-full bg-[var(--color-forest)] px-8 py-3 text-sm font-semibold text-white shadow-md transition hover:bg-[var(--color-terracotta)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2"
                >
                    Zu den Rezepten
                </Link>
            </Motion>
        </Motion>
    </section>
</template>

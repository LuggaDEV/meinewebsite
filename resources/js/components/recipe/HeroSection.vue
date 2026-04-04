<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import { index as recipesIndex } from '@/routes/recipes'

const easeOutExpo = [0.22, 1, 0.36, 1] as const
</script>

<template>
    <section id="hero" class="relative overflow-hidden bg-white py-20 md:py-28 lg:py-36">
        <div class="pointer-events-none absolute inset-0 overflow-hidden" aria-hidden="true">
            <div
                class="hero-blob hero-blob-a absolute -left-[18%] top-[-10%] h-[min(52vw,30rem)] w-[min(52vw,30rem)] rounded-full bg-[var(--color-terracotta)]/[0.09] blur-3xl"
            />
            <div
                class="hero-blob hero-blob-b absolute -bottom-[15%] -right-[12%] h-[min(48vw,26rem)] w-[min(48vw,26rem)] rounded-full bg-[var(--color-forest)]/[0.07] blur-3xl"
            />
            <div
                class="hero-blob hero-blob-c absolute left-1/2 top-1/2 h-[min(60vw,36rem)] w-[min(60vw,36rem)] -translate-x-1/2 -translate-y-1/2 rounded-full bg-gradient-to-tr from-[var(--color-terracotta)]/[0.04] to-transparent blur-3xl"
            />
        </div>

        <Motion
            :initial="{ opacity: 0, y: 20 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.65, ease: easeOutExpo }"
            class="relative mx-auto max-w-6xl px-4 text-center sm:px-6 lg:px-8"
        >
            <Motion
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.65, delay: 0.12, ease: easeOutExpo }"
            >
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-terracotta)]">
                    Kochen
                </p>
                <h1 class="font-heading text-4xl font-semibold tracking-tight text-[var(--color-forest)] md:text-5xl lg:text-6xl">
                    Luca Themann
                </h1>
            </Motion>
            <Motion
                :initial="{ opacity: 0, y: 12 }"
                :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.65, delay: 0.28, ease: easeOutExpo }"
            >
                <p class="mx-auto mt-4 max-w-2xl text-lg text-[var(--color-warm-gray)] md:text-xl">
                    Rezepte und mehr
                </p>
                <Link
                    :href="recipesIndex.url()"
                    class="mt-8 inline-flex items-center justify-center rounded-full bg-[var(--color-forest)] px-8 py-3 text-sm font-semibold text-white shadow-md transition-colors duration-300 hover:bg-[var(--color-terracotta)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2"
                >
                    Zu den Rezepten
                </Link>
            </Motion>
        </Motion>
    </section>
</template>

<style scoped>
@media (prefers-reduced-motion: no-preference) {
    .hero-blob-a {
        animation: hero-float-a 24s ease-in-out infinite;
    }

    .hero-blob-b {
        animation: hero-float-b 30s ease-in-out infinite;
    }

    .hero-blob-c {
        animation: hero-float-c 20s ease-in-out infinite;
    }
}

@keyframes hero-float-a {
    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }

    50% {
        transform: translate(6%, 8%) scale(1.06);
    }
}

@keyframes hero-float-b {
    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }

    50% {
        transform: translate(-5%, -6%) scale(1.05);
    }
}

@keyframes hero-float-c {
    0%,
    100% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }

    50% {
        transform: translate(-48%, -52%) scale(1.03);
        opacity: 0.85;
    }
}
</style>

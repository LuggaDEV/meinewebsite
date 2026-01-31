<script setup>
import { Motion } from 'motion-v'
</script>

<template>
  <section id="hero" class="relative py-20 md:py-28 lg:py-36 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-[var(--color-cream)] via-[var(--color-cream)] to-[var(--color-forest)]/5" aria-hidden="true" />
    <Motion
      :initial="{ opacity: 0, y: 20 }"
      :animate="{ opacity: 1, y: 0 }"
      :transition="{ duration: 0.6, ease: 'easeOut' }"
      class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 text-center"
    >
      <Motion
        :initial="{ opacity: 0, y: 10 }"
        :animate="{ opacity: 1, y: 0 }"
        :transition="{ duration: 0.6, delay: 0.2, ease: 'easeOut' }"
      >
        <h1 class="font-heading text-4xl md:text-5xl lg:text-6xl font-semibold text-[var(--color-forest)] tracking-tight">
          Luca Themann
        </h1>
      </Motion>
      <Motion
        :initial="{ opacity: 0, y: 10 }"
        :animate="{ opacity: 1, y: 0 }"
        :transition="{ duration: 0.6, delay: 0.4, ease: 'easeOut' }"
      >
        <p class="mt-4 text-lg md:text-xl text-[var(--color-warm-gray)] max-w-2xl mx-auto">
          Rezepte und mehr
        </p>
      </Motion>
    </Motion>
  </section>
</template>

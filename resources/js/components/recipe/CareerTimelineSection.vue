<script setup lang="ts">
import { Motion } from 'motion-v'

const easeOutExpo = [0.22, 1, 0.36, 1] as const

type CareerTimelineEntry = {
    organization: string
    role: string
    period: string
    location?: string | null
}

defineProps<{
    entries: CareerTimelineEntry[]
}>()
</script>

<template>
    <section id="career" class="relative isolate overflow-hidden border-t border-slate-200/60 bg-white py-20 md:py-28">
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div
                class="absolute -right-[12%] top-[10%] h-[360px] w-[360px] rounded-full bg-[var(--color-terracotta)]/[0.06] blur-3xl"
            />
            <div
                class="absolute -left-[8%] bottom-[5%] h-[280px] w-[280px] rounded-full bg-[var(--color-forest)]/[0.05] blur-3xl"
            />
        </div>

        <div class="relative mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <Motion
                :initial="{ opacity: 0, y: 22 }"
                :in-view="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.6, ease: easeOutExpo }"
                :viewport="{ once: true, margin: '-80px' }"
                class="mb-12 md:mb-16 md:flex md:items-end md:justify-between md:gap-8"
            >
                <div class="max-w-2xl">
                    <div
                        class="mb-4 inline-flex items-center gap-2 rounded-full border border-slate-200/80 bg-white/70 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-[var(--color-terracotta)] shadow-sm backdrop-blur-sm"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--color-terracotta)]" aria-hidden="true" />
                        Stationen
                    </div>
                    <h2
                        class="font-heading text-3xl font-semibold tracking-tight text-[var(--color-forest)] sm:text-4xl md:text-[2.65rem] md:leading-[1.12]"
                    >
                        Berufserfahrung
                    </h2>
                    <p class="mt-4 max-w-xl text-[1.05rem] leading-relaxed text-slate-600 md:text-[1.0625rem]">
                        Ein Auszug aus Küchen und Betrieben, in denen ich gearbeitet habe.
                    </p>
                </div>
                <div class="mt-8 hidden h-px flex-1 bg-gradient-to-r from-slate-200 to-transparent md:mt-0 md:block" />
            </Motion>

            <ol class="relative ml-2 list-none border-l-2 border-[var(--color-terracotta)]/25 pl-8 md:ml-4 md:pl-12">
                <li
                    v-for="(entry, index) in entries"
                    :key="`${entry.organization}-${entry.period}-${index}`"
                    class="relative pb-12 last:pb-0"
                >
                    <span
                        class="absolute -left-[25px] top-2 flex h-3.5 w-3.5 rounded-full border-2 border-white bg-[var(--color-terracotta)] shadow-[0_0_0_4px_rgba(255,255,255,1)] md:-left-[33px] md:h-4 md:w-4"
                        aria-hidden="true"
                    />
                    <Motion
                        :initial="{ opacity: 0, x: -12 }"
                        :in-view="{ opacity: 1, x: 0 }"
                        :transition="{ duration: 0.5, delay: 0.05 * index, ease: easeOutExpo }"
                        :viewport="{ once: true, margin: '-40px' }"
                    >
                        <div
                            class="rounded-2xl border border-slate-200/80 bg-slate-50/80 p-6 shadow-[0_4px_40px_-12px_rgba(15,23,42,0.1)] backdrop-blur-sm sm:p-7"
                        >
                            <p class="font-heading text-lg font-semibold text-[var(--color-forest)] sm:text-xl">
                                {{ entry.organization }}
                            </p>
                            <p v-if="entry.location" class="mt-1 text-sm font-medium text-slate-500">
                                {{ entry.location }}
                            </p>
                            <p class="mt-3 text-base font-medium text-[var(--color-terracotta)] sm:text-[1.05rem]">
                                {{ entry.role }}
                            </p>
                            <p class="mt-2 text-sm font-medium tracking-wide text-slate-600">{{ entry.period }}</p>
                        </div>
                    </Motion>
                </li>
            </ol>
        </div>
    </section>
</template>

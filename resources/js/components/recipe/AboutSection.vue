<script setup lang="ts">
import { computed } from 'vue'
import { Motion } from 'motion-v'

const easeOutExpo = [0.22, 1, 0.36, 1] as const

const props = defineProps<{
    about: {
        id?: number
        title: string
        content: string
        image: string | null
    } | null
}>()

const paragraphs = computed(() => {
    if (!props.about?.content) {
        return []
    }

    return props.about.content
        .split('\n')
        .map((p) => p.trim())
        .filter(Boolean)
})

const leadParagraph = computed(() => paragraphs.value[0] ?? '')
const bodyParagraphs = computed(() => paragraphs.value.slice(1))
</script>

<template>
    <section id="about" class="relative isolate overflow-hidden border-t border-slate-200/60 bg-slate-50 py-20 md:py-28">
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <div
                class="absolute -left-[20%] top-0 h-[420px] w-[420px] rounded-full bg-[var(--color-terracotta)]/[0.07] blur-3xl"
            />
            <div
                class="absolute -right-[15%] bottom-0 h-[380px] w-[380px] rounded-full bg-[var(--color-forest)]/[0.06] blur-3xl"
            />
            <div
                class="absolute inset-0 opacity-[0.4]"
                style="
                    background-image: radial-gradient(circle at 1px 1px, rgb(15 23 42 / 0.04) 1px, transparent 0);
                    background-size: 32px 32px;
                "
            />
        </div>

        <Motion
            :initial="{ opacity: 0, y: 24 }"
            :in-view="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.65, ease: easeOutExpo }"
            :viewport="{ once: true, margin: '-80px' }"
            class="relative mx-auto max-w-6xl px-4 sm:px-6 lg:px-8"
        >
            <div class="mb-12 md:mb-16 md:flex md:items-end md:justify-between md:gap-8">
                <div class="max-w-2xl">
                    <div
                        class="mb-4 inline-flex items-center gap-2 rounded-full border border-slate-200/80 bg-white/70 px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.22em] text-[var(--color-terracotta)] shadow-sm backdrop-blur-sm"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-[var(--color-terracotta)]" aria-hidden="true" />
                        Portrait
                    </div>
                    <h2
                        class="font-heading text-3xl font-semibold tracking-tight text-[var(--color-forest)] sm:text-4xl md:text-[2.65rem] md:leading-[1.12]"
                    >
                        {{ about?.title || 'Über mich' }}
                    </h2>
                </div>
                <div class="mt-8 hidden h-px flex-1 bg-gradient-to-r from-slate-200 to-transparent md:mt-0 md:block" />
            </div>

            <div v-if="about" class="grid gap-10 lg:grid-cols-12 lg:gap-12 lg:items-start">
                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :in-view="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.6, delay: 0.05, ease: easeOutExpo }"
                    :viewport="{ once: true }"
                    class="lg:col-span-5"
                >
                    <div class="lg:sticky lg:top-28">
                        <div
                            class="group relative mx-auto max-w-md overflow-hidden rounded-[1.75rem] bg-white p-1.5 shadow-[0_24px_60px_-12px_rgba(15,23,42,0.18)] ring-1 ring-slate-900/[0.06] lg:mx-0"
                        >
                            <div
                                class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-[var(--color-terracotta)]/15 blur-2xl transition-opacity duration-500 group-hover:opacity-80"
                                aria-hidden="true"
                            />
                            <div
                                class="relative aspect-[4/5] overflow-hidden rounded-[1.35rem] bg-gradient-to-br from-slate-100 to-slate-50"
                            >
                                <div
                                    v-if="about.image"
                                    class="absolute inset-0 bg-gradient-to-t from-[var(--color-forest)]/25 via-transparent to-transparent opacity-60 mix-blend-multiply"
                                    aria-hidden="true"
                                />
                                <img
                                    v-if="about.image"
                                    :src="about.image"
                                    :alt="about.title"
                                    class="h-full w-full object-cover transition duration-700 ease-out group-hover:scale-[1.035]"
                                />
                                <div
                                    v-else
                                    class="flex h-full flex-col items-center justify-center gap-4 p-10 text-center"
                                >
                                    <div
                                        class="flex h-20 w-20 items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-white/80 text-slate-300"
                                    >
                                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.25"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                            />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-slate-500">Portrait folgt – Bild im Admin hinterlegen.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </Motion>

                <div class="min-w-0 space-y-8 lg:col-span-7">
                    <Motion
                        v-if="leadParagraph"
                        :initial="{ opacity: 0, y: 18 }"
                        :in-view="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.55, delay: 0.08, ease: easeOutExpo }"
                        :viewport="{ once: true }"
                    >
                        <div
                            class="relative rounded-2xl border border-white/80 bg-white/75 p-6 shadow-[0_4px_40px_-8px_rgba(15,23,42,0.12)] backdrop-blur-md sm:p-8 md:p-9"
                        >
                            <div
                                class="absolute left-0 top-6 bottom-6 w-1 rounded-full bg-gradient-to-b from-[var(--color-terracotta)] to-[var(--color-terracotta)]/40 sm:top-8 sm:bottom-8"
                                aria-hidden="true"
                            />
                            <p
                                class="pl-5 text-lg font-medium leading-[1.65] text-[var(--color-forest)] sm:text-xl md:pl-6 md:text-2xl md:leading-snug [&_strong]:font-semibold [&_strong]:text-[var(--color-forest)]"
                            >
                                {{ leadParagraph }}
                            </p>
                        </div>
                    </Motion>

                    <div class="space-y-6">
                        <Motion
                            v-for="(paragraph, index) in bodyParagraphs"
                            :key="index"
                            :initial="{ opacity: 0, y: 14 }"
                            :in-view="{ opacity: 1, y: 0 }"
                            :transition="{ duration: 0.48, delay: 0.06 + index * 0.05, ease: easeOutExpo }"
                            :viewport="{ once: true }"
                        >
                            <p
                                class="text-[1.05rem] leading-[1.8] text-slate-600 md:text-[1.0625rem] [&_strong]:font-semibold [&_strong]:text-slate-800"
                            >
                                {{ paragraph }}
                            </p>
                        </Motion>
                    </div>
                </div>
            </div>

            <div
                v-else
                class="overflow-hidden rounded-3xl border border-dashed border-slate-200/90 bg-white/80 p-10 text-center shadow-sm backdrop-blur-sm sm:p-14"
            >
                <div
                    class="mx-auto mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-[var(--color-terracotta)]/15 to-[var(--color-terracotta)]/5 text-[var(--color-terracotta)]"
                >
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>
                <p class="font-heading text-lg font-semibold text-[var(--color-forest)]">Noch kein Inhalt</p>
                <p class="mx-auto mt-2 max-w-sm text-sm leading-relaxed text-slate-500">
                    Unter <span class="font-medium text-slate-700">Admin → Über mich</span> kannst du Titel, Text und Bild pflegen.
                </p>
            </div>
        </Motion>
    </section>
</template>

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
    <section
        id="about"
        class="about-section relative overflow-hidden border-t border-slate-200/80 bg-gradient-to-b from-slate-50 via-white to-[var(--color-cream)] py-16 md:py-24"
    >
        <div
            class="pointer-events-none absolute inset-0 opacity-[0.55]"
            aria-hidden="true"
            style="
                background-image: radial-gradient(circle at 1px 1px, rgb(15 23 42 / 0.05) 1px, transparent 0);
                background-size: 28px 28px;
            "
        />
        <Motion
            :initial="{ opacity: 0, y: 28 }"
            :in-view="{ opacity: 1, y: 0 }"
            :transition="{ duration: 0.62, ease: easeOutExpo }"
            :viewport="{ once: true, margin: '-100px' }"
            class="relative mx-auto max-w-4xl px-4 sm:px-6 lg:px-8"
        >
            <p class="mb-3 text-center text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-terracotta)]">
                Portrait
            </p>
            <h2 class="mb-10 text-center font-heading text-3xl font-semibold text-[var(--color-forest)] md:text-4xl">
                {{ about?.title || 'Über mich' }}
            </h2>

            <div v-if="about" class="flex flex-col items-start gap-8 md:flex-row md:gap-12">
                <Motion
                    :initial="{ opacity: 0, scale: 0.96 }"
                    :in-view="{ opacity: 1, scale: 1 }"
                    :transition="{ duration: 0.62, delay: 0.08, ease: easeOutExpo }"
                    :viewport="{ once: true }"
                    class="w-full shrink-0 md:w-64 lg:w-80"
                >
                    <div
                        class="group relative flex aspect-square items-center justify-center overflow-hidden rounded-2xl border border-[var(--color-forest)]/10 bg-gradient-to-br from-[var(--color-cream)] to-[var(--color-terracotta)]/[0.06] shadow-lg ring-1 ring-[var(--color-forest)]/[0.04] transition-shadow duration-500 ease-out hover:shadow-xl hover:ring-2 hover:ring-[var(--color-terracotta)]/15"
                    >
                        <div v-if="about.image" class="h-full w-full overflow-hidden">
                            <img
                                :src="about.image"
                                :alt="about.title"
                                class="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-[1.04]"
                            />
                        </div>
                        <div v-else class="p-8 text-center">
                            <div
                                class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-2xl bg-[var(--color-forest)]/[0.04] text-[var(--color-forest)]/35"
                            >
                                <svg class="h-14 w-14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                                    />
                                </svg>
                            </div>
                            <p class="text-sm font-medium leading-relaxed text-[var(--color-warm-gray)]">
                                Hier erscheint bald dein Portrait.
                            </p>
                        </div>
                    </div>
                </Motion>

                <div class="min-w-0 flex-1 space-y-0 text-[var(--color-warm-gray)]">
                    <Motion
                        v-if="leadParagraph"
                        :initial="{ opacity: 0, y: 16 }"
                        :in-view="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.55, delay: 0.06, ease: easeOutExpo }"
                        :viewport="{ once: true }"
                        class="mb-6"
                    >
                        <p
                            class="max-w-prose text-xl font-medium leading-relaxed text-[var(--color-forest)]/90 md:text-2xl [&_strong]:font-semibold [&_strong]:text-[var(--color-forest)]"
                        >
                            {{ leadParagraph }}
                        </p>
                    </Motion>
                    <Motion
                        v-for="(paragraph, index) in bodyParagraphs"
                        :key="index"
                        :initial="{ opacity: 0, y: 14 }"
                        :in-view="{ opacity: 1, y: 0 }"
                        :transition="{ duration: 0.5, delay: 0.1 + index * 0.06, ease: easeOutExpo }"
                        :viewport="{ once: true }"
                        class="prose prose-lg max-w-none text-[var(--color-warm-gray)] prose-p:leading-relaxed"
                    >
                        <p>{{ paragraph }}</p>
                    </Motion>
                </div>
            </div>
            <div v-else class="rounded-2xl border border-dashed border-slate-200/80 bg-white/60 px-6 py-12 text-center backdrop-blur-sm">
                <div
                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[var(--color-terracotta)]/10 text-[var(--color-terracotta)]"
                >
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>
                <p class="font-medium text-[var(--color-forest)]">Noch kein Portrait-Text</p>
                <p class="mt-2 text-sm text-[var(--color-warm-gray)]">
                    Dieser Bereich kann im Admin unter „Über mich“ befüllt werden.
                </p>
            </div>
        </Motion>
    </section>
</template>

<script setup lang="ts">
import { Motion } from 'motion-v'
import type { InstagramPost } from '@/types/instagram'

const instagramUrl = 'https://www.instagram.com/luca_themann/'

defineProps<{
    feed: InstagramPost[]
}>()

const PREVIEW_AT_SECONDS = 5

function useFifthSecondAsPreview(el: EventTarget | null): void {
    const video = el as HTMLVideoElement | null
    if (!video) return
    const duration = video.duration
    const targetTime =
        Number.isFinite(duration) && duration >= PREVIEW_AT_SECONDS
            ? PREVIEW_AT_SECONDS
            : duration > 0
              ? duration / 2
              : 0
    video.currentTime = targetTime
}

function pauseAfterSeek(el: EventTarget | null): void {
    (el as HTMLVideoElement | null)?.pause()
}
</script>

<template>
    <section id="instagram" class="border-t border-slate-200 bg-white py-16 md:py-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <Motion
                :initial="{ opacity: 0, y: 20 }"
                :in-view="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.6, ease: 'easeOut' }"
                :viewport="{ once: true }"
            >
                <p class="mb-3 text-center text-xs font-semibold uppercase tracking-[0.2em] text-[var(--color-terracotta)]">
                    Social
                </p>
                <h2 class="mb-4 text-center font-heading text-3xl font-semibold text-[var(--color-forest)] md:text-4xl">
                    Folge mir auf Instagram
                </h2>
                <p class="text-center text-[var(--color-warm-gray)] mb-12 max-w-xl mx-auto">
                    Aktuelle Kreationen und Einblicke hinter die Kulissen.
                </p>
            </Motion>
            <div v-if="feed.length === 0" class="text-center text-[var(--color-warm-gray)] py-8">
                Derzeit keine Posts sichtbar.
            </div>
            <div v-else class="flex flex-wrap justify-center gap-2 md:gap-4">
                <Motion
                    v-for="(post, i) in feed"
                    :key="post.id"
                    :initial="{ opacity: 0, scale: 0.9 }"
                    :in-view="{ opacity: 1, scale: 1 }"
                    :transition="{ duration: 0.4, delay: i * 0.1, ease: 'easeOut' }"
                    :viewport="{ once: true, margin: '-50px' }"
                    class="w-[calc(50%-0.25rem)] sm:w-[calc(33.333%-0.67rem)] md:w-[calc(25%-0.75rem)] lg:w-[calc(16.666%-0.83rem)] max-w-[200px]"
                >
                    <a
                        :href="post.permalink"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="group block aspect-square w-full overflow-hidden rounded-xl shadow-sm ring-1 ring-[var(--color-forest)]/[0.06] transition-shadow hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                    >
                        <video
                            v-if="post.media_type === 'VIDEO' && post.video_url"
                            :src="post.video_url"
                            muted
                            loop
                            playsinline
                            preload="auto"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                            @loadedmetadata="useFifthSecondAsPreview($event.target)"
                            @seeked="pauseAfterSeek($event.target)"
                        />
                        <img
                            v-else
                            :src="post.media_url"
                            :alt="post.caption ?? 'Instagram Post'"
                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                        />
                    </a>
                </Motion>
            </div>
            <div class="mt-10 text-center">
                <a
                    :href="instagramUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-[var(--color-forest)] text-white font-medium rounded-lg hover:bg-[var(--color-terracotta)] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-forest)]"
                >
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                    @luca_themann auf Instagram
                </a>
            </div>
        </div>
    </section>
</template>

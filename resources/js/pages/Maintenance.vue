<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps<{
    message: string | null
    endsAt: string | null
}>()

const now = ref(Date.now())
let tick: ReturnType<typeof setInterval> | null = null

const endTime = computed(() =>
    props.endsAt ? new Date(props.endsAt).getTime() : null
)

const isFuture = computed(() => {
    const end = endTime.value
    return end !== null && end > now.value
})

const countdown = computed(() => {
    const end = endTime.value
    if (end === null || end <= now.value) {
        return { days: 0, hours: 0, minutes: 0, seconds: 0, done: true }
    }
    const diff = Math.max(0, Math.floor((end - now.value) / 1000))
    const days = Math.floor(diff / 86400)
    const hours = Math.floor((diff % 86400) / 3600)
    const minutes = Math.floor((diff % 3600) / 60)
    const seconds = diff % 60
    return { days, hours, minutes, seconds, done: false }
})

function pad(n: number): string {
    return String(n).padStart(2, '0')
}

const currentYear = new Date().getFullYear()
const socialLinks = [
    { name: 'Instagram', href: 'https://www.instagram.com/luca_themann/' },
    { name: 'LinkedIn', href: 'https://www.linkedin.com/in/luca-elias-themann-6b47963a8/' },
    { name: 'Gronda', href: 'https://gronda.com/@luca-elias', logoUrl: 'https://cdn.prod.website-files.com/62f1652df4eb674e370a0f6f/68303447efd928e224df6f17_logo_dark.png' },
]

onMounted(() => {
    if (endTime.value !== null) {
        tick = setInterval(() => {
            now.value = Date.now()
            if (countdown.value.done) {
                if (tick) clearInterval(tick)
                router.reload()
            }
        }, 1000)
    }
})

onUnmounted(() => {
    if (tick) clearInterval(tick)
})
</script>

<template>
    <div class="flex min-h-screen flex-col bg-[var(--color-cream)]">
        <main class="flex flex-1 flex-col items-center justify-center px-4 py-12 sm:py-16">
            <div class="w-full max-w-lg text-center">
                <p class="text-xs font-medium uppercase tracking-widest text-[var(--color-terracotta)]">
                    Wartungsarbeiten
                </p>
                <h1 class="mt-3 font-heading text-2xl font-semibold text-[var(--color-forest)] sm:text-3xl">
                    Wir sind gleich wieder für Sie da
                </h1>
                <p class="mt-4 text-[var(--color-warm-gray)]">
                    {{ message || 'Wir arbeiten gerade an einigen Verbesserungen. Bitte schauen Sie in Kürze wieder vorbei.' }}
                </p>

                <div
                    v-if="endsAt && isFuture"
                    class="mt-8 flex flex-wrap items-center justify-center gap-2 font-mono text-xl text-[var(--color-forest)] sm:gap-3 sm:text-2xl"
                >
                    <span class="rounded-lg bg-white px-3 py-2 shadow-sm border border-[var(--color-forest)]/10">{{ countdown.days }}</span>
                    <span class="text-[var(--color-forest)]/40">T</span>
                    <span class="rounded-lg bg-white px-3 py-2 shadow-sm border border-[var(--color-forest)]/10">{{ pad(countdown.hours) }}</span>
                    <span class="text-[var(--color-forest)]/40">h</span>
                    <span class="rounded-lg bg-white px-3 py-2 shadow-sm border border-[var(--color-forest)]/10">{{ pad(countdown.minutes) }}</span>
                    <span class="text-[var(--color-forest)]/40">m</span>
                    <span class="rounded-lg bg-white px-3 py-2 shadow-sm border border-[var(--color-forest)]/10">{{ pad(countdown.seconds) }}</span>
                    <span class="text-[var(--color-forest)]/40">s</span>
                </div>
                <p v-else-if="endsAt && !isFuture" class="mt-6 text-[var(--color-warm-gray)]">
                    Wir sind gleich wieder da.
                </p>

                <Link
                    href="/"
                    class="mt-8 inline-flex items-center gap-2 rounded-lg bg-[var(--color-forest)] px-5 py-2.5 text-sm font-medium text-white hover:bg-[var(--color-terracotta)] focus:outline-none focus:ring-2 focus:ring-[var(--color-forest)] focus:ring-offset-2"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 0 0 4.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 0 1-15.357-2m15.357 2H15" />
                    </svg>
                    Seite neu laden
                </Link>
            </div>
        </main>

        <footer class="border-t border-[var(--color-forest)]/10 bg-white/50 py-6">
            <div class="mx-auto flex max-w-4xl flex-col items-center gap-4 px-4 sm:flex-row sm:justify-between sm:gap-6">
                <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-sm text-[var(--color-warm-gray)] sm:justify-start">
                    <span>© {{ currentYear }} Luca Themann</span>
                    <span class="hidden sm:inline">·</span>
                    <Link href="/impressum" class="hover:text-[var(--color-terracotta)] focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)] focus:ring-offset-2 rounded">
                        Impressum
                    </Link>
                    <span>·</span>
                    <Link href="/datenschutz" class="hover:text-[var(--color-terracotta)] focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)] focus:ring-offset-2 rounded">
                        Datenschutz
                    </Link>
                    <span>·</span>
                    <Link href="/admin" class="inline-flex items-center gap-1.5 hover:text-[var(--color-terracotta)] focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)] focus:ring-offset-2 rounded">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2zm10-10V7a4 4 0 0 0-8 0v4h8z" />
                        </svg>
                        Admin-Login
                    </Link>
                </div>
                <div class="flex items-center gap-4">
                    <a
                        v-for="link in socialLinks"
                        :key="link.name"
                        :href="link.href"
                        target="_blank"
                        rel="noopener noreferrer"
                        :aria-label="link.name"
                        class="text-[var(--color-forest)]/70 hover:text-[var(--color-terracotta)] focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)] focus:ring-offset-2 rounded-full p-1"
                    >
                        <img
                            v-if="'logoUrl' in link && link.logoUrl"
                            :src="link.logoUrl"
                            :alt="link.name"
                            class="h-5 w-auto max-w-[5rem] object-contain opacity-70 hover:opacity-100 transition-opacity"
                        />
                        <svg v-else class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path v-if="link.name === 'Instagram'" d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                            <path v-else-if="link.name === 'LinkedIn'" d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                        </svg>
                    </a>
                </div>
            </div>
        </footer>
    </div>
</template>

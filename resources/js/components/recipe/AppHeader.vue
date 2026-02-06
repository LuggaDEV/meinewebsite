<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'

const mobileMenuOpen = ref(false)

const navLinks = [
    { label: 'STARTSEITE', to: '/' },
    { label: 'REZEPTE', to: '/#recipes' },
    { label: 'EQUIPMENT', to: '/equipment' },
    { label: 'ÜBER MICH', to: '/#about' },
]

function toggleMobileMenu(): void {
    mobileMenuOpen.value = !mobileMenuOpen.value
}

function closeMobileMenu(): void {
    mobileMenuOpen.value = false
}

function handleResize(): void {
    if (window.innerWidth >= 768) {
        mobileMenuOpen.value = false
    }
}

onMounted(() => {
    window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
    window.removeEventListener('resize', handleResize)
})
</script>

<template>
    <header class="sticky top-0 z-50 w-full bg-[var(--color-cream)]/95 backdrop-blur-sm border-b border-[var(--color-forest)]/10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 md:h-20">
                <Link href="/" class="font-heading text-xl md:text-2xl font-semibold text-[var(--color-forest)] hover:text-[var(--color-terracotta)] transition-colors" @click="closeMobileMenu">
                    Luca Themann
                </Link>

                <nav class="hidden md:flex items-center gap-8">
                    <Link
                        v-for="link in navLinks"
                        :key="link.label"
                        :href="link.to"
                        class="text-sm font-medium uppercase tracking-wider text-[var(--color-forest)]/80 hover:text-[var(--color-terracotta)] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2 rounded"
                    >
                        {{ link.label }}
                    </Link>
                    <Link
                        href="/admin"
                        class="p-2 rounded-lg text-[var(--color-forest)]/80 hover:text-[var(--color-terracotta)] hover:bg-[var(--color-forest)]/5 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2"
                        aria-label="Admin"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </Link>
                </nav>

                <button
                    type="button"
                    class="md:hidden p-2 rounded-lg text-[var(--color-forest)] hover:bg-[var(--color-forest)]/5 focus:outline-none focus:ring-2 focus:ring-[var(--color-terracotta)]"
                    aria-label="Menü öffnen"
                    @click="toggleMobileMenu"
                >
                    <svg v-if="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div
                v-show="mobileMenuOpen"
                class="md:hidden py-4 border-t border-[var(--color-forest)]/10"
            >
                <nav class="flex flex-col gap-2">
                    <Link
                        v-for="link in navLinks"
                        :key="link.label"
                        :href="link.to"
                        class="block py-2 px-3 text-sm font-medium uppercase tracking-wider text-[var(--color-forest)] hover:bg-[var(--color-forest)]/5 rounded-lg transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2"
                        @click="closeMobileMenu"
                    >
                        {{ link.label }}
                    </Link>
                    <Link
                        href="/admin"
                        class="flex items-center gap-3 py-2 px-3 text-sm font-medium text-[var(--color-forest)] hover:bg-[var(--color-forest)]/5 rounded-lg transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2"
                        @click="closeMobileMenu"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <span>Admin</span>
                    </Link>
                </nav>
            </div>
        </div>
    </header>
</template>

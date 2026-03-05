<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import {
    BookOpen,
    ChefHat,
    Settings,
    Star,
    User,
    Wrench,
} from 'lucide-vue-next'
import { useCurrentUrl } from '@/composables/useCurrentUrl'
import { index as adminRecipesIndex } from '@/routes/admin/recipes'
import { index as adminReviewsIndex } from '@/routes/admin/reviews'
import { index as adminEquipmentIndex } from '@/routes/admin/equipment'
import { edit as adminAboutEdit } from '@/routes/admin/about'
import { edit as adminMaintenanceEdit } from '@/routes/admin/maintenance'
import { edit as profileEdit } from '@/routes/profile'

const { currentUrl } = useCurrentUrl()

defineProps<{
    variant?: 'inline' | 'sidebar'
}>()

const emit = defineEmits<{
    navigate: []
}>()

const navLinks = [
    { label: 'Rezepte', href: adminRecipesIndex.url(), icon: BookOpen, match: '/admin/recipes', exactActive: ['/admin'] },
    { label: 'Bewertungen', href: adminReviewsIndex.url(), icon: Star, match: '/admin/reviews' },
    { label: 'Equipment', href: adminEquipmentIndex.url(), icon: ChefHat, match: '/admin/equipment' },
    { label: 'Über Mich', href: adminAboutEdit.url(), icon: User, match: '/admin/about' },
    { label: 'Wartung', href: adminMaintenanceEdit.url(), icon: Wrench, match: '/admin/maintenance' },
    { label: 'Einstellungen', href: profileEdit.url(), icon: Settings, match: '/user/profile' },
] as const

function isActive(link: (typeof navLinks)[number]): boolean {
    const path = currentUrl.value
    if (link.exactActive?.includes(path)) return true
    return path === link.match || path.startsWith(link.match + '/')
}

function onLinkClick(): void {
    emit('navigate')
}
</script>

<template>
    <template v-if="variant === 'sidebar'">
        <ul class="space-y-1">
            <li v-for="link in navLinks" :key="link.label">
                <Link
                    :href="link.href"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-colors"
                    :class="isActive(link)
                        ? 'bg-[var(--color-forest)]/10 text-[var(--color-forest)]'
                        : 'text-[var(--color-warm-gray)] hover:bg-[var(--color-forest)]/5 hover:text-[var(--color-forest)]'"
                    @click="onLinkClick"
                >
                    <component :is="link.icon" class="h-5 w-5 shrink-0" aria-hidden />
                    {{ link.label }}
                </Link>
            </li>
        </ul>
    </template>
    <template v-else>
        <nav class="flex flex-wrap items-center gap-3">
            <Link
                v-for="link in navLinks"
                :key="link.label"
                :href="link.href"
                class="rounded-lg border-2 border-[var(--color-forest)]/20 px-6 py-3 font-medium text-[var(--color-forest)] transition-colors hover:border-[var(--color-terracotta)] hover:bg-[var(--color-terracotta)]/5 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--color-forest)]"
            >
                {{ link.label }}
            </Link>
            <slot name="primary-action" />
        </nav>
    </template>
</template>

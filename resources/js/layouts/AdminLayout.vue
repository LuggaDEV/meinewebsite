<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import RecipeLayout from '@/layouts/RecipeLayout.vue'
import AdminNav from '@/components/admin/AdminNav.vue'
import AdminPageHeader from '@/components/admin/AdminPageHeader.vue'
import {
    Menu,
    X,
} from 'lucide-vue-next'

defineProps<{
    title: string
    subtitle?: string
}>()

const sidebarOpen = ref(false)

function closeSidebar(): void {
    sidebarOpen.value = false
}

function toggleSidebar(): void {
    sidebarOpen.value = !sidebarOpen.value
}

function onResize(): void {
    if (window.innerWidth >= 1024) {
        sidebarOpen.value = false
    }
}

onMounted(() => {
    window.addEventListener('resize', onResize)
})

onUnmounted(() => {
    window.removeEventListener('resize', onResize)
})
</script>

<template>
    <RecipeLayout>
        <div class="min-h-[60vh] flex">
            <!-- Mobile overlay -->
            <div
                v-show="sidebarOpen"
                class="fixed inset-0 z-40 bg-[var(--color-forest)]/20 backdrop-blur-sm lg:hidden"
                aria-hidden="true"
                @click="closeSidebar"
            />

            <!-- Sidebar -->
            <aside
                :class="[
                    'fixed top-0 left-0 z-50 h-full w-72 transform bg-white shadow-xl transition-transform duration-200 ease-out lg:static lg:z-auto lg:translate-x-0 lg:shadow-none',
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                ]"
            >
                <div class="flex h-full flex-col border-r border-[var(--color-forest)]/10">
                    <div class="flex items-center justify-between border-b border-[var(--color-forest)]/10 px-5 py-4">
                        <span class="font-heading text-lg font-semibold text-[var(--color-forest)]">
                            Administration
                        </span>
                        <button
                            type="button"
                            class="rounded-lg p-2 text-[var(--color-forest)] hover:bg-[var(--color-forest)]/5 lg:hidden"
                            aria-label="Menü schließen"
                            @click="closeSidebar"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                    <nav class="flex-1 overflow-y-auto px-3 py-4">
                        <AdminNav variant="sidebar" @navigate="closeSidebar" />
                    </nav>
                    <div v-if="$slots['sidebar-footer']" class="border-t border-[var(--color-forest)]/10 p-3">
                        <slot name="sidebar-footer" />
                    </div>
                </div>
            </aside>

            <!-- Main content -->
            <main class="flex-1 min-w-0">
                <div class="py-8 md:py-12">
                    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                        <!-- Mobile menu button -->
                        <div class="mb-6 flex items-center gap-4 lg:hidden">
                            <button
                                type="button"
                                class="rounded-lg p-2 text-[var(--color-forest)] hover:bg-[var(--color-forest)]/5"
                                aria-label="Menü öffnen"
                                @click="toggleSidebar"
                            >
                                <Menu class="h-6 w-6" />
                            </button>
                        </div>

                        <AdminPageHeader :title="title" :subtitle="subtitle">
                            <template #actions>
                                <slot name="actions" />
                            </template>
                        </AdminPageHeader>

                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </RecipeLayout>
</template>

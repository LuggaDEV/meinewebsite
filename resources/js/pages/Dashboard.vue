<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ExternalLink, Shield } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { index as adminIndex } from '@/routes/admin';
import { dashboard, home } from '@/routes';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <div>
                <h2 class="text-lg font-semibold text-foreground">
                    Willkommen
                </h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    Schnellzugriff auf deine Website und die Verwaltung.
                </p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <Link :href="home()" class="block rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <Card class="h-full transition-shadow hover:shadow-md">
                        <CardHeader>
                            <div class="mb-2 flex items-center gap-2 text-primary">
                                <ExternalLink class="size-5" />
                                <CardTitle class="text-base">Website</CardTitle>
                            </div>
                            <CardDescription>
                                Öffentliche Startseite mit Rezepten ansehen.
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </Link>
                <Link :href="adminIndex()" class="block rounded-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <Card class="h-full transition-shadow hover:shadow-md">
                        <CardHeader>
                            <div class="mb-2 flex items-center gap-2 text-primary">
                                <Shield class="size-5" />
                                <CardTitle class="text-base">
                                    Verwaltung
                                </CardTitle>
                            </div>
                            <CardDescription>
                                Rezepte, Bewertungen und Inhalte bearbeiten.
                            </CardDescription>
                        </CardHeader>
                    </Card>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

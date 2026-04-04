<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { SeoSite } from '@/types'

const props = withDefaults(
    defineProps<{
        title: string
        description?: string
        image?: string | null
        url?: string
        type?: 'website' | 'article'
    }>(),
    {
        description: undefined,
        image: undefined,
        url: undefined,
        type: 'website',
    },
)

const page = usePage()

const site = computed(
    () => page.props.seoSite as SeoSite | undefined,
)

const description = computed(() => props.description ?? site.value?.description ?? '')
const imageUrl = computed(() => props.image ?? site.value?.image ?? '')
const canonicalUrl = computed(() => props.url ?? `${site.value?.baseUrl ?? ''}${page.url}`)
const fullTitle = computed(() =>
    props.title ? `${props.title} - ${site.value?.name ?? ''}` : (site.value?.name ?? ''),
)
</script>

<template>
    <Head :title="title">
        <template v-if="site">
            <link head-key="canonical" rel="canonical" :href="canonicalUrl" />

            <meta head-key="description" name="description" :content="description" />

            <meta head-key="og:title" property="og:title" :content="fullTitle" />
            <meta head-key="og:description" property="og:description" :content="description" />
            <meta head-key="og:type" property="og:type" :content="type" />
            <meta head-key="og:url" property="og:url" :content="canonicalUrl" />
            <meta head-key="og:image" property="og:image" :content="imageUrl" />
            <meta head-key="og:locale" property="og:locale" :content="site.locale" />
            <meta head-key="og:site_name" property="og:site_name" :content="site.name" />

            <meta
                head-key="twitter:card"
                name="twitter:card"
                content="summary_large_image"
            />
            <meta head-key="twitter:title" name="twitter:title" :content="fullTitle" />
            <meta
                head-key="twitter:description"
                name="twitter:description"
                :content="description"
            />
            <meta head-key="twitter:image" name="twitter:image" :content="imageUrl" />
        </template>
    </Head>
</template>

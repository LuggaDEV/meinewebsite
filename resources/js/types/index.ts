export * from './auth';
export * from './navigation';
export * from './ui';

import type { Auth } from './auth';

export type SeoSite = {
    name: string;
    baseUrl: string;
    description: string;
    image: string;
    favicon: string;
    faviconIco: string;
    locale: string;
};

export type SeoPageMeta = {
    title: string;
    description: string;
    image?: string | null;
    url: string;
    type?: 'website' | 'article';
};

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    auth: Auth;
    sidebarOpen: boolean;
    seoSite: SeoSite;
    [key: string]: unknown;
};

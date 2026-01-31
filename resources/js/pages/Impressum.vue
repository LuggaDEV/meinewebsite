<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { Motion } from 'motion-v'
import RecipeLayout from '@/layouts/RecipeLayout.vue'
</script>

<template>
    <RecipeLayout>
        <div class="py-12 md:py-16">
            <Motion
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :transition="{ duration: 0.4 }"
                class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8"
            >
                <Motion
                    :initial="{ opacity: 0, x: -20 }"
                    :animate="{ opacity: 1, x: 0 }"
                    :transition="{ duration: 0.4, delay: 0.1 }"
                >
                    <Link
                        href="/"
                        class="mb-8 inline-flex items-center gap-2 text-[var(--color-forest)] hover:text-[var(--color-terracotta)] transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-terracotta)] focus-visible:ring-offset-2 rounded"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Zurück zur Startseite
                    </Link>
                </Motion>

                <Motion
                    :initial="{ opacity: 0, y: 20 }"
                    :animate="{ opacity: 1, y: 0 }"
                    :transition="{ duration: 0.5, delay: 0.2 }"
                >
                    <article>
                        <h1 class="font-heading text-3xl md:text-4xl lg:text-5xl font-semibold text-[var(--color-forest)] mb-8">
                            Impressum
                        </h1>

                        <div class="prose prose-lg max-w-none text-[var(--color-warm-gray)] space-y-6">
                            <section>
                                <h2 class="font-heading text-2xl md:text-3xl font-semibold text-[var(--color-forest)] mb-4">
                                    Angaben gemäß § 5 TMG
                                </h2>
                                <p>
                                    Luca Themann<br />
                                    Gewerbegebiet 5<br />
                                    25946 Nebel
                                </p>
                            </section>

                            <section>
                                <h2 class="font-heading text-2xl md:text-3xl font-semibold text-[var(--color-forest)] mb-4">
                                    Kontakt
                                </h2>
                                <p>
                                    E-Mail: info@lthemann.de
                                </p>
                            </section>

                            <section>
                                <h2 class="font-heading text-2xl md:text-3xl font-semibold text-[var(--color-forest)] mb-4">
                                    Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV
                                </h2>
                                <p>
                                    Luca Themann<br />
                                    Gewerbegebiet 5<br />
                                    25946 Nebel
                                </p>
                            </section>

                            <section>
                                <h2 class="font-heading text-2xl md:text-3xl font-semibold text-[var(--color-forest)] mb-4">
                                    Haftungsausschluss
                                </h2>
                                
                                <h3 class="font-heading text-xl font-semibold text-[var(--color-forest)] mt-6 mb-3">
                                    Haftung für Inhalte
                                </h3>
                                <p>
                                    Die Inhalte unserer Seiten wurden mit größter Sorgfalt erstellt. Für die Richtigkeit, Vollständigkeit und Aktualität der Inhalte können wir jedoch keine Gewähr übernehmen. Als Diensteanbieter sind wir gemäß § 7 Abs.1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich.
                                </p>

                                <h3 class="font-heading text-xl font-semibold text-[var(--color-forest)] mt-6 mb-3">
                                    Haftung für Links
                                </h3>
                                <p>
                                    Unser Angebot enthält Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich.
                                </p>

                                <h3 class="font-heading text-xl font-semibold text-[var(--color-forest)] mt-6 mb-3">
                                    Urheberrecht
                                </h3>
                                <p>
                                    Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers.
                                </p>
                            </section>
                        </div>
                    </article>
                </Motion>
            </Motion>
        </div>
    </RecipeLayout>
</template>

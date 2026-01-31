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
                            Datenschutzerklärung
                        </h1>

                        <div class="prose prose-lg max-w-none text-[var(--color-warm-gray)] space-y-6">
                            <section>
                                <h2 class="font-heading text-2xl md:text-3xl font-semibold text-[var(--color-forest)] mb-4">
                                    1. Datenschutz auf einen Blick
                                </h2>
                                
                                <h3 class="font-heading text-xl font-semibold text-[var(--color-forest)] mt-6 mb-3">
                                    Allgemeine Hinweise
                                </h3>
                                <p>
                                    Die folgenden Hinweise geben einen einfachen Überblick darüber, was mit Ihren personenbezogenen Daten passiert, wenn Sie diese Website besuchen. Personenbezogene Daten sind alle Daten, mit denen Sie persönlich identifiziert werden können.
                                </p>

                                <h3 class="font-heading text-xl font-semibold text-[var(--color-forest)] mt-6 mb-3">
                                    Datenerfassung auf dieser Website
                                </h3>
                                <p>
                                    <strong>Wer ist verantwortlich für die Datenerfassung auf dieser Website?</strong><br />
                                    Die Datenverarbeitung auf dieser Website erfolgt durch den Websitebetreiber. Dessen Kontaktdaten können Sie dem Abschnitt „Hinweis zur Verantwortlichen Stelle" in dieser Datenschutzerklärung entnehmen.
                                </p>
                                <p>
                                    <strong>Wie erfassen wir Ihre Daten?</strong><br />
                                    Ihre Daten werden zum einen dadurch erhoben, dass Sie uns diese mitteilen. Hierbei kann es sich z. B. um Daten handeln, die Sie in ein Kontaktformular eingeben.
                                </p>
                                <p>
                                    <strong>Wofür nutzen wir Ihre Daten?</strong><br />
                                    Ein Teil der Daten wird erhoben, um eine fehlerfreie Bereitstellung der Website zu gewährleisten. Andere Daten können zur Analyse Ihres Nutzerverhaltens verwendet werden.
                                </p>
                            </section>

                            <section>
                                <h2 class="font-heading text-2xl md:text-3xl font-semibold text-[var(--color-forest)] mb-4">
                                    2. Hosting
                                </h2>
                                <p>
                                    Diese Website wird bei [Ihr Hosting-Anbieter] gehostet. Anbieter ist [Name und Kontaktdaten des Hosting-Anbieters]. Wenn Sie diese Website besuchen, erfasst der Hoster automatisch verschiedene Logfiles inklusive Ihrer IP-Adressen.
                                </p>
                            </section>

                            <section>
                                <h2 class="font-heading text-2xl md:text-3xl font-semibold text-[var(--color-forest)] mb-4">
                                    3. Allgemeine Hinweise und Pflichtinformationen
                                </h2>
                                
                                <h3 class="font-heading text-xl font-semibold text-[var(--color-forest)] mt-6 mb-3">
                                    Datenschutz
                                </h3>
                                <p>
                                    Die Betreiber dieser Seiten nehmen den Schutz Ihrer persönlichen Daten sehr ernst. Wir behandeln Ihre personenbezogenen Daten vertraulich und entsprechend den gesetzlichen Datenschutzbestimmungen sowie dieser Datenschutzerklärung.
                                </p>

                                <h3 class="font-heading text-xl font-semibold text-[var(--color-forest)] mt-6 mb-3">
                                    Hinweis zur verantwortlichen Stelle
                                </h3>
                                <p>
                                    Die verantwortliche Stelle für die Datenverarbeitung auf dieser Website ist:
                                </p>
                                <p>
                                    Luca Themann<br />
                                    Gewerbegebiet 5<br />
                                    25946 Nebel
                                </p>
                                <p>
                                    Telefon: +49 151 22192136<br />
                                    E-Mail: info@lthemann.de
                                </p>
                                <p>
                                    Verantwortliche Stelle ist die natürliche oder juristische Person, die allein oder gemeinsam mit anderen über die Zwecke und Mittel der Verarbeitung von personenbezogenen Daten (z. B. Namen, E-Mail-Adressen o. Ä.) entscheidet.
                                </p>

                                <h3 class="font-heading text-xl font-semibold text-[var(--color-forest)] mt-6 mb-3">
                                    Speicherdauer
                                </h3>
                                <p>
                                    Soweit innerhalb dieser Datenschutzerklärung keine speziellere Speicherdauer genannt wurde, verbleiben Ihre personenbezogenen Daten bei uns, bis der Zweck für die Datenverarbeitung entfällt. Wenn Sie ein berechtigtes Löschersuchen geltend machen oder eine Einwilligung zur Datenverarbeitung widerrufen, werden Ihre Daten gelöscht, sofern wir keine anderen rechtlich zulässigen Gründe für die Speicherung Ihrer personenbezogenen Daten haben.
                                </p>

                                <h3 class="font-heading text-xl font-semibold text-[var(--color-forest)] mt-6 mb-3">
                                    Widerruf Ihrer Einwilligung zur Datenverarbeitung
                                </h3>
                                <p>
                                    Viele Datenverarbeitungsvorgänge sind nur mit Ihrer ausdrücklichen Einwilligung möglich. Sie können eine bereits erteilte Einwilligung jederzeit widerrufen. Die Rechtmäßigkeit der bis zum Widerruf erfolgten Datenverarbeitung bleibt vom Widerruf unberührt.
                                </p>

                                <h3 class="font-heading text-xl font-semibold text-[var(--color-forest)] mt-6 mb-3">
                                    Widerspruchsrecht gegen die Datenerhebung in besonderen Fällen
                                </h3>
                                <p>
                                    WENN DIE DATENVERARBEITUNG AUF GRUNDLAGE VON ART. 6 ABS. 1 LIT. E ODER F DSGVO ERFOLGT, HABEN SIE JEDERZEIT DAS RECHT, AUS GRÜNDEN, DIE SICH AUS IHRER BESONDEREN SITUATION ERGEBEN, GEGEN DIE VERARBEITUNG IHRER PERSONENBEZOGENEN DATEN WIDERSPRUCH EINZULEGEN.
                                </p>
                            </section>

                            <section>
                                <h2 class="font-heading text-2xl md:text-3xl font-semibold text-[var(--color-forest)] mb-4">
                                    4. Datenerfassung auf dieser Website
                                </h2>
                                
                                <h3 class="font-heading text-xl font-semibold text-[var(--color-forest)] mt-6 mb-3">
                                    Kontaktformular
                                </h3>
                                <p>
                                    Wenn Sie uns per Kontaktformular Anfragen zukommen lassen, werden Ihre Angaben aus dem Anfrageformular inklusive der von Ihnen dort angegebenen Kontaktdaten zwecks Bearbeitung der Anfrage und für den Fall von Anschlussfragen bei uns gespeichert.
                                </p>

                                <h3 class="font-heading text-xl font-semibold text-[var(--color-forest)] mt-6 mb-3">
                                    Server-Log-Dateien
                                </h3>
                                <p>
                                    Der Provider der Seiten erhebt und speichert automatisch Informationen in so genannten Server-Log-Dateien, die Ihr Browser automatisch an uns übermittelt. Dies sind:
                                </p>
                                <ul class="list-disc list-inside space-y-2 ml-4">
                                    <li>Browsertyp und Browserversion</li>
                                    <li>verwendetes Betriebssystem</li>
                                    <li>Referrer URL</li>
                                    <li>Hostname des zugreifenden Rechners</li>
                                    <li>Uhrzeit der Serveranfrage</li>
                                    <li>IP-Adresse</li>
                                </ul>
                            </section>

                            <section>
                                <h2 class="font-heading text-2xl md:text-3xl font-semibold text-[var(--color-forest)] mb-4">
                                    5. Ihre Rechte
                                </h2>
                                <p>
                                    Sie haben jederzeit das Recht, unentgeltlich Auskunft über Herkunft, Empfänger und Zweck Ihrer gespeicherten personenbezogenen Daten zu erhalten. Sie haben außerdem ein Recht, die Berichtigung oder Löschung dieser Daten zu verlangen. Wenn Sie eine Einwilligung zur Datenverarbeitung erteilt haben, können Sie diese Einwilligung jederzeit für die Zukunft widerrufen. Außerdem haben Sie das Recht, unter bestimmten Umständen die Einschränkung der Verarbeitung Ihrer personenbezogenen Daten zu verlangen. Des Weiteren steht Ihnen ein Beschwerderecht bei der zuständigen Aufsichtsbehörde zu.
                                </p>
                            </section>
                        </div>
                    </article>
                </Motion>
            </Motion>
        </div>
    </RecipeLayout>
</template>

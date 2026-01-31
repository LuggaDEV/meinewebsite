<script setup>
import { Motion } from 'motion-v'

// Bild-URL - kann später durch ein Upload oder eine Prop ersetzt werden
const profileImage = null // Platzhalter - Bild wird später eingefügt
</script>

<template>
  <section id="about" class="py-16 md:py-24 bg-white/50">
    <Motion
      :initial="{ opacity: 0, y: 30 }"
      :in-view="{ opacity: 1, y: 0 }"
      :transition="{ duration: 0.6, ease: 'easeOut' }"
      :viewport="{ once: true, margin: '-100px' }"
      class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8"
    >
      <h2 class="font-heading text-3xl md:text-4xl font-semibold text-[var(--color-forest)] mb-10 text-center">
        Über mich
      </h2>
      
      <div class="flex flex-col md:flex-row gap-8 md:gap-12 items-start">
        <!-- Bild -->
        <Motion
          :initial="{ opacity: 0, scale: 0.95 }"
          :in-view="{ opacity: 1, scale: 1 }"
          :transition="{ duration: 0.6, delay: 0.2, ease: 'easeOut' }"
          :viewport="{ once: true }"
          class="flex-shrink-0 w-full md:w-64 lg:w-80"
        >
          <div class="aspect-square overflow-hidden rounded-xl shadow-lg border-4 border-[var(--color-forest)]/10 bg-gradient-to-br from-[var(--color-cream)] to-[var(--color-forest)]/10 flex items-center justify-center">
            <div v-if="profileImage" class="w-full h-full">
              <img
                :src="profileImage"
                alt="Luca Themann"
                class="w-full h-full object-cover"
              />
            </div>
            <div v-else class="text-center p-8">
              <svg class="w-24 h-24 mx-auto text-[var(--color-forest)]/30 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
              <p class="text-sm text-[var(--color-warm-gray)] font-medium">
                Bild wird<br />später eingefügt
              </p>
            </div>
          </div>
        </Motion>
        
        <!-- Text -->
        <div class="flex-1 prose prose-lg text-[var(--color-warm-gray)] space-y-6">
        <p>
          Schon als kleiner Junge wollte ich Koch werden. Ich habe immer gern gekocht und mit Zutaten experimentiert – ob im Garten meiner Mutter oder beim Kochen der Signature-Pasta meines Vaters.
        </p>
        <p>
          Früh habe ich in kleinen Küchen gearbeitet, und mit fünfzehn habe ich mein erstes Kochvideo gedreht. Im selben Jahr kam ich in ein Sterne-Restaurant: Oud Sluis, damals eines der besten Restaurants der Niederlande, mit drei Michelin-Sternen. Ein Kurzpraktikum für die Schule – aber von dem Tag an wusste ich: Kochen wird mein Leben!
        </p>
        <p>
          Seither habe ich in verschiedenen Spitzenrestaurants gearbeitet und viel gelernt. Diese Erfahrung möchte ich jetzt mit euch teilen.
        </p>
        <p>
          Ich liebe feine Patisserie und klassische französische Techniken. Dementsprechend teile ich Gerichte aus der Haute Cuisine und andere Kreationen.
        </p>
        <p>
          Hoffentlich hilft euch das für jetzt und die Zukunft!
        </p>
        </div>
      </div>
    </Motion>
  </section>
</template>

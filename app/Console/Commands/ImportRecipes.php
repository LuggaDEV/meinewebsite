<?php

namespace App\Console\Commands;

use App\Models\Recipe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportRecipes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:import {file : Path to recipes.json file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import recipes from JSON file (with Base64 image support)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return Command::FAILURE;
        }

        $json = file_get_contents($filePath);
        $recipes = json_decode($json, true);

        if (!is_array($recipes)) {
            $this->error('Invalid JSON format');
            return Command::FAILURE;
        }

        $this->info("Found " . count($recipes) . " recipes to import");

        $imported = 0;
        $skipped = 0;

        foreach ($recipes as $recipeData) {
            // Prüfe ob Rezept bereits existiert
            if (Recipe::where('id', $recipeData['id'] ?? null)->exists()) {
                $this->warn("Recipe ID {$recipeData['id']} already exists, skipping...");
                $skipped++;
                continue;
            }

            $imagePath = null;

            // Verarbeite Bild (Base64 oder URL)
            if (!empty($recipeData['image'])) {
                $image = $recipeData['image'];

                // Prüfe ob Base64 Data URL
                if (preg_match('/^data:image\/(\w+);base64,(.+)$/', $image, $matches)) {
                    $extension = $matches[1];
                    $base64Data = $matches[2];
                    $imageData = base64_decode($base64Data);

                    if ($imageData !== false) {
                        $filename = 'recipe-' . ($recipeData['id'] ?? Str::random(10)) . '-' . time() . '.' . $extension;
                        $imagePath = Storage::disk('public')->put('recipes/' . $filename, $imageData);
                    }
                } elseif (filter_var($image, FILTER_VALIDATE_URL)) {
                    // Externe URL - speichere als URL
                    $imagePath = $image;
                } elseif (str_starts_with($image, '/')) {
                    // Relativer Pfad - behalte bei
                    $imagePath = $image;
                }
            }

            // Konvertiere ingredients und instructions zu Arrays falls Strings
            $ingredients = is_array($recipeData['ingredients'] ?? null)
                ? $recipeData['ingredients']
                : (!empty($recipeData['ingredients']) ? [$recipeData['ingredients']] : []);

            $instructions = is_array($recipeData['instructions'] ?? null)
                ? $recipeData['instructions']
                : (!empty($recipeData['instructions']) ? explode("\n", $recipeData['instructions']) : []);

            // Filtere leere Einträge
            $ingredients = array_filter($ingredients, fn($ing) => !empty(trim($ing)));
            $instructions = array_filter($instructions, fn($inst) => !empty(trim($inst)));

            try {
                Recipe::create([
                    'id' => $recipeData['id'] ?? null,
                    'title' => $recipeData['title'] ?? 'Unbenanntes Rezept',
                    'description' => $recipeData['description'] ?? '',
                    'image' => $imagePath,
                    'servings' => $recipeData['servings'] ?? null,
                    'prep_time' => $recipeData['prepTime'] ?? $recipeData['prep_time'] ?? null,
                    'ingredients' => array_values($ingredients),
                    'instructions' => array_values($instructions),
                ]);

                $imported++;
                $this->info("Imported: {$recipeData['title']}");
            } catch (\Exception $e) {
                $this->error("Failed to import {$recipeData['title']}: " . $e->getMessage());
            }
        }

        $this->info("\nImport completed!");
        $this->info("Imported: {$imported}");
        $this->info("Skipped: {$skipped}");

        return Command::SUCCESS;
    }
}

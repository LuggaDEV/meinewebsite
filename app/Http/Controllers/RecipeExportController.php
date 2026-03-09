<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class RecipeExportController extends Controller
{
    /**
     * Export the recipe as schema.org Recipe JSON-LD for cookbook app import.
     */
    public function json(Recipe $recipe): JsonResponse
    {
        $imageUrl = $recipe->image
            ? (str_starts_with($recipe->image, 'http') ? $recipe->image : asset('storage/'.$recipe->image))
            : null;

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Recipe',
            'name' => $recipe->title,
            'description' => $recipe->description,
        ];

        if ($imageUrl !== null) {
            $schema['image'] = $imageUrl;
        }

        if ($recipe->servings !== null) {
            $schema['recipeYield'] = (string) $recipe->servings;
        }

        if ($recipe->prep_time !== null && $recipe->prep_time > 0) {
            $schema['prepTime'] = $this->minutesToIso8601Duration((int) $recipe->prep_time);
        }

        if ($recipe->cook_time !== null && $recipe->cook_time > 0) {
            $schema['cookTime'] = $this->minutesToIso8601Duration((int) $recipe->cook_time);
        }

        if ($recipe->rest_time !== null && $recipe->rest_time > 0) {
            $schema['restTime'] = $this->minutesToIso8601Duration((int) $recipe->rest_time);
        }

        $schema['recipeIngredient'] = $recipe->ingredients ?? [];

        $schema['recipeInstructions'] = [];
        foreach ($recipe->instructions ?? [] as $step) {
            $schema['recipeInstructions'][] = [
                '@type' => 'HowToStep',
                'text' => $step,
            ];
        }

        $filename = Str::slug($recipe->title).'-rezept.json';

        return response()->json($schema, 200, [
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Convert minutes to ISO 8601 duration (e.g. PT30M, PT1H30M).
     */
    private function minutesToIso8601Duration(int $minutes): string
    {
        if ($minutes < 60) {
            return 'PT'.$minutes.'M';
        }

        $hours = (int) floor($minutes / 60);
        $mins = $minutes % 60;

        $duration = 'PT'.$hours.'H';
        if ($mins > 0) {
            $duration .= $mins.'M';
        }

        return $duration;
    }
}

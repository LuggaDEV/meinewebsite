export interface RecipeReview {
  id: number
  rating: number
  body: string | null
  author_name?: string | null
  created_at: string
  reply?: string | null
  replied_at?: string | null
  user: { id: number | null; name: string }
}

export interface Recipe {
  id: number
  title: string
  description: string
  image: string | null
  servings: number | null
  prep_time: number | null
  cook_time: number | null
  rest_time: number | null
  ingredients: string[]
  instructions: string[]
  average_rating?: number
  reviews_count?: number
  reviews?: RecipeReview[]
  user_review?: RecipeReview | null
}

export interface ActiveFilters {
  prepTime: string[]
  cookTime: string[]
  servings: string[]
}

export interface FilterOption {
  value: string
  label: string
}

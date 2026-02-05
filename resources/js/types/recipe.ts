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

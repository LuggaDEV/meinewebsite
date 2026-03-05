export interface Equipment {
    id: number
    name: string
    description: string | null
    image: string | null
    link: string
    category: string
    price: string | null
    original_price: string | null
    discount_percentage: string | null
    last_price_checked_at: string | null
    created_at: string
    updated_at: string
}

export interface ActiveFilters {
    categories: string[]
}

export interface Equipment {
    id: number
    name: string
    description: string | null
    image: string | null
    link: string
    category: string
    created_at: string
    updated_at: string
}

export interface ActiveFilters {
    categories: string[]
}

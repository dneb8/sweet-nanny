// types/Child.ts
export interface Child {
  id?: string           
  ulid?: string         
  tutor_id?: string
  name?: string
  birthdate: string
  kinkship?: string
  created_at: string
  updated_at: string
  deleted_at?: string | null
}

// Modelo de formulario (crear/editar)
export interface ChildInput {
  id?: string            
  ulid?: string          
  tutor_id: string
  name: string
  birthdate: string
  kinkship: string
}

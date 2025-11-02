// resources/js/types/inertia.d.ts
import { User } from '@/types/User'

declare module '@inertiajs/core' {
  export interface PageProps {
    auth: {
      user: User
      roles: string[]
      permisos: string[]
    } | null
    flash: {
      message?: string
      notification?: {
        type: string
        message: string
      }
    }
    name?: string
    quote?: string
    ziggy?: unknown
    sidebarOpen?: boolean
    [key: string]: any
  }
}

export type { PageProps } from '@inertiajs/core'
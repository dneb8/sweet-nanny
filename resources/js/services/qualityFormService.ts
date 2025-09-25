import axios from 'axios'
import { ref, Ref, watch } from 'vue'
import type { Nanny } from '@/types/Nanny'

export class QualityFormService {
  public nanny?: Ref<Nanny>
  public values: { qualities: string[] }
  public loading = ref(false)
  public saved = ref(false)

  constructor(nanny?: Nanny) {
    this.nanny = nanny ? ref(JSON.parse(JSON.stringify(nanny))) : undefined
    this.values = { qualities: nanny?.qualities?.map(q => q.name) ?? [] }

    // Auto-save: SOLO enviar `values.qualities` al backend
    watch(
      () => this.values.qualities,
      async (newVal) => {
        if (!this.nanny?.value?.id) return
        this.loading.value = true
        this.saved.value = false

        try {
          await axios.post(`/nannies/${this.nanny.value.id}/async-qualities`, {
            qualities: newVal
          })
          // No modificamos nanny.value.qualities localmente
          this.saved.value = true
          setTimeout(() => (this.saved.value = false), 2000)
        } catch (error) {
          console.error('Error guardando cualidades:', error)
        } finally {
          this.loading.value = false
        }
      },
      { deep: true }
    )
  }
}

import { ref, Ref } from "vue"
import axios from "axios"
import { route } from "ziggy-js"
import { toast } from "vue-sonner" 

export class QualityFormService {
  public nannyId: string
  public qualities: Ref<string[]>
  public loading = ref(false)
  public errores = ref<Record<string, string[]>>({})

  constructor(nannyId: string | number, selectedQualities: string[]) {
    this.nannyId = String(nannyId) 
    this.qualities = ref([...selectedQualities])
  }

  // Agregar cualidad
  async addQuality(name: string) {
    if (!this.qualities.value.includes(name)) {
      this.qualities.value.push(name)
      await this.guardar(`Cualidad "${name}" agregada correctamente`, true)
    }
  }

  // Eliminar cualidad
  async removeQuality(name: string) {
    this.qualities.value = this.qualities.value.filter(q => q !== name)
    await this.guardar(`Cualidad "${name}" eliminada correctamente`, true)
  }

  //  Guardar cambios en backend
  private async guardar(message?: string, successToast = false) {
    this.loading.value = true
    this.errores.value = {}

    try {
      const res = await axios.patch(
        route("nannies.update.qualities", this.nannyId),
        { qualities: this.qualities.value },
        { headers: { Accept: "application/json" } }
      )

      // Actualizar con la respuesta real del backend
      this.qualities.value = res.data.qualities.map((q: any) => q.name)

      // Mostrar toast con estilo de badge
      if (message && successToast) {
        toast.success(message, {
          style: {
            background: "#E9D5FF", 
            color: "#6B21A8",      
            fontWeight: "500",
          }
        })
      }

      return true
    } catch (err: any) {
      if (err?.response?.data?.errors) {
        this.errores.value = err.response.data.errors
      }

      // Toast de error
      toast.error("No se pudieron guardar los cambios.", {
        style: {
          background: "#FECACA", 
          color: "#991B1B",      
          fontWeight: "500",
        }
      })

      return false
    } finally {
      this.loading.value = false
    }
  }
}

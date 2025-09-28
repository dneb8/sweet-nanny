import { ref, Ref } from "vue"
import axios from "axios"
import { route } from "ziggy-js"
import * as z from "zod"
import { toTypedSchema } from "@vee-validate/zod"
import { useForm } from "vee-validate"
import type { Child } from "@/types/Child"

export class ChildFormService {
  public child: Ref<Child>

  public formSchema
  public values
  public isFieldDirty

  public loading = ref(false)
  public saved   = ref(false)
  public errores = ref<Record<string, string[]>>({})

  // nuestra API: sin evento, siempre Promise<boolean>
  public guardar: () => Promise<boolean>

  // ✅ headers por-request para forzar JSON en errores también
  private cfg = {
    headers: {
      Accept: "application/json",
      "X-Requested-With": "XMLHttpRequest",
    },
  }

  constructor(child: Child) {
    // clone plano para no mutar props (y evitar proxies/relaciones)
    this.child = ref<Child>(JSON.parse(JSON.stringify(child)))

    this.formSchema = toTypedSchema(
      z.object({
        tutor_id: z.string(),
        name: z.string().min(2, "Nombre requerido").max(100),
        birthdate: z.string().min(10, "Fecha requerida"), // YYYY-MM-DD
        kinkship: z.string().min(2, "Parentesco requerido"),
      })
    )

    const { values, isFieldDirty, handleSubmit } = useForm({
      validationSchema: this.formSchema,
      initialValues: {
        tutor_id : this.child.value.tutor_id,
        name     : this.child.value.name ?? "",
        birthdate: this.child.value.birthdate ?? "",
        kinkship : this.child.value.kinkship ?? "",
      },
    })

    this.values = values
    this.isFieldDirty = isFieldDirty

    // 1) armamos la función de envío Vee-Validate
    const submit = handleSubmit(async (payload) => {
      this.loading.value = true
      this.errores.value = {}

      const isUpdate = !!this.child.value.id
      const url = isUpdate
        ? route("children.update", this.child.value.id as any)
        : route("children.store")

      console.groupCollapsed(
        `%c[ChildFormService] ${isUpdate ? "PATCH" : "POST"} ${url}`,
        "color:#6b7280;font-weight:bold;"
      )
      console.log("payload ⇒", payload)
      console.log("child   ⇒", this.child.value)
      console.groupEnd()

      try {
        const res = isUpdate
          ? await axios.patch(url, payload, this.cfg)   
          : await axios.post(url, payload, this.cfg)  

        console.groupCollapsed(
          `%c[ChildFormService] ✓ ${isUpdate ? "PATCH" : "POST"} ${url} (status ${res.status})`,
          "color:#16a34a;font-weight:bold;"
        )
        console.log("response.data ⇒", res.data)
        console.groupEnd()

        this.child.value = res.data
        this.saved.value = true
        setTimeout(() => (this.saved.value = false), 3500)

        return true
      } catch (err: any) {
        console.groupCollapsed(
          `%c[ChildFormService] ✗ ${isUpdate ? "PATCH" : "POST"} ${url}`,
          "color:#dc2626;font-weight:bold;"
        )
        if (err?.response) {
          console.error("status ⇒", err.response.status)
          console.error("data   ⇒", err.response.data)
          console.error("errors ⇒", err.response.data?.errors)
        } else if (err?.request) {
          console.error("No hubo respuesta del servidor")
          console.error("request ⇒", err.request)
        } else {
          console.error("Error al preparar la petición ⇒", err?.message)
        }
        console.groupEnd()

        if (err?.response?.data?.errors) {
          this.errores.value = err.response.data.errors
        }
        return false
      } finally {
        setTimeout(() => (this.loading.value = false), 300)
      }
    })
    this.guardar = async (): Promise<boolean> => {
      try {
        const res = await submit(undefined as any) // normalizamos retorno
        return res === true
      } catch {
        return false
      }
    }
  }

  async eliminar(): Promise<void> {
    if (!this.child.value.id) return
    this.loading.value = true
    this.errores.value = {}
    try {
      await axios.delete(route("children.destroy", this.child.value.id as any), this.cfg) 
    } catch (err: any) {
      if (err?.response?.data?.errors) {
        this.errores.value = err.response.data.errors
      }
    } finally {
      this.loading.value = false
    }
  }
}

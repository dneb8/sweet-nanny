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

  public guardar: () => Promise<boolean>

  private cfg = {
    headers: {
      Accept: "application/json",
      "X-Requested-With": "XMLHttpRequest",
    },
  }

  constructor(child: Child) {
    this.child = ref<Child>(JSON.parse(JSON.stringify(child)))

    this.formSchema = toTypedSchema(
      z.object({
        tutor_id: z.string(),
        name: z.string().min(2, "Nombre requerido").max(100),
        birthdate: z.string().min(10, "Fecha requerida"),
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

    const submit = handleSubmit(async (payload) => {
      this.loading.value = true
      this.errores.value = {}

      // Usa ULID para rutas (fallback a id numérico si no hay ulid)
      const routeKey = (this.child.value as Child).ulid ?? this.child.value.id
      const isUpdate = !!routeKey

      const url = isUpdate
        ? route("children.update", { child: routeKey as any })
        : route("children.store")

      try {
        const res = isUpdate
          ? await axios.patch(url, payload, this.cfg)
          : await axios.post(url, payload, this.cfg)

        this.child.value = res.data
        this.saved.value = true
        setTimeout(() => (this.saved.value = false), 3500)
        return true
      } catch (err: any) {
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
        const res = await submit(undefined as any)
        return res === true
      } catch {
        return false
      }
    }
  }

  async eliminar(): Promise<void> {
    // Usa ULID para delete (fallback a id)
    const routeKey = (this.child.value as any).ulid ?? this.child.value.id
    if (!routeKey) return

    this.loading.value = true
    this.errores.value = {}
    try {
      await axios.delete(route("children.destroy", { child: routeKey as any }), this.cfg)
    } catch (err: any) {
      if (err?.response?.data?.errors) {
        this.errores.value = err.response.data.errors
      }
    } finally {
      this.loading.value = false
    }
  }
}

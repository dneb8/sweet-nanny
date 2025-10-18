import { ref, Ref } from "vue"
import axios from "axios"
import { route } from "ziggy-js"
import * as z from "zod"
import { toTypedSchema } from "@vee-validate/zod"
import { useForm } from "vee-validate"
import type { Address } from "@/types/Address"

export class AddressFormService {
  public address: Ref<Address>

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

  constructor(address: Address, private ownerId?: number, private ownerType?: string) {
    this.address = ref<Address>(JSON.parse(JSON.stringify(address)))

    this.formSchema = toTypedSchema(
      z.object({
        postal_code: z.string().min(4, "Código postal requerido"),
        street: z.string().min(2, "Calle requerida"),
        neighborhood: z.string().min(2, "Colonia requerida"),
        type: z.string().min(2, "Tipo requerido"),
        other_type: z.string().optional(),
        internal_number: z.string().optional(),
      })
    )

    const { values, isFieldDirty, handleSubmit } = useForm({
      validationSchema: this.formSchema,
      initialValues: {
        postal_code: this.address.value.postal_code ?? "",
        street: this.address.value.street ?? "",
        neighborhood: this.address.value.neighborhood ?? "",
        type: this.address.value.type ?? "",
        other_type: this.address.value.other_type ?? "",
        internal_number: this.address.value.internal_number ?? "",
      },
    })

    this.values = values
    this.isFieldDirty = isFieldDirty

    const submit = handleSubmit(async (payload) => {
      this.loading.value = true
      this.errores.value = {}

      const isUpdate = !!this.address.value.id

      // For create, include owner info
      const dataToSend = isUpdate 
        ? payload 
        : {
            ...payload,
            owner_type: this.ownerType,
            owner_id: this.ownerId,
          }

      const url = isUpdate
        ? route("addresses.update", { address: this.address.value.id as any })
        : route("addresses.store")

      try {
        const res = isUpdate
          ? await axios.patch(url, dataToSend, this.cfg)
          : await axios.post(url, dataToSend, this.cfg)

        this.address.value = res.data.address ?? res.data
        this.saved.value = true
        setTimeout(() => (this.saved.value = false), 3500)
        return true
      } catch (err: any) {
        if (err?.response?.data?.errors) {
          this.errores.value = err.response.data.errors
        }
        return false
      } finally {
        this.loading.value = false
      }
    })

    this.guardar = () => submit()
  }
}

import { ref, type Ref } from "vue"
import { toTypedSchema } from "@vee-validate/zod"
import { useForm } from "vee-validate"
import { useForm as useInertiaForm, usePage } from "@inertiajs/vue3"
import { route } from "ziggy-js"
import * as z from "zod"
import type { Address } from "@/types/Address"

export type Owner = {
  ownerId: number | string
  ownerType: string // FQCN: "App\\Models\\Tutor" | "App\\Models\\Nanny" | "App\\Models\\Booking"
}

type Ctor = {
  address?: Address
  ownerId: number
  ownerType: string // FQCN: "App\\Models\\Tutor" | "App\\Models\\Nanny" | "App\\Models\\Booking"
}

export class AddressFormService {
  public address?: Ref<Address>
  public formSchema
  public values
  public isFieldDirty
  public setFieldValue: (field: string, value: any) => void

  public loading = ref<boolean>(false)
  public saved = ref<boolean>(false)
  public errors = ref<Record<string, string[]>>({})
  public savedAddress = ref<Address | null>(null)

  public saveAddress: (e?: Event) => void
  public updateAddress: (e?: Event) => void

  constructor({ address, ownerId, ownerType }: Ctor) {
    if (address) {
      // clonar para no mutar prop
      this.address = ref<Address>(JSON.parse(JSON.stringify(address)))
    }

    // ✅ Validación: incluye addressable_* requeridos
    this.formSchema = toTypedSchema(
      z.object({
        postal_code: z.string().nonempty("El código postal es obligatorio").length(5, "El código postal debe tener 5 dígitos").regex(/^\d{5}$/, "El código postal debe ser numérico"),
        street: z.string().nonempty("La calle es obligatoria").max(255),
        name: z.string().nonempty("El nombre de la dirección es obligatorio").max(80),
        neighborhood: z.string().nonempty("La colonia es obligatoria").max(255),
        external_number: z.string().nonempty("El número exterior es obligatorio").max(50),
        internal_number: z.string().max(50).nullable().optional(),
        municipality: z.string().max(255).nullable().optional(),
        state: z.string().max(255).nullable().optional(),
        latitude: z.number().nullable().optional(),
        longitude: z.number().nullable().optional(),
        type: z.string().nonempty("El tipo de dirección es obligatorio"),
        addressable_id: z.number().int().positive(),      // 🔸 polimórfico
        addressable_type: z.string().nonempty(),          // 🔸 polimórfico (FQCN)
      })
    )

    const { values, isFieldDirty, handleSubmit, setFieldValue } = useForm({
      validationSchema: this.formSchema,
      initialValues: {
        postal_code: address?.postal_code ?? "",
        street: address?.street ?? "",
        name: address?.name ?? "",
        neighborhood: address?.neighborhood ?? "",
        external_number: address?.external_number ?? "",
        internal_number: address?.internal_number ?? "",
        municipality: address?.municipality ?? "",
        state: address?.state ?? "",
        latitude: address?.latitude ?? null,
        longitude: address?.longitude ?? null,
        type: address?.type ?? "",
        addressable_id: ownerId,
        addressable_type: ownerType,
      },
    })

    this.values = values
    this.isFieldDirty = isFieldDirty
    this.setFieldValue = setFieldValue as unknown as (field: string, value: any) => void

    // Crear
    this.saveAddress = handleSubmit(async (vals) => {
      this.loading.value = true
      this.errors.value = {}

      const form = useInertiaForm(vals)

      form.post(route("addresses.store"), {
        preserveState: true,
        onSuccess: () => {
          this.saved.value = true
          const p: any = usePage().props
          this.savedAddress.value = p?.recent?.address ?? null
        },
        onError: (errs: Record<string, any>) => {
          const normalized: Record<string, string[]> = {}
          Object.entries(errs).forEach(([k, v]) => (normalized[k] = Array.isArray(v) ? v : [v]))
          this.errors.value = normalized
        },
        onFinish: () => (this.loading.value = false),
      })
    })

    // Editar
    this.updateAddress = handleSubmit(async (vals) => {
      if (!this.address?.value?.id) return

      this.loading.value = true
      this.errors.value = {}

      const form = useInertiaForm(vals)

      form.patch(route("addresses.update", this.address.value.id), {
        preserveState: true,
        onSuccess: () => {
          this.saved.value = true
          const p: any = usePage().props
          this.savedAddress.value = p?.recent?.address ?? null
        },
        onError: (errs: Record<string, any>) => {
          const normalized: Record<string, string[]> = {}
          Object.entries(errs).forEach(([k, v]) => (normalized[k] = Array.isArray(v) ? v : [v]))
          this.errors.value = normalized
        },
        onFinish: () => (this.loading.value = false),
      })
    })
  }
}

// Compatibilidad: helpers con export nombrado
export function createAddress(payload: any, owner: Owner) {
  const formData = {
    ...payload,
    addressable_id: owner.ownerId,
    addressable_type: owner.ownerType,
  }
  const form = useInertiaForm(formData)
  return new Promise<Address>((resolve, reject) => {
    form.post(route("addresses.store"), {
      preserveState: true,
      onSuccess: () => {
        const p: any = usePage().props
        resolve(p?.recent?.address ?? null)
      },
      onError: (errors: unknown) => reject({ response: { data: { errors } } }),
    })
  })
}

export function updateAddress(id: string | number, payload: any, owner: Owner) {
  const formData = {
    ...payload,
    addressable_id: owner.ownerId,
    addressable_type: owner.ownerType,
  }
  const form = useInertiaForm(formData)
  return new Promise<Address>((resolve, reject) => {
    form.patch(route("addresses.update", id), {
      preserveState: true,
      onSuccess: () => {
        const p: any = usePage().props
        resolve(p?.recent?.address ?? null)
      },
      onError: (errors: unknown) => reject({ response: { data: { errors } } }),
    })
  })
}

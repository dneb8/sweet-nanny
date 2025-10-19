// resources/js/services/bookingFormService.ts
import { ref, Ref, computed } from "vue"
import { toTypedSchema } from "@vee-validate/zod"
import { useForm } from "vee-validate"
import { useForm as useInertiaForm } from "@inertiajs/vue3"
import { route } from "ziggy-js"
import { useField } from "vee-validate"
import * as z from "zod"
import type { Booking } from "@/types/Booking"
import type { Address } from "@/types/Address"
import type { BookingAppointment } from "@/types/BookingAppointment"

type AppointmentForForm = Pick<BookingAppointment, "start_date" | "end_date"> & { duration: number }

type BookingFormValues = {
  booking: {
    tutor_id: number | null
    address_id: number | null
    description: string
    recurrent: boolean
    child_ids: string[]            // <-- UI usa IDs planos
    qualities: string[]            // New: qualities array
    degree: string | null          // New: degree
    courses: string[]              // New: courses array
  }
  appointments: AppointmentForForm[]
  address: Pick<
    Address,
    "postal_code" | "street" | "neighborhood" | "type" | "other_type" | "internal_number"
  >
}

export class BookingFormService {
  public booking?: Ref<Booking>
  public formSchema
  public values
  public isFieldDirty
  public loading = ref(false)
  public saved = ref(false)
  public serverErrors = ref<Record<string, string[]>>({})
  public clientErrors
  public meta
  public canSubmit
  public saveBooking!: (e?: Event) => Promise<void>
  public updateBooking!: (e?: Event) => Promise<void>
  public lastInvalidField = ref<string | null>(null)
  public lastInvalidStep = ref<number>(1)
  public errorTick = ref(0)
  private initialTutorId?: number

  constructor(booking?: Booking, tutorId?: number | string) {
    if (booking) this.booking = ref(booking)
    this.initialTutorId = tutorId != null ? Number(tutorId) : undefined

    const appointmentItem = z.object({
      start_date: z.string().min(1, "Selecciona fecha y hora"),
      end_date: z.string().min(1, "Calcula hora de término"),
      duration: z.number().min(1, "Mínimo 1h").max(8, "Máximo 8h"),
    })

    // Validación para los campos que realmente bindea el UI
    this.formSchema = toTypedSchema(
      z.object({
        booking: z.object({
          tutor_id: z.number().int().min(1, "Tutor inválido"),
          description: z.string().trim().min(5, "Agrega una descripción"),
          recurrent: z.boolean(),
          child_ids: z.array(z.string()).min(1, "Selecciona al menos 1 niño").max(4, "Máximo 4 niños"),
          address_id: z.number().nullable().optional(),
          qualities: z.array(z.string()).optional(),
          degree: z.string().nullable().optional(),
          courses: z.array(z.string()).optional(),
        }),
        appointments: z.array(appointmentItem).min(1, "Agrega al menos 1 cita"),
        address: z.object({
          postal_code: z.string().min(4, "Código postal requerido"),
          street: z.string().min(2, "Calle requerida"),
          neighborhood: z.string().min(2, "Colonia requerida"),
          type: z.string().min(2, "Tipo requerido"),
          other_type: z.string().optional(),
          internal_number: z.string().optional(),
        }),
      })
    )

    const initialChildIds: string[] =
      Array.isArray(booking?.children)
        ? booking!.children
            .map((c: any) => c?.id)
            .filter((id: any) => id != null)
            .map((id: any) => String(id))
        : []

    const initial: BookingFormValues = {
      booking: {
        tutor_id: this.initialTutorId ?? null,
        address_id: booking?.address_id ?? (booking?.address?.id as any) ?? null,
        description: booking?.description ?? "",
        recurrent: !!booking?.recurrent,
        child_ids: initialChildIds,
        qualities: Array.isArray(booking?.qualities) ? booking.qualities : [],
        degree: booking?.degree ?? null,
        courses: Array.isArray(booking?.courses) ? booking.courses : [],
      },
      appointments: (booking?.bookingAppointments ?? booking?.booking_appointments)?.map(this.mapAppointment) ?? [],
      address: {
        postal_code: booking?.address?.postal_code ?? "",
        street: booking?.address?.street ?? "",
        neighborhood: booking?.address?.neighborhood ?? "",
        type: booking?.address?.type ?? "",
        other_type: booking?.address?.other_type ?? "",
        internal_number: booking?.address?.internal_number ?? "",
      },
    }

    const { values, isFieldDirty, handleSubmit, errors, meta, setErrors } = useForm<BookingFormValues>({
      validationSchema: this.formSchema,
      initialValues: initial,
      keepValuesOnUnmount: true,
    })

    this.values = values
    this.isFieldDirty = isFieldDirty
    this.clientErrors = errors
    this.meta = meta
    this.canSubmit = computed(() => this.meta.value.valid && !this.loading.value)

    const onErrorHandler = (errs: Record<string, any>) => {
      const remapped = remapServerErrors(errs)
      // trazas de depuración
      console.log('[BookingFormService] raw server errors:', errs)
      console.log('[BookingFormService] remapped errors:', remapped)
      this.serverErrors.value = normalizeErrors(remapped)
      setErrors(remapped)
      this.setInvalidFromErrors(remapped)
    }

    this.saveBooking = async (e?: Event) => {
      await handleSubmit(
        async (vals) => {
          this.loading.value = true
          this.serverErrors.value = {}
          const payload = this.createPayload(vals)
          const form = useInertiaForm(payload)
          form.post(route("bookings.store"), {
            onSuccess: () => { this.saved.value = true },
            onError: onErrorHandler,
            onFinish: () => { this.loading.value = false },
            preserveScroll: true,
          })
        },
        (invalidCtx) => {
          this.setInvalidFromErrors(invalidCtx?.errors ?? {})
        }
      )(e)
    }

    this.updateBooking = async (e?: Event) => {
      await handleSubmit(
        async (vals) => {
          const bookingId = this.booking?.value?.id
          if (!bookingId) return
          this.loading.value = true
          this.serverErrors.value = {}
          const payload = this.createPayload(vals)
          const form = useInertiaForm(payload)
          form.put(route("bookings.update", bookingId), {
            onSuccess: () => { this.saved.value = true },
            onError: onErrorHandler,
            onFinish: () => { this.loading.value = false },
            preserveScroll: true,
          })
        },
        (invalidCtx) => {
          this.setInvalidFromErrors(invalidCtx?.errors ?? {})
        }
      )(e)
    }
  }

  private mapAppointment(a: BookingAppointment): AppointmentForForm {
    return {
      start_date: String(a.start_date),
      end_date: String(a.end_date),
      duration: 0,
    }
  }

  private createPayload(vals: BookingFormValues) {
    // El backend espera booking.children (IDs), no child_ids
    return {
      booking: {
        tutor_id: Number(vals.booking.tutor_id ?? this.initialTutorId ?? 0),
        address_id: vals.booking.address_id ?? null,
        description: vals.booking.description || "",
        recurrent: !!vals.booking.recurrent,
        children: (vals.booking.child_ids ?? []).map(String),
        qualities: vals.booking.qualities ?? [],
        degree: vals.booking.degree ?? null,
        courses: vals.booking.courses ?? [],
      },
      appointments: vals.appointments.map((a) => ({
        start_date: a.start_date,
        end_date: a.end_date,
        duration: Number(a.duration || 0),
      })),
      address: vals.address,
    }
  }

  private fieldToStep(path: string): number {
    if (!path) return 1
    if (path.startsWith('booking.description') || path.startsWith('booking.child_ids') || path.startsWith('booking.recurrent')) return 1
    if (path.startsWith('appointments')) return 2
    if (path.startsWith('address') || path === 'booking.address_id') return 3
    if (path.startsWith('booking.qualities') || path.startsWith('booking.degree') || path.startsWith('booking.courses')) return 4
    if (path.startsWith('booking.')) return 1
    return 1
  }

  private setInvalidFromErrors(errs: Record<string, any>) {
    const keys = Object.keys(errs ?? {})
    const firstKey = keys[0] || ""
    this.lastInvalidField.value = firstKey || null
    this.lastInvalidStep.value = this.fieldToStep(firstKey)
    this.errorTick.value++
  }
}

function normalizeErrors(e: Record<string, any>): Record<string, string[]> {
  const out: Record<string, string[]> = {}
  Object.entries(e ?? {}).forEach(([k, v]) => {
    out[k] = Array.isArray(v) ? v : [String(v)]
  })
  return out
}

// Convierte claves del backend a las del UI:
// - booking.children.0 -> booking.child_ids.0
function remapServerErrors(errs: Record<string, any>): Record<string, string[]> {
  const out: Record<string, string[]> = {}
  Object.entries(errs ?? {}).forEach(([k, v]) => {
    let key = k
    // children -> child_ids
    if (key === 'booking.children') key = 'booking.child_ids'
    if (key.startsWith('booking.children.')) {
      key = key.replace('booking.children.', 'booking.child_ids.')
    }
    // address -> booking.address_id
    if (key === 'address_id') key = 'booking.address_id'
    if (key === 'booking.address') key = 'booking.address_id'
    if (key === 'booking.address_id') key = 'booking.address_id'

    out[key] = Array.isArray(v) ? v.map(String) : [String(v)]
  })
  return out
}

export function useBoundField<T = any>(name: string) {
  const { value, errorMessage } = useField<T>(name)
  return { value, errorMessage }
}

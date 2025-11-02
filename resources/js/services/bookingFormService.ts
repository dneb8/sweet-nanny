// resources/js/services/bookingFormService.ts
import { ref, Ref, computed } from "vue"
import { toTypedSchema } from "@vee-validate/zod"
import { useForm } from "vee-validate"
import { useForm as useInertiaForm } from "@inertiajs/vue3"
import { route } from "ziggy-js"
import { useField } from "vee-validate"
import * as z from "zod"
import type { Booking } from "@/types/Booking"
import type { BookingAppointment } from "@/types/BookingAppointment"

type AppointmentForForm = Pick<BookingAppointment, "start_date" | "end_date"> & { duration: number }

type BookingFormValues = {
  booking: {
    tutor_id: number | null
    address_id: number | null
    description: string
    recurrent: boolean
  child_ids: number[]     
  qualities: string[]      
  careers: string[]        
  courses: string[]        
  }
  appointments: AppointmentForForm[]
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

    // Schema Zod (careers como array, máx 5)
    this.formSchema = toTypedSchema(
      z.object({
        booking: z.object({
          tutor_id: z.number().int().min(1, "Tutor inválido"),
          description: z.string().trim().min(5, "Agrega una descripción"),
          recurrent: z.boolean(),
          child_ids: z.array(z.number()).min(1, "Selecciona al menos 1 niño").max(4, "Máximo 4 niños"),

          address_id: z.preprocess(
            (v) => (v === null || v === "" ? undefined : v),
            z.number({
              required_error: "La dirección es obligatoria",
              invalid_type_error: "La dirección es obligatoria",
            })
              .int({ message: "La dirección es obligatoria" })
              .min(1, "La dirección es obligatoria")
          ),

          qualities: z.array(z.string()).optional().default([]),
          careers:  z.array(z.string()).max(5, "Máximo 5 carreras").optional().default([]), // <- array
          courses:  z.array(z.string()).optional().default([]),
        }),
        appointments: z.array(appointmentItem).min(1, "Agrega al menos 1 cita"),
      })
    )

    // Extract children and address from first appointment (if editing)
    const firstAppointment = booking?.booking_appointments?.[0]
    const initialChildIds: number[] =
      Array.isArray(firstAppointment?.children)
        ? firstAppointment!.children
            .map((c: any) => c?.id)
            .filter((id: any) => id != null)
            .map((id: any) => Number(id))
        : []

    const initialAddressId = 
      firstAppointment?.addresses?.[0]?.id ?? null

    const initial: BookingFormValues = {
      booking: {
        tutor_id: this.initialTutorId ?? null,
        address_id: initialAddressId,
        description: booking?.description ?? "",
        recurrent: !!booking?.recurrent,
  child_ids: initialChildIds,

        qualities: Array.isArray((booking as any)?.qualities) ? (booking as any).qualities : [],
        // Migración suave: si backend aún trae career/degree como string, mételo en array.
        careers: Array.isArray((booking as any)?.careers)
          ? (booking as any).careers
          : (
              (booking as any)?.career ? [(booking as any).career] :
              (booking as any)?.degree ? [(booking as any).degree] : []
            ),

        courses: Array.isArray((booking as any)?.courses) ? (booking as any).courses : [],
      },
      appointments: (booking?.booking_appointments ?? booking?.booking_appointments)?.map(this.mapAppointment) ?? [],
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
      console.log('[BookingFormService] raw server errors:', errs)
      if (process.env.NODE_ENV === 'development') {
        console.log('[BookingFormService] remapped errors:', remapped)
      }
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
    const s = a.start_date ? new Date(String(a.start_date)) : null
    const e = a.end_date ? new Date(String(a.end_date)) : null
    const durH = s && e ? Math.max(1, Math.round((+e - +s) / 36e5)) : 1
    return {
      start_date: String(a.start_date ?? ''),
      end_date: String(a.end_date ?? ''),
      duration: durH,
    }
  }

  private createPayload(vals: BookingFormValues) {
    const childIds = (vals.booking.child_ids ?? []).map((n) => Number(n)).filter((n) => !Number.isNaN(n))
    return {
      booking: {
        tutor_id: Number(vals.booking.tutor_id ?? this.initialTutorId ?? 0),
        address_id: vals.booking.address_id ?? null,
        description: vals.booking.description || "",
        recurrent: !!vals.booking.recurrent,
        child_ids: childIds,
        children: childIds.map(String),

        qualities: vals.booking.qualities ?? [],
        careers:  vals.booking.careers ?? [],   // <- array
        courses:  vals.booking.courses ?? [],
      },
      appointments: this.values.appointments?.map((a: any) => ({
        start_date: a.start_date,
        end_date: a.end_date,
        duration: Math.max(1, Number(a.duration || 0)),
        status: a.status ?? 'pending',
        payment_status: a.payment_status ?? 'unpaid',
        extra_hours: Number(a.extra_hours || 0),
        total_cost: Number(a.total_cost || 0),
      })) ?? [],
    };
  }

  // Map a field name to a step number for validation errors
  private fieldToStep(path: string): number {
    if (!path) return 1;
    if (
      path.startsWith('booking.description') ||
      path.startsWith('booking.child_ids') ||
      path.startsWith('booking.recurrent')
    )
      return 1;
    if (path.startsWith('appointments')) return 2;
    if (path.startsWith('address') || path === 'booking.address_id') return 3;
    if (
      path.startsWith('booking.qualities') ||
      path.startsWith('booking.careers') ||
      path.startsWith('booking.courses')
    )
      return 4;
    if (path.startsWith('booking.')) return 1;
    return 1;
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

// Convierte claves del backend a las del UI
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

    // legacy: career (string) -> careers (array)
    if (key === 'booking.career') key = 'booking.careers'
    if (key.startsWith('booking.career.')) {
      key = key.replace('booking.career.', 'booking.careers.')
    }

    out[key] = Array.isArray(v) ? v.map(String) : [String(v)]
  })
  return out
}

export function useBoundField<T = any>(name: string) {
  const { value, errorMessage } = useField<T>(name)
  return { value, errorMessage }
}

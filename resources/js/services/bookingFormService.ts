import { ref, computed, toRaw } from "vue"
import { useForm, useField } from "vee-validate"
import * as z from "zod"
import { toTypedSchema } from "@vee-validate/zod"
import { toast } from "vue-sonner"
import { router } from "@inertiajs/vue3"
import { route } from "ziggy-js"

type UseBookingFormOptions = {
  mode?: "create" | "edit"
  bookingId?: string | number
  routes?: { create: string; update: string }
}

export function useBookingForm(opts: UseBookingFormOptions = {}) {
  const mode = opts.mode ?? "create"
  const routesCfg = {
    create: opts.routes?.create ?? "bookings.store",
    update: opts.routes?.update ?? "bookings.update",
  }

  const stepIndex = ref(1)
  const steps = [
    { step: 1, title: "Servicio",  description: "Describe tu servicio",        icon: "solar:clipboard-text-broken" },
    { step: 2, title: "Citas",     description: "Selecciona fecha y hora",     icon: "solar:calendar-linear" },
    { step: 3, title: "Dirección", description: "Lugar del servicio",          icon: "solar:map-point-linear" },
  ]

  const appointmentItem = z.object({
    start_date: z.string().min(1, "Selecciona fecha y hora"),
    end_date:   z.string().min(1, "Calcula hora de término"),
    duration:   z.number().min(1, "Duración mínima 1 hora").max(8, "Duración máxima 8 horas"),
  })

  // Forma canónica que usa el form
  const baseInitial = {
    booking: {
      tutor_id: 0 as number,
      address_id: null as number | null,
      description: "" as string,
      recurrent: false as boolean,
      child_ids: [] as string[], // <- strings
    },
    appointments: [] as Array<{ start_date: string; end_date: string; duration: number }>,
    address: {
      postal_code: "",
      street: "",
      neighborhood: "",
      type: "",
      other_type: "",
      internal_number: "",
    },
  }

  const initialSnapshot = ref(structuredClone(baseInitial))

  const { handleSubmit, validate, setFieldValue, values, errors, isSubmitting, resetForm, setErrors } = useForm({
    keepValuesOnUnmount: true,
    validationSchema: computed(() => {
      if (stepIndex.value === 1) {
        return toTypedSchema(
          z.object({
            booking: z.object({
              tutor_id:    z.number().min(1, "Selecciona un tutor"),
              description: z.string().trim().min(5, "Agrega una breve descripción"),
              recurrent:   z.boolean(),
              child_ids:   z.array(z.string()).min(1, "Selecciona al menos 1 niño").max(4, "Máximo 4 niños"),
              address_id:  z.number().nullable().optional(),
            }),
          }).passthrough()
        )
      }
      if (stepIndex.value === 2) {
        const isRec  = !!values.booking?.recurrent
        const minCnt = isRec ? 2 : 1
        const maxCnt = isRec ? 10 : 1
        const minMsg = isRec ? "Agrega al menos 2 citas para un servicio recurrente" : "Agrega 1 cita"
        const maxMsg = isRec ? "Máximo 10 citas para un servicio recurrente" : "En servicio fijo debe haber exactamente 1 cita"
        return toTypedSchema(
          z.object({
            booking:      z.object({ recurrent: z.boolean() }),
            appointments: z.array(appointmentItem).min(minCnt, minMsg).max(maxCnt, maxMsg),
          }).passthrough()
        )
      }
      return toTypedSchema(
        z.object({
          address: z.object({
            postal_code:     z.string().min(4, "Código postal requerido"),
            street:          z.string().min(2, "Calle requerida"),
            neighborhood:    z.string().min(2, "Colonia requerida"),
            type:            z.string().min(2, "Tipo de dirección requerido"),
            other_type:      z.string().optional(),
            internal_number: z.string().optional(),
          }),
        }).passthrough()
      )
    }),
    initialValues: initialSnapshot.value,
  })

  const isDirty = computed(() => {
    const current = JSON.parse(JSON.stringify(toRaw(values)))
    const snap    = JSON.parse(JSON.stringify(initialSnapshot.value))
    return JSON.stringify(current) !== JSON.stringify(snap)
  })

  // 🔧 NORMALIZADOR: acepta payload tipo { booking:{...} } ó el modelo plano $booking
  function normalizeServerInitial(server: any): typeof baseInitial {
    const s = JSON.parse(JSON.stringify(server || {}))

    // child_ids desde booking.child_ids o derivados de children[]
    const childIdsFromBooking = Array.isArray(s?.booking?.child_ids)
      ? s.booking.child_ids.map((x: any) => String(x))
      : []

    const childIdsFromTop = Array.isArray(s?.children)
      ? s.children.map((c: any) => String(c?.id)).filter(Boolean)
      : []

    const booking = {
      tutor_id:   Number(s?.booking?.tutor_id ?? s?.tutor_id ?? 0),
      address_id: s?.booking?.address_id != null
                    ? Number(s.booking.address_id) || null
                    : (s?.address_id != null
                        ? Number(s.address_id) || null
                        : (s?.address?.id != null ? Number(s.address.id) || null : null)),
      description: String(s?.booking?.description ?? s?.description ?? ""),
      recurrent:   Boolean(s?.booking?.recurrent ?? s?.recurrent ?? false),
      child_ids:   childIdsFromBooking.length ? childIdsFromBooking : childIdsFromTop,
    }

    // Appointments: soporta server.appointments o server.bookingAppointments
    const rawAppts = Array.isArray(s?.appointments)
      ? s.appointments
      : (Array.isArray(s?.bookingAppointments) ? s.bookingAppointments : [])

    const appointments = rawAppts.map((a: any) => ({
      start_date: String(a?.start_date ?? ""),
      end_date:   String(a?.end_date ?? ""),
      duration:   Number(a?.duration ?? 0),
    }))

    const address = {
      postal_code:     String(s?.address?.postal_code ?? ""),
      street:          String(s?.address?.street ?? ""),
      neighborhood:    String(s?.address?.neighborhood ?? ""),
      type:            String(s?.address?.type ?? ""),
      other_type:      String(s?.address?.other_type ?? ""),
      internal_number: String(s?.address?.internal_number ?? ""),
    }

    return { booking, appointments, address }
  }

  function hydrateWithServerValues(serverPayload: any) {
    const payload = normalizeServerInitial(serverPayload)
    initialSnapshot.value = structuredClone(payload)
    resetForm({ values: structuredClone(payload) })
  }

  async function nextStep() {
    const result = await validate()
    if (!result.valid) {
      const firstMsg =
        (Object.values(result.errors)[0] as any) ??
        (Object.values(errors.value)[0] as any) ??
        "Revisa los campos del paso actual"
      toast.error(String(firstMsg))
      return
    }
    if (stepIndex.value < steps.length) stepIndex.value += 1
  }
  function prevStep() {
    if (stepIndex.value > 1) stepIndex.value -= 1
  }

  const onSubmit = handleSubmit(async () => {
    const payload = JSON.parse(JSON.stringify(values))

    if (!payload.booking) payload.booking = {}

    const aid = payload.booking.address_id
    if (!aid || Number(aid) === 0) payload.booking.address_id = null

    payload.booking.tutor_id = Number(payload.booking.tutor_id || 0)
    payload.booking.recurrent = !!payload.booking.recurrent
    if (Array.isArray(payload.booking.child_ids)) {
      payload.booking.child_ids = payload.booking.child_ids.map((x: any) => String(x))
    }

    console.log("[BOOKING] payload →", JSON.stringify(payload, null, 2))

    if (mode === "create") {
      router.post(route(routesCfg.create), payload, {
        preserveScroll: true,
        onSuccess: () => toast.success("Servicio creado", { description: "Se registró correctamente tu servicio" }),
        onError: (inertiaErrors) => {
          setErrors(flattenInertiaErrors(inertiaErrors))
          toast.error("No se pudo crear el servicio", { description: "Revisa los campos marcados" })
        },
      })
      return
    }

    if (!opts.bookingId) {
      toast.error("No se pudo actualizar", { description: "ID de servicio no válido" })
      return
    }
    router.put(route(routesCfg.update, opts.bookingId), payload, {
      preserveScroll: true,
      onSuccess: () => toast.success("Servicio actualizado", { description: "Cambios guardados correctamente" }),
      onError: (inertiaErrors) => {
        setErrors(flattenInertiaErrors(inertiaErrors))
        toast.error("No se pudo actualizar", { description: "Revisa los campos marcados" })
      },
    })
  })

  function upsertSingleAppointment(startISO: string, durationHours: number) {
    const start = new Date(startISO)
    const end = new Date(start)
    end.setHours(end.getHours() + durationHours)
    setFieldValue("appointments", [
      { start_date: start.toISOString(), end_date: end.toISOString(), duration: durationHours },
    ])
  }

  return {
    stepIndex, steps, nextStep, prevStep,
    values, errors, isSubmitting, setFieldValue, onSubmit,
    isDirty, hydrateWithServerValues, initialSnapshot,
    upsertSingleAppointment,
  }
}

export function useBoundField<T = any>(name: string) {
  const { value, errorMessage } = useField<T>(name)
  return { value, errorMessage }
}

function flattenInertiaErrors(e: Record<string, any>): Record<string, string> {
  const out: Record<string, string> = {}
  Object.entries(e ?? {}).forEach(([k, v]) => { out[k] = Array.isArray(v) ? String(v[0]) : String(v) })
  return out
}

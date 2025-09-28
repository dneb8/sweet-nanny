import { ref, computed } from "vue"
import { useForm, useField } from "vee-validate"
import * as z from "zod"
import { toTypedSchema } from "@vee-validate/zod"
import { toast } from "vue-sonner"

export function useBookingForm() {
  // 🧭 Stepper
  const stepIndex = ref(1)
  const steps = [
    { step: 1, title: "Servicio",  description: "Describe tu servicio",    icon: "solar:clipboard-text-broken" },
    { step: 2, title: "Citas",     description: "Selecciona fecha y hora", icon: "solar:calendar-linear" },
    { step: 3, title: "Dirección", description: "Lugar del servicio",      icon: "solar:map-point-linear" },
  ]

  // 🔤 Sub-esquemas
  const appointmentItem = z.object({
    start_date: z.string().min(1, "Selecciona fecha y hora"),
    end_date  : z.string().min(1, "Calcula hora de término"),
    duration  : z.number()
      .min(1, "Duración mínima 1 hora")
      .max(8, "Duración máxima 8 horas"),
  })

  // 🗂️ Valores iniciales (incluye child_ids)
  const initialValues = {
    booking: {
      tutor_id   : 0,
      address_id : 0,
      description: "",
      recurrent  : false,
      child_ids  : [] as string[],
    },
    appointments: [] as Array<{ start_date: string; end_date: string; duration: number }>,
    address: {
      postal_code    : "",
      street         : "",
      neighborhood   : "",
      type           : "",
      other_type     : "",
      internal_number: "",
    },
  }

  // 🧠 Form base
  const { handleSubmit, validate, setFieldValue, values, errors, isSubmitting } = useForm({
    // Esquema dinámico por paso (condicional en paso 2)
    validationSchema: computed(() => {
      if (stepIndex.value === 1) {
        return toTypedSchema(
          z.object({
            booking: z.object({
              tutor_id   : z.number().min(1),
              description: z.string().trim().min(5, "Agrega una breve descripción"),
              recurrent  : z.boolean(),
              child_ids  : z.array(z.string())
                .min(1, "Selecciona al menos 1 niño")
                .max(4, "Máximo 4 niños"),
            }),
          })
        )
      }

      if (stepIndex.value === 2) {
        const isRec = !!values.booking?.recurrent
        const minMsg = isRec ? "Agrega al menos 2 citas para un servicio recurrente" : "Agrega 1 cita"
        const maxMsg = isRec ? "Máximo 10 citas para un servicio recurrente" : "En servicio fijo debe haber exactamente 1 cita"

        // Nota: para 'fijo' usamos min=1 y max=1 (exactamente 1)
        const minCount = isRec ? 2 : 1
        const maxCount = isRec ? 10 : 1

        return toTypedSchema(
          z.object({
            booking: z.object({ recurrent: z.boolean() }), // necesario para leer isRec en el step
            appointments: z.array(appointmentItem)
              .min(minCount, minMsg)
              .max(maxCount, maxMsg),
          })
        )
      }

      // Paso 3
      return toTypedSchema(
        z.object({
          address: z.object({
            postal_code    : z.string().min(4, "Código postal requerido"),
            street         : z.string().min(2, "Calle requerida"),
            neighborhood   : z.string().min(2, "Colonia requerida"),
            type           : z.string().optional(),
            other_type     : z.string().optional(),
            internal_number: z.string().optional(),
          }),
        })
      )
    }),
    initialValues,
  })

  // ⏭️ Navegación con guardia de validación
  async function nextStep() {
    const result = await validate()
    if (!result.valid) {
      const firstMsg =
        // error plano del paso actual
        (Object.values(result.errors)[0] as any) ??
        // o el primero reactivo
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

  // 📨 Submit final (valida todo junto por seguridad)
  const onSubmit = handleSubmit(async (vals) => {
    const schema1 = z.object({
      booking: z.object({
        tutor_id   : z.number().min(1),
        description: z.string().trim().min(5),
        recurrent  : z.boolean(),
        child_ids  : z.array(z.string()).min(1).max(4),
      }),
    })
    const schema2 = z.object({
      booking: z.object({ recurrent: z.boolean() }),
      appointments: z.array(appointmentItem),
    }).refine(
      (d) => d.booking.recurrent ? d.appointments.length >= 2 && d.appointments.length <= 10
                                 : d.appointments.length === 1,
      {
        message: vals.booking.recurrent
          ? "Para recurrente: 2 a 10 citas"
          : "Para fijo: exactamente 1 cita",
        path: ["appointments"],
      }
    )
    const schema3 = z.object({
      address: z.object({
        postal_code   : z.string().min(4),
        street        : z.string().min(2),
        neighborhood  : z.string().min(2),
        type          : z.string().optional(),
        other_type    : z.string().optional(),
        internal_number: z.string().optional(),
      }),
    })

    schema1.parse(vals)
    schema2.parse(vals)
    schema3.parse(vals)

    // TODO: enviar a backend
    // await axios.post('/api/bookings', vals)

    toast("El servicio ha sido creado", {
      description: "Se registró correctamente tu servicio",
      action: { label: "Deshacer", onClick: () => console.log("Undo") },
    })
  })

  // ⏱️ Util: crear/actualizar 1 cita fija y calcular fin
  function upsertSingleAppointment(startISO: string, durationHours: number) {
    const start = new Date(startISO)
    const end = new Date(start)
    end.setHours(end.getHours() + durationHours)
    const appt = {
      start_date: start.toISOString(),
      end_date  : end.toISOString(),
      duration  : durationHours,
    }
    setFieldValue("appointments", [appt])
  }

  return {
    // stepper
    stepIndex, steps, nextStep, prevStep,
    // form
    values, errors, isSubmitting, setFieldValue, onSubmit,
    // helpers
    upsertSingleAppointment,
  }
}

// 🔌 Campo enlazado por nombre (para los Steps)
export function useBoundField<T = any>(name: string) {
  const { value, errorMessage } = useField<T>(name)
  return { value, errorMessage }
}

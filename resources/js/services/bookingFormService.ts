import { ref, computed } from "vue"
import { useForm, useField } from "vee-validate"
import * as z from "zod"
import { toTypedSchema } from "@vee-validate/zod"
import { toast } from "vue-sonner"

export function useBookingForm() {
  // 🧭 Stepper
  const stepIndex = ref(1)
  const steps = [
  { step: 1, title: "Servicio",  description: "Describe tu servicio",                      icon: "solar:clipboard-text-broken" },
  { step: 2, title: "Citas",     description: "Selecciona fecha, hora y duración",         icon: "solar:calendar-linear" },
  { step: 3, title: "Dirección", description: "Indica el lugar del servicio",              icon: "solar:map-point-linear" },
]

  // 🧪 Schemas por paso (solo valida lo visible)
  const stepSchemas = [
    z.object({
      booking: z.object({
        description: z.string().min(5, "Agrega una breve descripción").nonempty("La descripción es obligatoria"),
        recurrent: z.boolean().default(false),
      }),
    }),
    z.object({
      appointments: z.array(
        z.object({
          start_date: z.string().min(1, "Selecciona fecha y hora"),
          end_date: z.string().min(1, "Calcula hora de término"),
          duration: z.number().min(1).max(8, "Duración entre 1 y 8 horas"),
        })
      ).min(1, "Agrega al menos una cita"),
    }),
    z.object({
      address: z.object({
        postal_code: z.string().min(4, "Código postal requerido"),
        street: z.string().min(2, "Calle requerida"),
        neighborhood: z.string().min(2, "Colonia requerida"),
        type: z.string().optional(),
        other_type: z.string().optional(),
        internal_number: z.string().optional(),
      }),
    }),
  ] as const

  // 🗂️ Valores iniciales únicos para TODO el formulario
  const initialValues = {
    booking: { description: "", recurrent: false, tutor_id: 0, address_id: 0 },
    appointments: [] as Array<{ start_date: string; end_date: string; duration: number }>,
    address: { postal_code: "", street: "", neighborhood: "", type: "", other_type: "", internal_number: "" },
  }

  // 🧠 Form con validación dinámicamente atada al paso actual
  const { handleSubmit, validate, setFieldValue, values, errors, isSubmitting } = useForm({
    validationSchema: computed(() => toTypedSchema(stepSchemas[stepIndex.value - 1])),
    initialValues,
  })

  // ⏭️ Navegación + guardias de validación
  async function nextStep() {
    const res = await validate()
    if (!res.valid) {
      toast.error("Revisa los campos del paso actual")
      return
    }
    if (stepIndex.value < steps.length) stepIndex.value += 1
  }
  function prevStep() {
    if (stepIndex.value > 1) stepIndex.value -= 1
  }

  // 📨 Submit final (valida TODO juntando esquemas)
  const onSubmit = handleSubmit(async (vals) => {
    // Validación global antes de enviar (opcional)
    const fullSchema = z.intersection(stepSchemas[0], z.intersection(stepSchemas[1], stepSchemas[2]))
    fullSchema.parse(vals)

    // aquí haces tu fetch/axios.post a Laravel
    // await axios.post('/api/bookings', vals)

    toast("El servicio ha sido creado", {
      description: "Se registró correctamente tu servicio",
      action: { label: "Deshacer", onClick: () => console.log("Undo") },
    })
  })

  // Utilitario para calcular end_date y setear cita
  function upsertSingleAppointment(startISO: string, durationHours: number) {
    const start = new Date(startISO)
    const end = new Date(start)
    end.setHours(end.getHours() + durationHours)
    const appt = {
      start_date: start.toISOString(),
      end_date: end.toISOString(),
      duration: durationHours,
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

// 🔌 Patrón recomendado en los steps
export function useBoundField<T = any>(name: string) {
  const { value, errorMessage } = useField<T>(name)
  return { value, errorMessage }
}

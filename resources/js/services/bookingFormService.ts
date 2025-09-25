import { ref } from "vue"
import * as z from "zod"
import { toTypedSchema } from "@vee-validate/zod"
import { toast } from "vue-sonner"

// ✅ Esquemas de validación por step
export const formSchema = [
  z.object({
    title: z.string().min(3, "El título es requerido"),
    description: z.string().min(5, "Agrega una breve descripción"),
  }),
  z.object({
    date: z.string().nonempty("Selecciona una fecha"),
    time: z.string().nonempty("Selecciona la hora"),
    duration: z.number({ required_error: "La duración es requerida" }),
    address: z.string().min(5, "La dirección es requerida"),
  }),
  z.object({
    serviceType: z.union([
      z.literal("por_horas"),
      z.literal("tiempo_completo"),
      z.literal("tutoria"),
    ]),
    requirements: z.string().optional(),
  }),
]

// ✅ Stepper
export const stepIndex = ref(1)
export const steps = [
  { step: 1, title: "Datos del servicio", description: "Título y descripción" },
  { step: 2, title: "Información logística", description: "Fecha, hora y ubicación" },
  { step: 3, title: "Preferencias", description: "Tipo de servicio y requerimientos" },
]

// ✅ Submit
export function onSubmit(values: any) {
  console.log("Formulario enviado:", values)

  toast("El servicio ha sido creado", {
    description: "Sunday, December 03, 2023 at 9:00 AM",
    action: {
      label: "Undo",
      onClick: () => console.log("Undo"),
    },
  })

  // Aquí puedes hacer el fetch/axios.post a tu backend Laravel
}

// ✅ Export para usar en BookingForm.vue
export function useBookingForm() {
  return {
    formSchema,
    stepIndex,
    steps,
    onSubmit,
    toTypedSchema,
  }
}

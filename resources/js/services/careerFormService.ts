// CareerFormService.ts
import { ref, Ref } from "vue";
import { toTypedSchema } from "@vee-validate/zod";
import { useForm } from "vee-validate";
import { useForm as useInertiaForm } from "@inertiajs/vue3";
import * as z from "zod";
import { Career } from "@/types/Career";
import { Nanny } from "@/types/Nanny";

export class CareerFormService {
  public career?: Ref<Career>;
  public nanny?: Ref<Nanny>;

  public formSchema;
  public values;
  public isFieldDirty;

  public loading = ref<boolean>(false);
  public saved = ref<boolean>(false);
  public errors = ref<Record<string, string[]>>({});

  // funciones de envío
  public saveCareer: (e?: Event) => void;
  public updateCareer: (e?: Event) => void;

  constructor(nanny: Nanny, career?: Career) {
    if (career) {
      this.career = ref<Career>(JSON.parse(JSON.stringify(career)));
    }

    // esquema de validación
    this.formSchema = toTypedSchema(
      z.object({
        name: z
          .string()
          .nonempty("El nombre es obligatorio")
          .min(2, "Nombre muy corto")
          .max(255, "Nombre demasiado largo"),
        area: z
          .string()
          .nonempty("El área es obligatoria")
          .max(255, "El área no puede exceder 255 caracteres"),
        nanny_id: z.number().optional(),
        degree: z.string().max(255, "El grado no puede exceder 255 caracteres").optional(),
        status: z.string().max(255, "El estado no puede exceder 255 caracteres").optional(),
        institution: z.string().max(255, "La institución no puede exceder 255 caracteres").optional(),
      })
    );

    const { values, isFieldDirty, handleSubmit } = useForm({
      validationSchema: this.formSchema,
      initialValues: {
        name: career ? career.name : "",
        area: career ? career.area : "",
        nanny_id: career?.pivot?.nanny_id || nanny?.id || undefined, 
        degree: career?.pivot?.degree ?? "",
        status: career?.pivot?.status ?? "",
        institution: career?.pivot?.institution ?? "",
      },
    });

    this.values = values;
    this.isFieldDirty = isFieldDirty;

    // Crear
    this.saveCareer = handleSubmit(async (values) => {
      this.loading.value = true;
      this.errors.value = {};

      const form = useInertiaForm(values);

      form.post(route("careers.store"), {
        onSuccess: () => {
          this.saved.value = true;
        },
        onError: (errors: Record<string, any>) => {
          const normalized: Record<string, string[]> = {};
          Object.entries(errors).forEach(([key, value]) => {
            normalized[key] = Array.isArray(value) ? value : [value];
          });
          this.errors.value = normalized;
        },
        onFinish: () => {
          this.loading.value = false;
        },
      });
    });

    // Editar
    this.updateCareer = handleSubmit(async (values) => {
      if (!this.career?.value?.id) return;

      this.loading.value = true;
      this.errors.value = {};

      const form = useInertiaForm(values);
      console.log(form);

      form.patch(route("careers.update", this.career.value.id), {
        onSuccess: () => {
          this.saved.value = true;
        },
        onError: (errors: Record<string, any>) => {
          const normalized: Record<string, string[]> = {};
          Object.entries(errors).forEach(([key, value]) => {
            normalized[key] = Array.isArray(value) ? value : [value];
          });
          this.errors.value = normalized;
        },
        onFinish: () => {
          this.loading.value = false;
        },
      });
    });
  }
}

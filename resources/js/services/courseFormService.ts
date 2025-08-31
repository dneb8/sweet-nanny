// CourseFormService.ts
import { ref, Ref } from "vue";
import { toTypedSchema } from "@vee-validate/zod";
import { useForm } from "vee-validate";
import { useForm as useInertiaForm } from "@inertiajs/vue3";
import * as z from "zod";
import { Course } from "@/types/Course";

export class CourseFormService {
  public course?: Ref<Course>;

  public formSchema;
  public values;
  public isFieldDirty;

  public loading = ref<boolean>(false);
  public saved = ref<boolean>(false);
  public errors = ref<Record<string, string[]>>({});

  // funciones de envío
  public saveCourse: (e?: Event) => void;
  public updateCourse: (e?: Event) => void;

constructor(course?: Course) {
    if (course) {
      this.course = ref<Course>(JSON.parse(JSON.stringify(course)));
    }

    // esquema de validación
    this.formSchema = toTypedSchema(
      z.object({
        name: z
          .string()
          .nonempty("El nombre es obligatorio")
          .min(2, "Nombre muy corto")
          .max(255, "Nombre demasiado largo"),
        organization: z
          .string()
          .max(255, "La organización no puede exceder 255 caracteres")
          .nonempty("La organización es obligatoria"),
        date: z.string().nonempty("La fecha es obligatoria"),
        nanny_id: z.number().optional()
      })
    );

    const { values, isFieldDirty, handleSubmit } = useForm({
      validationSchema: this.formSchema,
      initialValues: {
        name: course ? course.name : "",
        organization: course ? course.organization : "",
        date: course ? course.date : "",
        nanny_id: course ? course?.nanny_id : undefined,
      },
    });

    this.values = values;
    this.isFieldDirty = isFieldDirty;

    // Crear
    this.saveCourse = handleSubmit(async (values) => {
      this.loading.value = true;
      this.errors.value = {};

      const form = useInertiaForm(values);

      form.post(route("courses.store"), {
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
    this.updateCourse = handleSubmit(async (values) => {
      if (!this.course?.value?.id) return;

      this.loading.value = true;
      this.errors.value = {};

      const form = useInertiaForm(values);
      console.log(form);

      form.patch(route("courses.update", this.course.value.id), {
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

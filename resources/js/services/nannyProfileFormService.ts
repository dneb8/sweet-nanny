import { ref, type Ref } from "vue";
import { toTypedSchema } from "@vee-validate/zod";
import { useForm } from "vee-validate";
import { useForm as useInertiaForm } from "@inertiajs/vue3";
import * as z from "zod";
import type { Nanny } from "@/types/Nanny";
import { toast } from "vue-sonner";

export class NannyProfileFormService {
  public nanny?: Ref<Nanny>;

  public formSchema;
  public values;
  public isFieldDirty;

  public loading = ref<boolean>(false);
  public saved = ref<boolean>(false);
  public errors = ref<Record<string, string[]>>({});

  public updateProfile: (e?: Event) => void;

  constructor(nanny?: Nanny) {
    if (nanny) {
      this.nanny = ref<Nanny>(JSON.parse(JSON.stringify(nanny)));
    }

    // esquema de validación
    this.formSchema = toTypedSchema(
      z.object({
        bio: z.string().optional(),
        start_date: z
          .string()
          .nonempty("La fecha de inicio es obligatoria")
          .refine((dateStr) => {
            const selected = new Date(dateStr);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            return selected <= today;
          }, "La fecha no puede ser posterior a hoy"),
        nanny_id: z.number().optional(),
      })
    );

    const { values, isFieldDirty, handleSubmit } = useForm({
      validationSchema: this.formSchema,
      initialValues: {
        bio: nanny?.bio ?? "",
        start_date: nanny?.start_date ?? "",
        nanny_id: nanny?.id ?? undefined,
      },
    });

    this.values = values;
    this.isFieldDirty = isFieldDirty;

    this.updateProfile = handleSubmit(async (vals) => {
      if (!this.nanny?.value) return;

      this.loading.value = true;
      this.errors.value = {};
      this.saved.value = false;

      const form = useInertiaForm(vals);

      form.patch(`/nannies/${this.nanny.value.ulid}/profile`, {
        preserveScroll: true,
        onSuccess: () => {
          this.saved.value = true;
          toast.success("Perfil actualizado correctamente", {
            style: {
              background: "#E9D5FF",
              color: "#6B21A8",
              fontWeight: "500",
            },
          });
        },
        onError: (errs: Record<string, any>) => {
          const normalized: Record<string, string[]> = {};
          Object.entries(errs).forEach(([key, value]) => {
            normalized[key] = Array.isArray(value) ? value : [value];
          });
          this.errors.value = normalized;

          toast.error("No se pudieron guardar los cambios.", {
            style: {
              background: "#FECACA",
              color: "#991B1B",
              fontWeight: "500",
            },
          });
        },
        onFinish: () => {
          this.loading.value = false;
        },
      });
    });
  }
}

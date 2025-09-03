// AddressFormService.ts
import { ref, Ref } from "vue";
import { toTypedSchema } from "@vee-validate/zod";
import { useForm } from "vee-validate";
import { useForm as useInertiaForm } from "@inertiajs/vue3";
import * as z from "zod";
import { Address } from "@/types/Address";

export class AddressFormService {
  public address?: Ref<Address>;

  public formSchema;
  public values;
  public isFieldDirty;

  public loading = ref<boolean>(false);
  public saved = ref<boolean>(false);
  public errors = ref<Record<string, string[]>>({});

  // funciones de envío
  public saveAddress: (e?: Event) => void;
  public updateAddress: (e?: Event) => void;

  constructor(address?: Address) {
    if (address) {
      this.address = ref<Address>(JSON.parse(JSON.stringify(address)));
    }

    // esquema de validación
    this.formSchema = toTypedSchema(
      z.object({
        postal_code: z.string().nonempty("El código postal es obligatorio").max(10, "Código postal demasiado largo"),
        street: z.string().nonempty("La calle es obligatoria").max(255, "Calle demasiado larga"),
        neighborhood: z.string().nonempty("La colonia es obligatoria").max(255, "Colonia demasiado larga"),
        type: z.string().nonempty("El tipo de dirección es obligatorio"),
        other_type: z.string().max(255, "Otro tipo demasiado largo").optional(),
        internal_number: z.string().max(50, "Número interno demasiado largo").optional(),
        nanny_id: z.number().optional(),
      })
    );

    const { values, isFieldDirty, handleSubmit } = useForm({
      validationSchema: this.formSchema,
      initialValues: {
        postal_code: address ? address.postal_code : "",
        street: address ? address.street : "",
        neighborhood: address ? address.neighborhood : "",
        type: address ? address.type : "",
        other_type: address ? address.other_type : "",
        internal_number: address ? address.internal_number : "",
        nanny_id: address ? address.nanny_id : undefined,
      },
    });

    this.values = values;
    this.isFieldDirty = isFieldDirty;

    // Crear
    this.saveAddress = handleSubmit(async (values) => {
      this.loading.value = true;
      this.errors.value = {};

      const form = useInertiaForm(values);
      console.log(form)

      form.post(route("addresses.store"), {
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
    this.updateAddress = handleSubmit(async (values) => {
      if (!this.address?.value?.id) return;

      this.loading.value = true;
      this.errors.value = {};

      const form = useInertiaForm(values);
      console.log("Updating Address:", form);

      form.patch(route("addresses.update", this.address.value.id), {
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

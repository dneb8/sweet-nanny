// resources/js/services/QualityFormService.ts
import { ref, Ref } from "vue";
import axios from "axios";

// Definimos un tipo más seguro para Nanny
export interface NannyType {
  id?: string | number;
  qualities?: Array<{ name: string } | string>;
  [key: string]: any; // permite otras propiedades
}

export class QualityFormService {
  public nanny: Ref<NannyType>;
  public values: { qualities: string[] };
  public loading = ref(false);
  public errores = ref<Record<string, string[]>>({});

  private cfg = {
    headers: {
      Accept: "application/json",
      "X-Requested-With": "XMLHttpRequest",
    },
  };

  constructor(nanny: NannyType) {
    // Inicializamos la nanny de manera segura
    this.nanny = ref<NannyType>(JSON.parse(JSON.stringify(nanny ?? {})));

    this.values = {
      qualities: nanny?.qualities?.map(q =>
        typeof q === "string" ? q : q.name
      ) ?? [],
    };
  }

  // Guardar cambios en backend
  async guardar(): Promise<boolean> {
    if (!this.nanny.value?.id) {
      console.error("No hay ID de niñera:", this.nanny.value);
      return false;
    }

    this.loading.value = true;
    this.errores.value = {};

    const url = `/nannies/${this.nanny.value.id}/async-qualities`;
    console.log("POST a URL:", url, "con data:", this.values.qualities);

    try {
      const res = await axios.post(url, { qualities: this.values.qualities }, this.cfg);
      console.log("Respuesta del backend:", res.data);

      // Actualizamos el objeto nanny con las cualidades nuevas
      this.nanny.value.qualities = res.data.qualities.map((q: string) => ({ name: q }));
      return true;
    } catch (err: any) {
      console.error("Error en axios.post:", err.response?.data ?? err);
      if (err?.response?.data?.errors) {
        this.errores.value = err.response.data.errors;
      }
      return false;
    } finally {
      this.loading.value = false;
    }
  }

  // Agregar una cualidad (no guarda automáticamente, solo modifica local)
  addQuality(q: string) {
    if (!this.values.qualities.includes(q)) {
      this.values.qualities.push(q);
    }
  }

  // Eliminar una cualidad (no guarda automáticamente, solo modifica local)
  removeQuality(q: string) {
    this.values.qualities = this.values.qualities.filter(item => item !== q);
  }
}

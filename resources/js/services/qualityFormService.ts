// resources/js/services/QualityFormService.ts
import { ref, Ref } from "vue";
import axios from "axios";
import type { Nanny } from "@/types/Nanny";

export class QualityFormService {
  public nanny: Ref<Nanny>;
  public values: { qualities: string[] };
  public loading = ref(false);
  public errores = ref<Record<string, string[]>>({});

  private cfg = {
    headers: {
      Accept: "application/json",
      "X-Requested-With": "XMLHttpRequest",
    },
  };

  constructor(nanny: Nanny) {
    this.nanny = ref<Nanny>(JSON.parse(JSON.stringify(nanny)));

    this.values = {
      qualities: nanny?.qualities?.map(q => (typeof q === "string" ? q : q.name)) ?? [],
    };
  }

  // Guardar cambios en backend
  async guardar(): Promise<boolean> {
    if (!this.nanny.value?.id) return false;

    this.loading.value = true;
    this.errores.value = {};

    const url = `/nannies/${this.nanny.value.id}/async-qualities`;

    try {
      const res = await axios.post(url, { qualities: this.values.qualities }, this.cfg);
      this.nanny.value.qualities = res.data.qualities.map((q: string) => ({ name: q }));
      return true;
    } catch (err: any) {
      if (err?.response?.data?.errors) {
        this.errores.value = err.response.data.errors;
      }
      return false;
    } finally {
      this.loading.value = false;
    }
  }

  async addQuality(q: string) {
    if (!this.values.qualities.includes(q)) {
      this.values.qualities.push(q);
      await this.guardar();
    }
  }

  async removeQuality(q: string) {
    this.values.qualities = this.values.qualities.filter(item => item !== q);
    await this.guardar();
  }
}

import { Nanny } from "./Nanny";

export interface Career {
  id?: number;
  name: string;
  area: string;
  nanny_id?: number; // opcional, porque la relación es N:N

  // Relación
  nanny?: Nanny;

  // Campos del pivot
  pivot?: {
    nanny_id: number;
    career_id: number;
    degree?: string;
    status?: string;
    institution?: string;
    created_at?: string;
    updated_at?: string;
  };
}

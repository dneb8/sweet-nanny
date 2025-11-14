// getUserInitials.ts
import type { User } from "@/types/User";
import { toUpper } from "lodash";

/**
 * Devuelve iniciales robustas para un usuario:
 * - Preferencia: primera letra de name + primera letra de surnames
 * - Fallback: primeras dos iniciales de las primeras dos palabras de name
 * - Si solo hay una, usa una sola inicial
 * - Si no hay datos válidos, retorna "?"
 */
export function getUserInitials(user?: Partial<User> | null): string {
  // Normaliza strings (trim y vacío -> undefined)
  const norm = (v?: unknown) => {
    const s = typeof v === "string" ? v.trim() : "";
    return s.length ? s : undefined;
  };

  const name = norm(user?.name);
  const surnames = norm(user?.surnames);

  // Caso ideal: name + surnames
  const a = name?.slice(0, 1) ?? "";
  const b = surnames?.slice(0, 1) ?? "";

  if (a && b) return toUpper(a + b);

  // Fallback: tomar de name (puede venir con nombres y apellidos combinados)
  if (name) {
    const parts = name.split(/\s+/).filter(Boolean);
    if (parts.length >= 2) {
      return toUpper(parts[0].slice(0, 1) + parts[1].slice(0, 1));
    }
    // Solo una palabra disponible
    return toUpper(parts[0].slice(0, 1));
  }

  // Fallback final: intentar con surnames solo
  if (surnames) {
    const parts = surnames.split(/\s+/).filter(Boolean);
    if (parts.length >= 2) {
      return toUpper(parts[0].slice(0, 1) + parts[1].slice(0, 1));
    }
    return toUpper(parts[0].slice(0, 1));
  }

  // Sin datos
  return "?";
}

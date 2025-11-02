// utils/formatExperienceFrom.ts
export function formatExperienceFrom(startDate: string): string {
  if (!startDate) return 'Sin fecha';

  const start = new Date(startDate);
  const now = new Date();

  // Diferencia en años y meses
  let years = now.getFullYear() - start.getFullYear();
  let months = now.getMonth() - start.getMonth();
  let days = now.getDate() - start.getDate();

  if (days < 0) {
    months -= 1;
    days += new Date(now.getFullYear(), now.getMonth(), 0).getDate();
  }
  if (months < 0) {
    years -= 1;
    months += 12;
  }

  // Resultado en formato legible
  if (years <= 0 && months <= 0) return 'Nueva';
  const yearStr = years > 0 ? `${years} año${years > 1 ? 's' : ''}` : '';
  const monthStr = months > 0 ? `${months} mes${months > 1 ? 'es' : ''}` : '';

  return [yearStr, monthStr].filter(Boolean).join(' y ');
}

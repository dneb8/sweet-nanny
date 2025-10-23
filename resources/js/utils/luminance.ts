/**
 * Calcula la luminance para determinar si el background es claro u oscuro.
 * @param hexColor string
 * @returns boolean
 */
export function isDarkColor (hexColor: string) {
    // Convierte color de hex a RGB
    hexColor = hexColor.replace('#', '');
    let r = parseInt(hexColor.substring(0, 2), 16);
    let g = parseInt(hexColor.substring(2, 4), 16);
    let b = parseInt(hexColor.substring(4, 6), 16);

    // Normaliza los valores RGB de [0, 1]
    const normalizedR = r / 255;
    const normalizedG = g / 255;
    const normalizedB = b / 255;

    // Calcula la luminance
    const luminance =
    0.2126 * normalizedR +
    0.7152 * normalizedG +
    0.0722 * normalizedB;

    // Si la luminance es menor a 0.5 se considera oscuro
    return luminance < 0.5;
}

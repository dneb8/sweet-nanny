// Enum values matching PHP backend
export enum AddressTypeEnum {
    FRACCIONAMIENTO = 'fraccionamiento',
    CASA = 'casa',
    EDIFICIO = 'edificio',
    DEPARTAMENTO = 'departamento',
    DUPLEX = 'dúplex',
    LOCAL = 'local',
    PARQUE_DIVERSIONES = 'parque_de_diversiones',
    HOTEL = 'hotel',
    SALON_FIESTAS = 'salon_de_fiestas',
    CONDOMINIO = 'condominio',
    OTRO = 'otro',
}

// Human-readable labels
export const AddressTypeLabels: Record<AddressTypeEnum, string> = {
    [AddressTypeEnum.FRACCIONAMIENTO]: 'Fraccionamiento',
    [AddressTypeEnum.CASA]: 'Casa',
    [AddressTypeEnum.EDIFICIO]: 'Edificio',
    [AddressTypeEnum.DEPARTAMENTO]: 'Departamento',
    [AddressTypeEnum.DUPLEX]: 'Dúplex',
    [AddressTypeEnum.LOCAL]: 'Local',
    [AddressTypeEnum.PARQUE_DIVERSIONES]: 'Parque de diversiones',
    [AddressTypeEnum.HOTEL]: 'Hotel',
    [AddressTypeEnum.SALON_FIESTAS]: 'Salón de fiestas',
    [AddressTypeEnum.CONDOMINIO]: 'Condominio',
    [AddressTypeEnum.OTRO]: 'Otro',
};

// Get label for a specific type
export function getAddressTypeLabel(type: AddressTypeEnum): string {
    return AddressTypeLabels[type];
}

// Get all type values
export const ADDRESS_TYPE_VALUES = Object.values(AddressTypeEnum) as AddressTypeEnum[];

// Get all labels as Record<string, string>
export function getAddressTypeLabelsRecord(): Record<string, string> {
    return ADDRESS_TYPE_VALUES.reduce((acc, v) => {
        acc[v] = AddressTypeLabels[v];
        return acc;
    }, {} as Record<string, string>);
}

// Get label from string (useful for backend data)
export function getAddressTypeLabelByString(value: string): string {
    const match = (Object.values(AddressTypeEnum) as string[]).find(v => v === value);
    return match ? AddressTypeLabels[match as AddressTypeEnum] : 'Tipo desconocido';
}

// Type guard: is the string a valid AddressTypeEnum?
export function isAddressType(value: string): value is AddressTypeEnum {
    return (Object.values(AddressTypeEnum) as string[]).includes(value);
}

// Badge classes for address types (Tailwind + dark mode)
export function getAddressTypeBadgeClass(type: AddressTypeEnum | string): string {
    switch (type) {
        case AddressTypeEnum.FRACCIONAMIENTO:
            return 'bg-blue-200/70 text-blue-800 dark:bg-blue-600/50 dark:text-blue-200';
        case AddressTypeEnum.CASA:
            return 'bg-green-200/70 text-green-800 dark:bg-green-600/50 dark:text-green-200';
        case AddressTypeEnum.EDIFICIO:
            return 'bg-purple-200/70 text-purple-800 dark:bg-purple-600/50 dark:text-purple-200';
        case AddressTypeEnum.DEPARTAMENTO:
            return 'bg-orange-200/70 text-orange-800 dark:bg-orange-600/50 dark:text-orange-200';
        case AddressTypeEnum.DUPLEX:
            return 'bg-pink-200/70 text-pink-800 dark:bg-pink-600/50 dark:text-pink-200';
        case AddressTypeEnum.LOCAL:
            return 'bg-teal-200/70 text-teal-800 dark:bg-teal-600/50 dark:text-teal-200';
        case AddressTypeEnum.PARQUE_DIVERSIONES:
            return 'bg-fuchsia-200/70 text-fuchsia-800 dark:bg-fuchsia-600/50 dark:text-fuchsia-200';
        case AddressTypeEnum.HOTEL:
            return 'bg-cyan-200/70 text-cyan-800 dark:bg-cyan-600/50 dark:text-cyan-200';
        case AddressTypeEnum.SALON_FIESTAS:
            return 'bg-rose-200/70 text-rose-800 dark:bg-rose-600/50 dark:text-rose-200';
        case AddressTypeEnum.CONDOMINIO:
            return 'bg-indigo-200/70 text-indigo-800 dark:bg-indigo-600/50 dark:text-indigo-200';
        case AddressTypeEnum.OTRO:
            return 'bg-gray-200/70 text-gray-800 dark:bg-gray-600/50 dark:text-gray-200';
        default:
            return 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200';
    }
}

// Options ready for <Select> or Dropdowns
export const ADDRESS_TYPE_OPTIONS = ADDRESS_TYPE_VALUES.map(v => ({
    value: v,
    label: AddressTypeLabels[v],
}));

// Legacy support - keep TypeEnum for backward compatibility
export const TypeEnum = {
    FRACCIONAMIENTO: AddressTypeEnum.FRACCIONAMIENTO,
    CASA: AddressTypeEnum.CASA,
    EDIFICIO: AddressTypeEnum.EDIFICIO,
    DEPARTAMENTO: AddressTypeEnum.DEPARTAMENTO,
    DUPLEX: AddressTypeEnum.DUPLEX,
    LOCAL: AddressTypeEnum.LOCAL,
    PARQUE_DIVERSIONES: AddressTypeEnum.PARQUE_DIVERSIONES,
    HOTEL: AddressTypeEnum.HOTEL,
    SALON_FIESTAS: AddressTypeEnum.SALON_FIESTAS,
    CONDOMINIO: AddressTypeEnum.CONDOMINIO,
    OTRO: AddressTypeEnum.OTRO,
    labels: getAddressTypeLabelsRecord,
};

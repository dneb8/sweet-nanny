// Matches ZoneEnum.php
export enum ZoneEnum {
    GUADALAJARA = 'guadalajara',
    ZAPOPAN = 'zapopan',
    TLAQUEPAQUE = 'tlaquepaque',
    TLAJOMULCO = 'tlajomulco',
    TONALA = 'tonala',
}

// Readable labels (identical to PHP::label())
export const ZoneLabels: Record<ZoneEnum, string> = {
    [ZoneEnum.GUADALAJARA]: 'Guadalajara',
    [ZoneEnum.ZAPOPAN]: 'Zapopan',
    [ZoneEnum.TLAQUEPAQUE]: 'Tlaquepaque',
    [ZoneEnum.TLAJOMULCO]: 'Tlajomulco',
    [ZoneEnum.TONALA]: 'Tonalá',
};

// Zone badge colors (similar to status badges)
export const ZoneBadgeClasses: Record<ZoneEnum, string> = {
    [ZoneEnum.GUADALAJARA]: 'bg-blue-200/70 text-blue-800 dark:bg-blue-600/50 dark:text-blue-200',
    [ZoneEnum.ZAPOPAN]: 'bg-purple-200/70 text-purple-800 dark:bg-purple-600/50 dark:text-purple-200',
    [ZoneEnum.TLAQUEPAQUE]: 'bg-orange-200/70 text-orange-800 dark:bg-orange-600/50 dark:text-orange-200',
    [ZoneEnum.TLAJOMULCO]: 'bg-teal-200/70 text-teal-800 dark:bg-teal-600/50 dark:text-teal-200',
    [ZoneEnum.TONALA]: 'bg-pink-200/70 text-pink-800 dark:bg-pink-600/50 dark:text-pink-200',
};

// Get label from enum value
export function getZoneLabel(zone: ZoneEnum | string): string {
    const match = (Object.values(ZoneEnum) as string[]).find((v) => v === zone);
    return match ? ZoneLabels[match as ZoneEnum] : zone;
}

// Get badge class from enum value
export function getZoneBadgeClass(zone: ZoneEnum | string): string {
    const match = (Object.values(ZoneEnum) as string[]).find((v) => v === zone);
    return match ? ZoneBadgeClasses[match as ZoneEnum] : 'bg-gray-200/70 text-gray-800 dark:bg-gray-600/50 dark:text-gray-200';
}

// Type guard
export function isZone(value: string): value is ZoneEnum {
    return (Object.values(ZoneEnum) as string[]).includes(value);
}

// Options for Select/Dropdown
export const ZONE_OPTIONS = Object.values(ZoneEnum).map((v) => ({
    value: v,
    label: ZoneLabels[v as ZoneEnum],
}));

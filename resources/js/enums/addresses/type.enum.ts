export const TypeEnum = {
  FRACCIONAMIENTO: 'fraccionamiento',
  CASA: 'casa',
  EDIFICIO: 'edificio',
  DEPARTAMENTO: 'departamento',
  DUPLEX: 'dúplex',
  LOCAL: 'local',
  PARQUE_DIVERSIONES: 'parque_de_diversiones',
  HOTEL: 'hotel',
  SALON_FIESTAS: 'salon_de_fiestas',
  CONDOMINIO: 'condominio',
  OTRO: 'otro',

  labels(): Record<string, string> {
    return {
      fraccionamiento: 'Fraccionamiento',
      casa: 'Casa',
      edificio: 'Edificio',
      departamento: 'Departamento',
      dúplex: 'Dúplex',
      local: 'Local',
      parque_de_diversiones: 'Parque de diversiones',
      hotel: 'Hotel',
      salon_de_fiestas: 'Salón de fiestas',
      condominio: 'Condominio',
      otro: 'Otro',
    }
  }
}

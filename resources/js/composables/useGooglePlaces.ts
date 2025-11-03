import { ref, type Ref } from 'vue';

export interface AddressSuggestion {
  label: string;
  street: string;
  external_number: string;
  neighborhood: string;
  municipality: string;
  state: string;
  postalCode: string;
  latitude: number;
  longitude: number;
  fullAddress: string;
  placeId: string;
}

// ---- Tipos/globales Google ----
declare global {
  interface Window {
    google: typeof google;
    initGooglePlaces: () => void;
    __gmapsLoadPromise?: Promise<void>;
    __gmapsLoaded?: boolean;
  }
}
declare const google: any;

// ---- Municipios permitidos ZMG ----
const ALLOWED_MUNICIPALITIES = new Set([
  'guadalajara',
  'zapopan',
  'tlaquepaque',
  'tlajomulco',
  'tonala',
]);

function normalizeMunicipality(name: string): string {
  return name
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '');
}

export function useGooglePlaces() {
  const loading = ref(false);
  const suggestions: Ref<AddressSuggestion[]> = ref([]);
  const error: Ref<string | null> = ref(null);
  const autocompleteService: Ref<any> = ref(null);
  const geocoder: Ref<any> = ref(null);

  const apiKey = import.meta.env.VITE_GMAPS_API_KEY;

  // Bounds aprox de ZMG
  const ZMG_BOUNDS = {
    south: 20.5,
    west: -103.5,
    north: 20.8,
    east: -103.2,
  };

  // -------- CARGA ROBUSTA DEL SDK --------
  const ensureGoogleMapsLoaded = (): Promise<void> => {
    if (!apiKey) {
      const msg = 'Google Maps API key is not configured';
      error.value = msg;
      return Promise.reject(new Error('VITE_GMAPS_API_KEY environment variable is not set'));
    }

    // Si ya está cargado completamente:
    if (window.google?.maps?.places) return Promise.resolve();

    // Si ya hay una promesa de carga, reúsala
    if (window.__gmapsLoadPromise) return window.__gmapsLoadPromise;

    // Crea la promesa y colócala en window para que sea singleton
    window.__gmapsLoadPromise = new Promise<void>((resolve, reject) => {
      // Define callback global (llamada por el script con &callback=)
      window.initGooglePlaces = () => {
        window.__gmapsLoaded = true;
        // Verifica que places esté realmente presente
        if (window.google?.maps?.places) {
          resolve();
        } else {
          reject(new Error('Google Maps loaded but places library missing'));
        }
      };

      // Si ya hay un script pero sin callback listo, no dupliques:
      const existing = document.querySelector<HTMLScriptElement>('script[data-gmaps="sdk"]');
      if (existing) {
        // Puede pasar que el script ya esté pero no haya disparado callback aún.
        // Esperar un poco y checar de nuevo:
        const checkReady = () => {
          if (window.google?.maps?.places) {
            resolve();
          } else if (window.__gmapsLoaded) {
            reject(new Error('Google Maps loaded without places library'));
          } else {
            setTimeout(checkReady, 50);
          }
        };
        checkReady();
        return;
      }

      // Inyectar script con places y callback
      const script = document.createElement('script');
      script.setAttribute('data-gmaps', 'sdk');
      const params = new URLSearchParams({
        key: apiKey,
        libraries: 'places',
        language: 'es-MX',
        region: 'MX',
        callback: 'initGooglePlaces',
      });
      script.src = `https://maps.googleapis.com/maps/api/js?${params.toString()}`;
      script.async = true;
      script.defer = true;

      script.onerror = () => {
        error.value = 'Failed to load Google Maps';
        reject(new Error('Failed to load Google Maps script'));
      };

      document.head.appendChild(script);
    });

    return window.__gmapsLoadPromise;
  };

  const initCoreServices = () => {
    // Verifica que la lib Places esté disponible
    if (!window.google?.maps?.places) {
      throw new Error('Google Maps Places library not available after load');
    }
    if (!autocompleteService.value) {
      autocompleteService.value = new google.maps.places.AutocompleteService();
    }
    if (!geocoder.value) {
      geocoder.value = new google.maps.Geocoder();
    }
  };

  /**
   * Inicializa servicios (idempotente)
   */
  const initServices = async (): Promise<void> => {
    try {
      await ensureGoogleMapsLoaded();
      initCoreServices();
    } catch (e: any) {
      error.value = e?.message || 'Failed to initialize Google Maps';
      throw e;
    }
  };

  /**
   * Parse Google Place Details into our address format
   */
  const parseAddressComponents = (addressComponents: any[], geometry: any): AddressSuggestion | null => {
    const components: any = {};

    addressComponents.forEach((component: any) => {
      const types = component.types;
      const value = component.long_name;

      if (types.includes('street_number')) {
        components.external_number = value;
      }
      if (types.includes('route')) {
        components.street = value;
      }
      if (types.includes('sublocality_level_1') || types.includes('neighborhood')) {
        components.neighborhood = value;
      }
      if (types.includes('locality') || types.includes('administrative_area_level_2')) {
        components.municipality = value;
      }
      if (types.includes('administrative_area_level_1')) {
        components.state = value;
      }
      if (types.includes('postal_code')) {
        components.postalCode = value;
      }
    });

    const streetParts = [];
    if (components.street) streetParts.push(components.street);
    if (components.external_number) streetParts.push(components.external_number);
    const fullStreet = streetParts.join(' ');

    const addressParts = [fullStreet, components.neighborhood, components.municipality, components.state]
      .filter(Boolean)
      .join(', ');

    return {
      label: addressParts || 'Dirección',
      street: components.street || '',
      external_number: components.external_number || '',
      neighborhood: components.neighborhood || '',
      municipality: components.municipality || '',
      state: components.state || '',
      postalCode: components.postalCode || '',
      latitude: geometry.location.lat(),
      longitude: geometry.location.lng(),
      fullAddress: addressParts,
      placeId: '',
    };
  };

  /**
   * Get place details by place_id
   */
  const getPlaceDetails = async (placeId: string): Promise<AddressSuggestion | null> => {
    return new Promise((resolve, reject) => {
      if (!window.google?.maps?.places) {
        reject(new Error('Google Maps Places not loaded'));
        return;
      }

      // Usar un div temporal para PlacesService
      const div = document.createElement('div');
      const service = new google.maps.places.PlacesService(div);

      service.getDetails(
        {
          placeId,
          fields: ['address_components', 'geometry', 'formatted_address'],
        },
        (place: any, status: any) => {
          if (status === google.maps.places.PlacesServiceStatus.OK && place) {
            const suggestion = parseAddressComponents(place.address_components, place.geometry);
            if (suggestion) {
              suggestion.placeId = placeId;
              resolve(suggestion);
            } else {
              reject(new Error('Failed to parse address'));
            }
          } else {
            reject(new Error(`Places service failed: ${status}`));
          }
        }
      );
    });
  };

  /**
   * Search for places using Google Places Autocomplete
   */
  const searchPlaces = async (query: string, maxResults: number = 10): Promise<AddressSuggestion[]> => {
    if (!query || query.length < 3) {
      suggestions.value = [];
      return [];
    }

    loading.value = true;
    error.value = null;

    try {
      // Inicializa (idempotente)
      if (!window.google?.maps?.places || !autocompleteService.value) {
        await initServices();
      }

      const bounds = new google.maps.LatLngBounds(
        new google.maps.LatLng(ZMG_BOUNDS.south, ZMG_BOUNDS.west),
        new google.maps.LatLng(ZMG_BOUNDS.north, ZMG_BOUNDS.east)
      );

      let predictions: any = await new Promise((resolve, reject) => {
        autocompleteService.value.getPlacePredictions(
          {
            input: query,
            componentRestrictions: { country: 'mx' },
            language: 'es-MX',
            locationBias: bounds,
            // sin filtro de types: permite direcciones y establecimientos
          },
          (results: any, status: any) => {
            if (status === google.maps.places.PlacesServiceStatus.OK) {
              resolve(results || []);
            } else if (status === google.maps.places.PlacesServiceStatus.ZERO_RESULTS) {
              resolve([]);
            } else {
              reject(new Error(`Autocomplete failed: ${status}`));
            }
          }
        );
      });

      // Fallback a QueryPredictions si no hubo resultados
      if (!predictions || predictions.length === 0) {
        predictions = await new Promise((resolve) => {
          autocompleteService.value.getQueryPredictions(
            {
              input: query,
              location: new google.maps.LatLng(
                parseFloat(import.meta.env.VITE_DEFAULT_CENTER_LAT || '20.6597'),
                parseFloat(import.meta.env.VITE_DEFAULT_CENTER_LNG || '-103.3496')
              ),
              radius: 50000,
            },
            (results: any, status: any) => {
              if (status === google.maps.places.PlacesServiceStatus.OK) {
                resolve(results || []);
              } else {
                resolve([]);
              }
            }
          );
        });
      }

      const results: AddressSuggestion[] = [];
      const limit = Math.min(predictions.length, maxResults * 2);

      for (let i = 0; i < limit && results.length < maxResults; i++) {
        try {
          const detail = await getPlaceDetails(predictions[i].place_id);
          if (detail) {
            const normalizedMunicipality = normalizeMunicipality(detail.municipality);
            if (ALLOWED_MUNICIPALITIES.has(normalizedMunicipality)) {
              detail.label = predictions[i].description;
              results.push(detail);
            }
          }
        } catch (err) {
          console.warn('Failed to get place details:', err);
        }
      }

      suggestions.value = results;
      return results;
    } catch (err: any) {
      error.value = err?.message || 'Error searching places';
      console.error('Google Places search error:', err);
      suggestions.value = [];
      return [];
    } finally {
      loading.value = false;
    }
  };

  const clearSuggestions = () => {
    suggestions.value = [];
    error.value = null;
  };

  return {
    loading,
    suggestions,
    error,
    searchPlaces,
    clearSuggestions,
    initServices,
  };
}

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

// Declare Google Maps types
declare global {
    interface Window {
        google: typeof google;
        initGooglePlaces: () => void;
    }
}
declare const google: any;

// Allowed municipalities in Guadalajara Metropolitan Area (ZMG)
const ALLOWED_MUNICIPALITIES = new Set([
    'guadalajara',
    'zapopan',
    'tlaquepaque',
    'tlajomulco',
    'tonala',
]);

/**
 * Normalize municipality name for comparison
 * Converts to lowercase and removes accents
 */
function normalizeMunicipality(name: string): string {
    return name
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, ''); // Remove diacritics
}

export function useGooglePlaces() {
    const loading = ref(false);
    const suggestions: Ref<AddressSuggestion[]> = ref([]);
    const error: Ref<string | null> = ref(null);
    const autocompleteService: Ref<any> = ref(null);
    const geocoder: Ref<any> = ref(null);

    const apiKey = import.meta.env.VITE_GMAPS_API_KEY;

    // Define bounds for Guadalajara Metropolitan Area for location bias
    // Southwest: 20.5, -103.5 | Northeast: 20.8, -103.2 (approximate ZMG bounds)
    const ZMG_BOUNDS = {
        south: 20.5,
        west: -103.5,
        north: 20.8,
        east: -103.2,
    };

    /**
     * Initialize Google Places services
     */
    const initServices = (): Promise<void> => {
        return new Promise((resolve, reject) => {
            if (!apiKey) {
                error.value = 'Google Maps API key is not configured';
                reject(new Error('VITE_GMAPS_API_KEY environment variable is not set'));
                return;
            }

            // Check if Google Maps is already loaded
            if (window.google && window.google.maps) {
                if (!autocompleteService.value) {
                    autocompleteService.value = new google.maps.places.AutocompleteService();
                }
                if (!geocoder.value) {
                    geocoder.value = new google.maps.Geocoder();
                }
                resolve();
                return;
            }

            // Load Google Maps script
            if (!document.querySelector('script[src*="maps.googleapis.com"]')) {
                const script = document.createElement('script');
                script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&libraries=places&language=es-MX&region=MX`;
                script.async = true;
                script.defer = true;

                script.onload = () => {
                    autocompleteService.value = new google.maps.places.AutocompleteService();
                    geocoder.value = new google.maps.Geocoder();
                    resolve();
                };

                script.onerror = () => {
                    error.value = 'Failed to load Google Maps';
                    reject(new Error('Failed to load Google Maps script'));
                };

                document.head.appendChild(script);
            }
        });
    };

    /**
     * Parse Google Place Details into our address format
     */
    const parseAddressComponents = (addressComponents: any[], geometry: any): AddressSuggestion | null => {
        const components: any = {};

        // Extract address components
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

        // Build full address
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
            if (!window.google || !window.google.maps) {
                reject(new Error('Google Maps not loaded'));
                return;
            }

            // Create a temporary div for PlacesService
            const div = document.createElement('div');
            const service = new google.maps.places.PlacesService(div);

            service.getDetails(
                {
                    placeId: placeId,
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
            // Initialize services if not already done
            if (!autocompleteService.value) {
                await initServices();
            }

            // Get predictions - includes addresses, businesses, and establishments
            // Removed types: ['address'] to allow Starbucks, Oxxo, etc.
            // Use location bias with strict bounds to focus on ZMG
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
                        // No types restriction - allows addresses AND establishments
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

            // If no results from getPlacePredictions, try getQueryPredictions as fallback
            // This can find businesses and places when autocomplete returns nothing
            if (!predictions || predictions.length === 0) {
                predictions = await new Promise((resolve) => {
                    autocompleteService.value.getQueryPredictions(
                        {
                            input: query,
                            location: new google.maps.LatLng(
                                parseFloat(import.meta.env.VITE_DEFAULT_CENTER_LAT || '20.6597'),
                                parseFloat(import.meta.env.VITE_DEFAULT_CENTER_LNG || '-103.3496')
                            ),
                            radius: 50000, // 50km radius around Guadalajara
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

            // Get details for each prediction and filter by ZMG municipalities
            const results: AddressSuggestion[] = [];
            const limit = Math.min(predictions.length, maxResults * 2); // Fetch more to account for filtering

            for (let i = 0; i < limit && results.length < maxResults; i++) {
                try {
                    const detail = await getPlaceDetails(predictions[i].place_id);
                    if (detail) {
                        // Filter: Only accept addresses from ZMG municipalities
                        const normalizedMunicipality = normalizeMunicipality(detail.municipality);
                        if (ALLOWED_MUNICIPALITIES.has(normalizedMunicipality)) {
                            // Use the formatted description from prediction for label
                            detail.label = predictions[i].description;
                            results.push(detail);
                        }
                        // Skip results from municipalities outside ZMG
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

    /**
     * Clear suggestions
     */
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

import { LocationClient, SearchPlaceIndexForTextCommand, type Place } from '@aws-sdk/client-location';
import { ref, type Ref } from 'vue';

export interface AddressSuggestion {
    label: string;
    street: string;
    neighborhood: string;
    city?: string;
    state?: string;
    postalCode: string;
    latitude: number;
    longitude: number;
    fullAddress: string;
}

export function useAwsLocation() {
    const loading = ref(false);
    const suggestions: Ref<AddressSuggestion[]> = ref([]);
    const error: Ref<string | null> = ref(null);

    const placeIndexName = import.meta.env.VITE_AWS_LOCATION_PLACE_INDEX;
    const region = import.meta.env.VITE_AWS_REGION;
    const accessKeyId = import.meta.env.VITE_AWS_ACCESS_KEY_ID;
    const secretAccessKey = import.meta.env.VITE_AWS_SECRET_ACCESS_KEY;

    // Initialize AWS Location client
    const locationClient = new LocationClient({
        region: region || 'us-east-2',
        credentials: {
            accessKeyId: accessKeyId || '',
            secretAccessKey: secretAccessKey || '',
        },
    });

    /**
     * Parse AWS Place result into our address format
     */
    const parsePlace = (place: Place): AddressSuggestion | null => {
        if (!place.Geometry?.Point || !place.Label) {
            return null;
        }

        const [longitude, latitude] = place.Geometry.Point;
        const addressNumber = place.AddressNumber || '';
        const street = place.Street || '';
        const neighborhood = place.Neighborhood || place.SubRegion || '';
        const city = place.Municipality || '';
        const state = place.Region || '';
        const postalCode = place.PostalCode || '';

        // Build full street with number
        const fullStreet = addressNumber ? `${street} ${addressNumber}` : street;

        return {
            label: place.Label,
            street: fullStreet,
            neighborhood,
            city,
            state,
            postalCode,
            latitude,
            longitude,
            fullAddress: place.Label,
        };
    };

    /**
     * Search for places using AWS Location Service
     */
    const searchPlaces = async (query: string, maxResults: number = 10): Promise<AddressSuggestion[]> => {
        if (!query || query.length < 3) {
            suggestions.value = [];
            return [];
        }

        if (!placeIndexName) {
            error.value = 'AWS Location Place Index is not configured';
            console.error('VITE_AWS_LOCATION_PLACE_INDEX environment variable is not set');
            return [];
        }

        loading.value = true;
        error.value = null;

        try {
            const command = new SearchPlaceIndexForTextCommand({
                IndexName: placeIndexName,
                Text: query,
                MaxResults: maxResults,
                // Filter to Mexico addresses
                FilterCountries: ['MEX'],
            });

            const response = await locationClient.send(command);
            const results: AddressSuggestion[] = [];

            if (response.Results) {
                for (const result of response.Results) {
                    if (result.Place) {
                        const parsed = parsePlace(result.Place);
                        if (parsed) {
                            results.push(parsed);
                        }
                    }
                }
            }

            suggestions.value = results;
            return results;
        } catch (err: any) {
            error.value = err?.message || 'Error searching places';
            console.error('AWS Location search error:', err);
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
    };
}

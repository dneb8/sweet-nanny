<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch } from 'vue';
import maplibregl from 'maplibre-gl';
import 'maplibre-gl/dist/maplibre-gl.css';

interface Props {
    latitude: number;
    longitude: number;
    height?: string;
    zoom?: number;
    showMarker?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    height: '400px',
    zoom: 15,
    showMarker: true,
});

const mapContainer = ref<HTMLDivElement | null>(null);
let map: maplibregl.Map | null = null;
let marker: maplibregl.Marker | null = null;

const initMap = () => {
    if (!mapContainer.value) return;

    // Note: For production, you would use AWS Location Maps with proper authentication
    // For this implementation, we're using OpenStreetMap as a simple fallback
    // In production, use AWS Location Maps API with proper credentials
    map = new maplibregl.Map({
        container: mapContainer.value,
        style: {
            version: 8,
            sources: {
                'osm': {
                    type: 'raster',
                    tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                    tileSize: 256,
                    attribution: '&copy; OpenStreetMap Contributors',
                    maxzoom: 19,
                },
            },
            layers: [
                {
                    id: 'osm',
                    type: 'raster',
                    source: 'osm',
                },
            ],
        },
        center: [props.longitude, props.latitude],
        zoom: props.zoom,
    });

    if (props.showMarker) {
        marker = new maplibregl.Marker({ color: '#ef4444' })
            .setLngLat([props.longitude, props.latitude])
            .addTo(map);
    }

    map.addControl(new maplibregl.NavigationControl(), 'top-right');
};

const updateMapPosition = () => {
    if (!map) return;

    map.setCenter([props.longitude, props.latitude]);

    if (marker) {
        marker.setLngLat([props.longitude, props.latitude]);
    } else if (props.showMarker) {
        marker = new maplibregl.Marker({ color: '#ef4444' })
            .setLngLat([props.longitude, props.latitude])
            .addTo(map);
    }
};

onMounted(() => {
    initMap();
});

onUnmounted(() => {
    if (marker) {
        marker.remove();
    }
    if (map) {
        map.remove();
    }
});

watch(
    () => [props.latitude, props.longitude],
    () => {
        updateMapPosition();
    }
);
</script>

<template>
    <div class="rounded-lg overflow-hidden border border-border">
        <div ref="mapContainer" :style="{ height: height }" />
    </div>
</template>

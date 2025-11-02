<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch, computed } from "vue"

// Declara el objeto global 'google' para TypeScript
declare global {
  interface Window {
    google: typeof google
    initMapReady: () => void
  }
}
declare const google: any

interface Props {
  latitude: number | string | null | undefined
  longitude: number | string | null | undefined
  zoom?: number
  height?: string
  showMarker?: boolean

  // Opciones del botón "Ver en Google Maps"
  showOpenButton?: boolean
  buttonLabel?: string
  openInNewTab?: boolean
}
const props = withDefaults(defineProps<Props>(), {
  zoom: 15,
  height: "400px",
  showMarker: true,
  showOpenButton: true,
  buttonLabel: "Ver en Google Maps",
  openInNewTab: true,
})

const mapEl = ref<HTMLDivElement | null>(null)
let map: any = null
let marker: any = null
let scriptEl: HTMLScriptElement | null = null

// --- Helpers de coordenadas ---
function toNumber(value: unknown): number {
  const n =
    typeof value === "string" ? parseFloat(value) :
    typeof value === "number" ? value :
    NaN
  return Number.isFinite(n) ? n : NaN
}
function isFiniteLatLng(lat: number, lng: number) {
  return Number.isFinite(lat) && Math.abs(lat) <= 90 &&
         Number.isFinite(lng) && Math.abs(lng) <= 180
}

const latNum = computed(() => toNumber(props.latitude))
const lngNum = computed(() => toNumber(props.longitude))
const hasValidCoords = computed(() => isFiniteLatLng(latNum.value, lngNum.value))

// URL para abrir en Google Maps
const googleMapsUrl = computed(() => {
  if (!hasValidCoords.value) return "#"
  return `https://www.google.com/maps/search/?api=1&query=${latNum.value},${lngNum.value}`
})

// Lee la API key desde variables de entorno (Vite expone las que empiezan con VITE_)
const GMAPS_API_KEY = import.meta.env.VITE_GMAPS as string | undefined

function loadGoogleMaps(apiKey: string): Promise<void> {
  if ((window as any).google?.maps) return Promise.resolve()

  if (document.getElementById("gmaps-sdk")) {
    return new Promise((res) => { (window as any).initMapReady = () => res() })
  }

  return new Promise((resolve, reject) => {
    ;(window as any).initMapReady = () => resolve()
    scriptEl = document.createElement("script")
    scriptEl.id = "gmaps-sdk"
    scriptEl.async = true
    scriptEl.defer = true
    scriptEl.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&callback=initMapReady`
    scriptEl.onerror = (e) => {
      console.error("[GoogleMaps] Error cargando el SDK:", e)
      reject(e)
    }
    document.head.appendChild(scriptEl)
  })
}

function initMap() {
  if (!mapEl.value) return
  const center = hasValidCoords.value
    ? { lat: latNum.value, lng: lngNum.value }
    : { lat: 19.704, lng: -103.344 } // fallback visual

  map = new google.maps.Map(mapEl.value, {
    center,
    zoom: props.zoom,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: true,
  })

  if (props.showMarker && hasValidCoords.value) {
    marker = new google.maps.Marker({
      position: center,
      map,
    })
  }
}

function updatePosition() {
  if (!map) return
  if (!hasValidCoords.value) return
  const pos = { lat: latNum.value, lng: lngNum.value }
  map.setCenter(pos)
  if (typeof props.zoom === "number") map.setZoom(props.zoom)
  if (props.showMarker) {
    if (!marker) marker = new google.maps.Marker({ position: pos, map })
    else marker.setPosition(pos)
  } else if (marker) {
    marker.setMap(null)
    marker = null
  }
}

onMounted(async () => {
  if (!GMAPS_API_KEY) {
    console.warn("[GoogleMaps] VITE_GMAPS no está definido. Define VITE_GMAPS en tu .env/.env.local")
    return
  }
  try {
    await loadGoogleMaps(GMAPS_API_KEY)
    initMap()
  } catch (err) {
    console.error("[GoogleMaps] No se pudo inicializar el mapa. Revisa tu API key y restricciones.", err)
  }
})

onUnmounted(() => {
  if (marker) { marker.setMap(null); marker = null }
  map = null
})

watch(() => [latNum.value, lngNum.value, props.zoom, props.showMarker], () => {
  updatePosition()
})
</script>

<template>
  <div class="rounded-lg overflow-hidden border border-border" :style="{ height: props.height }">
    <!-- Barra superior con botón -->
    <div class="flex items-center justify-end gap-2 p-2 border-b border-border bg-background/80">
      <slot name="left-actions" />
      <div class="flex-1" />
      <slot name="right-actions" />
      <a
        v-if="props.showOpenButton"
        :href="googleMapsUrl"
        :target="props.openInNewTab ? '_blank' : undefined"
        :rel="props.openInNewTab ? 'noopener noreferrer' : undefined"
        class="inline-flex items-center gap-2 rounded-xl px-3 py-1.5 text-sm border border-input shadow-xs hover:shadow-sm transition disabled:opacity-50 disabled:pointer-events-none"
        :aria-disabled="!hasValidCoords"
        :class="hasValidCoords ? 'cursor-pointer bg-card hover:bg-accent' : 'cursor-not-allowed bg-muted'"
        :tabindex="hasValidCoords ? 0 : -1"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z"/>
        </svg>
        <span>{{ props.buttonLabel }}</span>
      </a>
    </div>

    <!-- Contenedor del mapa -->
    <div ref="mapEl" class="w-full h-[calc(100%-44px)]"></div>
  </div>
</template>

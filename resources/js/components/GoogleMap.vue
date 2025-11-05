<script setup lang="ts">
import { onMounted, onUnmounted, ref, watch, computed, nextTick } from "vue"

// --- Declaraciones globales ---
declare global {
  interface Window {
    google: typeof google
    initMapReady: () => void
    __AdvancedMarkerElement__?: any
  }
}
declare const google: any

// --- Props ---
interface Props {
  latitude: number | string | null | undefined
  longitude: number | string | null | undefined
  zoom?: number
  height?: string
  showMarker?: boolean
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

// --- Refs ---
const rootEl = ref<HTMLDivElement | null>(null)
const mapEl = ref<HTMLDivElement | null>(null)
const configError = ref<string | null>(null)
let map: any = null
let marker: any = null
let scriptEl: HTMLScriptElement | null = null

// --- Helpers ---
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

const googleMapsUrl = computed(() => {
  if (!hasValidCoords.value) return "#"
  return `https://www.google.com/maps/search/?api=1&query=${latNum.value},${lngNum.value}`
})

// --- ENV ---
const GMAPS_API_KEY = (import.meta.env.VITE_GMAPS || import.meta.env.VITE_GMAPS_API_KEY) as string | undefined
const GMAPS_MAP_ID = import.meta.env.VITE_GMAPS_MAP_ID as string | undefined

function validateConfig(): string | null {
  if (!GMAPS_API_KEY) return "Falta configurar VITE_GMAPS_API_KEY en .env"
  // MAP_ID es recomendable para estilos; no es obligatorio si hay fallback
  return null
}

// --- Marker helpers (con feature-detect y fallback) ---
function createMarker(position: google.maps.LatLngLiteral, mapInstance: any) {
  const AdvancedMarkerElement = (window as any).__AdvancedMarkerElement__
  if (AdvancedMarkerElement) {
    return new AdvancedMarkerElement({ position, map: mapInstance })
  }
  // Fallback a Marker clásico
  return new google.maps.Marker({ position, map: mapInstance })
}
function updateMarkerPosition(markerInstance: any, position: google.maps.LatLngLiteral) {
  if (markerInstance?.setPosition) {
    markerInstance.setPosition(position) // Marker clásico
  } else {
    markerInstance.position = position   // AdvancedMarker
  }
}
function removeMarker(markerInstance: any) {
  if (!markerInstance) return
  if (markerInstance?.setMap) {
    markerInstance.setMap(null) // Marker clásico
  } else {
    markerInstance.map = null   // AdvancedMarker
  }
}

// --- Cargar Google Maps SDK (moderno) ---
function loadGoogleMaps(apiKey: string): Promise<void> {
  if ((window as any).google?.maps?.importLibrary) return Promise.resolve()

  if (document.getElementById("gmaps-sdk")) {
    return new Promise((res) => { (window as any).initMapReady = () => res() })
  }

  return new Promise((resolve, reject) => {
    ;(window as any).initMapReady = () => resolve()
    scriptEl = document.createElement("script")
    scriptEl.id = "gmaps-sdk"
    scriptEl.async = true
    scriptEl.defer = true
    // v=weekly para garantizar versión reciente con Advanced Markers
    scriptEl.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&v=weekly&callback=initMapReady`
    scriptEl.onerror = (e) => reject(e)
    document.head.appendChild(scriptEl)
  })
}

// --- Inicializar mapa ---
async function initMap() {
  if (!mapEl.value) return
  await nextTick()

  // Carga módulos de la API moderna
  const { Map } = await google.maps.importLibrary('maps') as google.maps.MapsLibrary

  // Intenta cargar librería de markers avanzados; si falla, usamos fallback
  let AdvancedMarkerElement: any | null = null
  try {
    const markerLib = await google.maps.importLibrary('marker') as google.maps.MarkerLibrary
    AdvancedMarkerElement = markerLib?.AdvancedMarkerElement ?? null
  } catch {
    AdvancedMarkerElement = null
  }
  ;(window as any).__AdvancedMarkerElement__ = AdvancedMarkerElement

  const center = hasValidCoords.value
    ? { lat: latNum.value, lng: lngNum.value }
    : { lat: 19.704, lng: -103.344 }

  const mapConfig: google.maps.MapOptions = {
    center,
    zoom: props.zoom,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: true,
    // mapId es opcional; útil si quieres estilos/AdvancedMarker styling
    mapId: GMAPS_MAP_ID,
  }

  map = new Map(mapEl.value, mapConfig)

  // Ajustes de resize/center cuando termina de renderizar
  google.maps.event.addListenerOnce(map as any, "idle", () => {
    google.maps.event.trigger(map as any, "resize")
    ;(map as any).setCenter(center)
  })

  if (props.showMarker && hasValidCoords.value) {
    marker = createMarker(center, map)
  }
}

// --- Actualizar posición ---
function updatePosition() {
  if (!map || !hasValidCoords.value) return
  const pos = { lat: latNum.value, lng: lngNum.value }
  ;(map as any).setCenter(pos)
  if (typeof props.zoom === "number") (map as any).setZoom(props.zoom)
  if (props.showMarker) {
    if (!marker) marker = createMarker(pos, map)
    else updateMarkerPosition(marker, pos)
  } else if (marker) {
    removeMarker(marker)
    marker = null
  }
}

// --- Resize / rebuild ---
let ro: ResizeObserver | null = null
let resizeTimer: number | null = null

function hardRebuildMap() {
  if (!mapEl.value) return
  const center = hasValidCoords.value
    ? { lat: latNum.value, lng: lngNum.value }
    : (map as any)?.getCenter?.() ?? { lat: 19.704, lng: -103.344 }
  if (marker) { removeMarker(marker); marker = null }
  map = null

  const mapConfig: google.maps.MapOptions = {
    center,
    zoom: props.zoom,
    mapTypeControl: false,
    streetViewControl: false,
    fullscreenControl: true,
    mapId: GMAPS_MAP_ID,
  }
  const { Map } = google.maps // ya cargado
  map = new Map(mapEl.value, mapConfig)
  if (props.showMarker && hasValidCoords.value) {
    marker = createMarker(center, map)
  }
}

function softResize() {
  if (!map || !mapEl.value) return
  google.maps.event.trigger(map as any, "resize")
  const pos = hasValidCoords.value
    ? { lat: latNum.value, lng: lngNum.value }
    : (map as any).getCenter()
  if (pos) (map as any).setCenter(pos)
}

function scheduleResize(hard = false) {
  if (resizeTimer) window.clearTimeout(resizeTimer)
  resizeTimer = window.setTimeout(async () => {
    await nextTick()
    const rect = mapEl.value?.getBoundingClientRect()
    if (hard || !rect || rect.width < 80 || rect.height < 80) {
      hardRebuildMap()
    } else {
      softResize()
    }
  }, 120)
}

onMounted(async () => {
  const error = validateConfig()
  if (error) {
    configError.value = error
    return
  }

  if (rootEl.value) {
    rootEl.value.style.height = props.height
    rootEl.value.style.minHeight = props.height
  }

  await loadGoogleMaps(GMAPS_API_KEY!)
  await initMap()

  // Observers
  ro = new ResizeObserver(() => scheduleResize(false))
  if (rootEl.value) ro.observe(rootEl.value)

  window.addEventListener("resize", onResize)
  window.addEventListener("orientationchange", onOrient)

  // *nudges* para layouts que tardan en calcular tamaño
  setTimeout(() => scheduleResize(false), 50)
  setTimeout(() => scheduleResize(false), 250)
})

function onResize() { scheduleResize(false) }
function onOrient() { scheduleResize(true) }

onUnmounted(() => {
  window.removeEventListener("resize", onResize)
  window.removeEventListener("orientationchange", onOrient)
  if (ro && rootEl.value) ro.unobserve(rootEl.value)
  ro = null
  if (marker) { removeMarker(marker); marker = null }
  map = null
})

watch(() => [latNum.value, lngNum.value, props.zoom, props.showMarker], () => {
  updatePosition()
})

// --- Expose manual refresh desde el padre ---
defineExpose({
  refresh: () => scheduleResize(false),
  rebuild: () => scheduleResize(true),
})
</script>

<template>
  <div
    ref="rootEl"
    class="rounded-lg overflow-hidden border border-border flex flex-col"
    :style="{ height: props.height, minHeight: props.height }"
  >
    <div v-if="configError" class="flex flex-col items-center justify-center flex-1 p-4 bg-muted/50 text-center">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-destructive mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        <path d="M12 8v4m0 4h.01"/>
      </svg>
      <p class="text-sm font-medium text-destructive mb-1">Error de configuración</p>
      <p class="text-xs text-muted-foreground">{{ configError }}</p>
    </div>

    <template v-else>
      <div class="flex items-center justify-end gap-2 p-2 border-b border-border bg-background/80">
        <slot name="left-actions" />
        <div class="flex-1" />
        <slot name="right-actions" />
        <a
          v-if="props.showOpenButton"
          :href="googleMapsUrl"
          :target="props.openInNewTab ? '_blank' : undefined"
          :rel="props.openInNewTab ? 'noopener noreferrer' : undefined"
          class="inline-flex items-center gap-2 rounded-xl px-3 py-1.5 text-sm border border-input shadow-xs hover:shadow-sm transition"
          :class="hasValidCoords ? 'cursor-pointer bg-card hover:bg-accent' : 'cursor-not-allowed bg-muted'"
          :tabindex="hasValidCoords ? 0 : -1"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7Zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z"/>
          </svg>
          <span>{{ props.buttonLabel }}</span>
        </a>
      </div>

      <div ref="mapEl" class="w-full flex-1" style="touch-action: pan-x pan-y;"></div>
    </template>
  </div>
</template>

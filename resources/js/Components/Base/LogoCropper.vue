<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import Modal from '@/Components/Base/Modal.vue'

const props = defineProps<{
    show:   boolean
    file:   File | null
}>()

const emit = defineEmits<{
    (e: 'close'): void
    (e: 'crop', blob: Blob): void
}>()

// ── Canvas state ─────────────────────────────────────────────────────────────
const canvas  = ref<HTMLCanvasElement | null>(null)
const VIEW    = 240      // viewport size (px, square)
const OUTPUT  = 512      // output canvas size

const img     = ref<HTMLImageElement | null>(null)
const scale   = ref(1)
const minScale = ref(1)
const offset  = ref({ x: 0, y: 0 })

// drag state
const dragging = ref(false)
const dragStart = ref({ x: 0, y: 0 })
const offsetAtStart = ref({ x: 0, y: 0 })

// ── Load image when file changes ─────────────────────────────────────────────
function loadFile(file: File) {
    const reader = new FileReader()
    reader.onload = (ev) => {
        const image = new Image()
        image.onload = () => {
            img.value = image
            // min scale = fill the view square
            const min = Math.max(VIEW / image.naturalWidth, VIEW / image.naturalHeight)
            minScale.value = min
            scale.value    = min
            offset.value   = {
                x: (VIEW - image.naturalWidth  * min) / 2,
                y: (VIEW - image.naturalHeight * min) / 2,
            }
            draw()
        }
        image.src = ev.target!.result as string
    }
    reader.readAsDataURL(file)
}

// ── Draw ─────────────────────────────────────────────────────────────────────
function draw() {
    const ctx = canvas.value?.getContext('2d')
    if (!ctx || !img.value) return
    ctx.clearRect(0, 0, VIEW, VIEW)
    ctx.drawImage(
        img.value,
        offset.value.x,
        offset.value.y,
        img.value.naturalWidth  * scale.value,
        img.value.naturalHeight * scale.value,
    )
}

// ── Clamp offset so image always covers the viewport ─────────────────────────
function clamp() {
    if (!img.value) return
    const w = img.value.naturalWidth  * scale.value
    const h = img.value.naturalHeight * scale.value
    offset.value.x = Math.min(0, Math.max(VIEW - w, offset.value.x))
    offset.value.y = Math.min(0, Math.max(VIEW - h, offset.value.y))
}

// ── Zoom ─────────────────────────────────────────────────────────────────────
function onWheel(e: WheelEvent) {
    e.preventDefault()
    const delta = -e.deltaY * 0.001
    applyZoom(delta, e.offsetX, e.offsetY)
}

function onSlider(e: Event) {
    const raw = parseFloat((e.target as HTMLInputElement).value)
    applyZoom(raw - scale.value, VIEW / 2, VIEW / 2)
}

function applyZoom(delta: number, cx: number, cy: number) {
    if (!img.value) return
    const prev = scale.value
    const next = Math.max(minScale.value, Math.min(6, prev + delta))
    const ratio = next / prev
    offset.value.x = cx - ratio * (cx - offset.value.x)
    offset.value.y = cy - ratio * (cy - offset.value.y)
    scale.value = next
    clamp()
    draw()
}

// ── Drag ─────────────────────────────────────────────────────────────────────
function onPointerDown(e: PointerEvent) {
    dragging.value = true
    dragStart.value = { x: e.clientX, y: e.clientY }
    offsetAtStart.value = { ...offset.value }
    ;(e.target as HTMLElement).setPointerCapture(e.pointerId)
}

function onPointerMove(e: PointerEvent) {
    if (!dragging.value) return
    offset.value.x = offsetAtStart.value.x + (e.clientX - dragStart.value.x)
    offset.value.y = offsetAtStart.value.y + (e.clientY - dragStart.value.y)
    clamp()
    draw()
}

function onPointerUp() { dragging.value = false }

// ── Confirm crop → emit blob ─────────────────────────────────────────────────
function confirm() {
    if (!img.value) return
    const out = document.createElement('canvas')
    out.width = OUTPUT; out.height = OUTPUT
    const ctx = out.getContext('2d')!
    const ratio = OUTPUT / VIEW
    ctx.drawImage(
        img.value,
        offset.value.x * ratio,
        offset.value.y * ratio,
        img.value.naturalWidth  * scale.value * ratio,
        img.value.naturalHeight * scale.value * ratio,
    )
    out.toBlob((blob) => {
        if (blob) emit('crop', blob)
    }, 'image/jpeg', 0.92)
}

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
    if (props.file) loadFile(props.file)
})

// re-load when file prop changes (parent opens with a new file)
import { watch } from 'vue'
watch(() => props.file, (f) => { if (f) loadFile(f) })
watch(() => props.show, (v) => { if (v && props.file) loadFile(props.file) })
</script>

<template>
    <Modal :show="show" title="اقتصاص الشعار" size="sm" @close="emit('close')">
        <div class="cropper-wrap">
            <!-- Canvas viewport -->
            <div class="cropper-viewport">
                <canvas
                    ref="canvas"
                    :width="240"
                    :height="240"
                    class="cropper-canvas"
                    :style="{ cursor: dragging ? 'grabbing' : 'grab' }"
                    @wheel.prevent="onWheel"
                    @pointerdown="onPointerDown"
                    @pointermove="onPointerMove"
                    @pointerup="onPointerUp"
                    @pointercancel="onPointerUp"
                />
                <!-- Overlay: circle mask guide -->
                <div class="cropper-overlay" />
            </div>

            <!-- Zoom slider -->
            <div class="cropper-controls">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="M8 11h6"/></svg>
                <input
                    type="range"
                    :min="minScale"
                    :max="minScale * 4"
                    :step="0.01"
                    :value="scale"
                    class="cropper-slider"
                    @input="onSlider"
                />
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="M8 11h6M11 8v6"/></svg>
            </div>

            <p class="cropper-hint">اسحب لتحريك · استخدم العجلة أو الشريط للتكبير</p>
        </div>

        <template #footer>
            <button class="btn btn-primary" @click="confirm">حفظ الشعار</button>
            <button class="btn btn-secondary" @click="emit('close')">إلغاء</button>
        </template>
    </Modal>
</template>

<style scoped>
.cropper-wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    padding: 4px 0;
}

.cropper-viewport {
    position: relative;
    width: 240px;
    height: 240px;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border-default);
    background: var(--bg-page);
    flex-shrink: 0;
}

.cropper-canvas {
    display: block;
    touch-action: none;
    user-select: none;
}

/* Subtle vignette to show crop boundary */
.cropper-overlay {
    position: absolute;
    inset: 0;
    pointer-events: none;
    box-shadow: inset 0 0 0 2px var(--sada-500);
    border-radius: 12px;
}

/* Zoom controls */
.cropper-controls {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 100%;
    max-width: 260px;
    color: var(--text-muted);
}

.cropper-slider {
    flex: 1;
    -webkit-appearance: none;
    appearance: none;
    height: 4px;
    border-radius: 4px;
    background: var(--border-default);
    outline: none;
    cursor: pointer;
}
.cropper-slider::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 16px; height: 16px;
    border-radius: 50%;
    background: var(--sada-500);
    cursor: pointer;
    border: 2px solid var(--bg-surface);
    box-shadow: 0 0 0 1px var(--sada-500);
}

.cropper-hint {
    font-size: 11px;
    color: var(--text-muted);
    margin: 0;
    text-align: center;
}
</style>

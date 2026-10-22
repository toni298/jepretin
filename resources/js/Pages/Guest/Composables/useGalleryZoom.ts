import { onMounted, onUnmounted, ref, type Ref } from 'vue'

interface PointerPosition {
    x: number
    y: number
}

interface PanStart {
    x: number
    y: number
    offsetX: number
    offsetY: number
}

interface PinchStart {
    distance: number
    scale: number
    imageX: number
    imageY: number
}

export function useGalleryZoom(
    viewport: Ref<HTMLDivElement | null>,
    image: Ref<HTMLImageElement | null>,
) {
    const scale = ref(1)
    const offsetX = ref(0)
    const offsetY = ref(0)
    const isPanning = ref(false)
    const pointers = new Map<number, PointerPosition>()

    let currentScale = 1
    let currentOffsetX = 0
    let currentOffsetY = 0
    let renderFrame: number | null = null
    let panStart: PanStart | null = null
    let pinchStart: PinchStart | null = null

    function resetTransform(): void {
        if (renderFrame !== null) {
            cancelAnimationFrame(renderFrame)
            renderFrame = null
        }

        currentScale = 1
        currentOffsetX = 0
        currentOffsetY = 0
        scale.value = 1
        offsetX.value = 0
        offsetY.value = 0
        isPanning.value = false
        pointers.clear()
        panStart = null
        pinchStart = null
    }

    function scheduleTransformRender(): void {
        if (renderFrame !== null) return

        renderFrame = requestAnimationFrame(() => {
            scale.value = currentScale
            offsetX.value = currentOffsetX
            offsetY.value = currentOffsetY
            renderFrame = null
        })
    }

    function clamp(value: number, minimum: number, maximum: number): number {
        return Math.min(Math.max(value, minimum), maximum)
    }

    function constrainOffsets(): void {
        const viewportElement = viewport.value
        const imageElement = image.value

        if (!viewportElement || !imageElement) return

        const maxX = Math.max((imageElement.clientWidth * currentScale - viewportElement.clientWidth) / 2, 0)
        const maxY = Math.max((imageElement.clientHeight * currentScale - viewportElement.clientHeight) / 2, 0)

        currentOffsetX = clamp(currentOffsetX, -maxX, maxX)
        currentOffsetY = clamp(currentOffsetY, -maxY, maxY)
        scheduleTransformRender()
    }

    function zoomAt(nextScale: number, focusX = 0, focusY = 0): void {
        const previousScale = currentScale
        const boundedScale = clamp(nextScale, 1, 4)
        const imageX = (focusX - currentOffsetX) / previousScale
        const imageY = (focusY - currentOffsetY) / previousScale

        currentScale = boundedScale
        currentOffsetX = focusX - imageX * boundedScale
        currentOffsetY = focusY - imageY * boundedScale
        constrainOffsets()
    }

    function zoomIn(): void {
        zoomAt(currentScale + 0.5)
    }

    function zoomOut(): void {
        zoomAt(currentScale - 0.5)
    }

    function distanceBetweenPointers(): number {
        const [first, second] = [...pointers.values()]

        return Math.hypot(second.x - first.x, second.y - first.y)
    }

    function midpointBetweenPointers(): PointerPosition {
        const [first, second] = [...pointers.values()]
        const bounds = viewport.value?.getBoundingClientRect()

        return {
            x: (first.x + second.x) / 2 - (bounds?.left ?? 0) - (bounds?.width ?? 0) / 2,
            y: (first.y + second.y) / 2 - (bounds?.top ?? 0) - (bounds?.height ?? 0) / 2,
        }
    }

    function startPinch(): void {
        if (pointers.size !== 2) return

        const midpoint = midpointBetweenPointers()

        pinchStart = {
            distance: Math.max(distanceBetweenPointers(), 1),
            scale: currentScale,
            imageX: (midpoint.x - currentOffsetX) / currentScale,
            imageY: (midpoint.y - currentOffsetY) / currentScale,
        }
        panStart = null
        isPanning.value = true
    }

    function handlePointerDown(event: PointerEvent): void {
        if (event.pointerType === 'mouse' && event.button !== 0) return
        if (pointers.size >= 2) return

        const target = event.currentTarget

        if (!(target instanceof HTMLDivElement)) return

        target.setPointerCapture(event.pointerId)
        pointers.set(event.pointerId, { x: event.clientX, y: event.clientY })

        if (pointers.size === 2) {
            startPinch()
        } else if (currentScale > 1) {
            panStart = {
                x: event.clientX,
                y: event.clientY,
                offsetX: currentOffsetX,
                offsetY: currentOffsetY,
            }
            isPanning.value = true
        }
    }

    function handlePointerMove(event: PointerEvent): void {
        if (!pointers.has(event.pointerId)) return

        pointers.set(event.pointerId, { x: event.clientX, y: event.clientY })

        if (pointers.size === 2 && pinchStart) {
            const midpoint = midpointBetweenPointers()
            const nextScale = clamp(
                pinchStart.scale * distanceBetweenPointers() / pinchStart.distance,
                1,
                4,
            )

            currentScale = nextScale
            currentOffsetX = midpoint.x - pinchStart.imageX * nextScale
            currentOffsetY = midpoint.y - pinchStart.imageY * nextScale
            constrainOffsets()

            return
        }

        if (pointers.size === 1 && panStart && currentScale > 1) {
            currentOffsetX = panStart.offsetX + event.clientX - panStart.x
            currentOffsetY = panStart.offsetY + event.clientY - panStart.y
            constrainOffsets()
        }
    }

    function handlePointerEnd(event: PointerEvent): void {
        pointers.delete(event.pointerId)
        pinchStart = null

        if (pointers.size === 1 && currentScale > 1) {
            const [remainingPointer] = pointers.values()
            panStart = {
                x: remainingPointer.x,
                y: remainingPointer.y,
                offsetX: currentOffsetX,
                offsetY: currentOffsetY,
            }

            return
        }

        panStart = null
        isPanning.value = false
    }

    function handleWheel(event: WheelEvent): void {
        event.preventDefault()

        const bounds = viewport.value?.getBoundingClientRect()

        if (!bounds) return

        const focusX = event.clientX - bounds.left - bounds.width / 2
        const focusY = event.clientY - bounds.top - bounds.height / 2
        const multiplier = Math.exp(-event.deltaY * 0.002)

        zoomAt(currentScale * multiplier, focusX, focusY)
    }

    function handleResize(): void {
        constrainOffsets()
    }

    onMounted(() => {
        window.addEventListener('resize', handleResize)
    })

    onUnmounted(() => {
        window.removeEventListener('resize', handleResize)
        if (renderFrame !== null) cancelAnimationFrame(renderFrame)
    })

    return {
        scale,
        offsetX,
        offsetY,
        isPanning,
        resetTransform,
        constrainOffsets,
        zoomIn,
        zoomOut,
        handlePointerDown,
        handlePointerMove,
        handlePointerEnd,
        handleWheel,
    }
}

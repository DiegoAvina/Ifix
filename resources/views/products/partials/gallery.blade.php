<div
    x-data="{
        images: @js($product->gallery),
        index: 0,
        lightbox: false,
        zoom: false,
        canHover: window.matchMedia('(hover: hover) and (pointer: fine)').matches,
        x: 50,
        y: 50,
        touchX: null,
        touchY: null,

        init() {
            if (this.images.length === 0) {
                this.images = [{
                    url: @js($product->image?->url),
                    alt: @js($product->image?->alt)
                }];
            }

            this.$watch('lightbox', (open) => {
                document.documentElement.classList.toggle('overflow-hidden', open);
            });
        },

        {{-- Abrir el lightbox crea una entrada de historial: el botón "atrás" del teléfono lo cierra en vez de salir del producto. --}}
        openLightbox() {
            this.lightbox = true;
            history.pushState({ ifixLightbox: true }, '');
        },

        closeLightbox() {
            if (!this.lightbox) {
                return;
            }

            history.state?.ifixLightbox ? history.back() : (this.lightbox = false);
        },

        get current() {
            return this.images[this.index];
        },

        get multiple() {
            return this.images.length > 1;
        },

        next() {
            this.index = (this.index + 1) % this.images.length;
        },

        prev() {
            this.index = (this.index - 1 + this.images.length) % this.images.length;
        },

        move(e) {
            const rect = e.currentTarget.getBoundingClientRect();

            this.x = ((e.clientX - rect.left) / rect.width) * 100;
            this.y = ((e.clientY - rect.top) / rect.height) * 100;
        },

        touchStart(e) {
            this.touchX = e.touches[0].clientX;
            this.touchY = e.touches[0].clientY;
        },

        {{-- Swipe horizontal para cambiar de imagen; ignora el scroll vertical. --}}
        touchEnd(e) {
            if (this.touchX === null || !this.multiple) {
                return;
            }

            const dx = e.changedTouches[0].clientX - this.touchX;
            const dy = e.changedTouches[0].clientY - this.touchY;

            this.touchX = null;

            if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
                dx < 0 ? this.next() : this.prev();
            }
        }
    }"

    @keydown.escape.window="closeLightbox()"
    @popstate.window="lightbox = false"
    @keydown.arrow-right.window="if (lightbox) next()"
    @keydown.arrow-left.window="if (lightbox) prev()"

    class="min-w-0 space-y-3 sm:space-y-4"
>

    {{-- Imagen principal --}}
    <div class="group relative overflow-hidden rounded-2xl border border-neutral-200 bg-white p-2 shadow-sm sm:p-3">

        {{-- Badges --}}
        @if (count($product->badges))
            <div class="pointer-events-none absolute left-4 right-4 top-4 z-30 flex flex-wrap gap-1.5 sm:left-5 sm:right-5 sm:top-5 sm:gap-2">
                @foreach ($product->badges as $badge)
                    @php
                        $classes = match ($badge->type) {
                            'danger' => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',
                            'warning' => 'bg-amber-100 text-amber-950 ring-1 ring-inset ring-amber-200',
                            'success' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200',
                            'info' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200',
                            default => 'bg-neutral-950 text-white ring-1 ring-inset ring-neutral-950',
                        };
                    @endphp

                    <span class="rounded-full px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide shadow-sm sm:px-3 {{ $classes }}">
                        {{ $badge->label }}
                    </span>
                @endforeach
            </div>
        @endif

        <div
            class="relative aspect-square cursor-zoom-in touch-pan-y overflow-hidden rounded-xl bg-[#f5f5f3]"
            @mouseenter="if (canHover) zoom = true"
            @mouseleave="zoom = false"
            @mousemove="if (canHover) move($event)"
            @touchstart.passive="touchStart($event)"
            @touchend="touchEnd($event)"
            @click="openLightbox()"
        >
            <img
                data-product-main-image
                src="{{ $product->image?->url }}"
                alt="{{ $product->image?->alt ?: $product->name }}"
                fetchpriority="high"
                :src="current.url"
                :alt="current.alt"
                class="h-full w-full select-none object-contain p-4 transition duration-300 ease-out sm:p-10"
                :style="zoom
                    ? `transform:scale(2); transform-origin:${x}% ${y}%`
                    : 'transform:scale(1)'"
                draggable="false"
            >

            {{-- Pista de ampliar en táctil --}}
            <span
                x-show="!canHover"
                class="pointer-events-none absolute bottom-3 right-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 text-neutral-700 shadow-sm"
                aria-hidden="true"
            >
                <span class="material-symbols-outlined text-xl">zoom_in</span>
            </span>

            {{-- Contador / puntos en móvil --}}
            <template x-if="multiple">
                <div class="pointer-events-none absolute inset-x-0 bottom-3 flex justify-center gap-1.5 sm:hidden">
                    <template x-for="(image, i) in images" :key="i">
                        <span
                            class="h-1.5 rounded-full transition-all"
                            :class="index === i ? 'w-5 bg-[#f5a623]' : 'w-1.5 bg-neutral-400/70'"
                        ></span>
                    </template>
                </div>
            </template>
        </div>

        {{-- Flechas sobre la imagen (desktop) --}}
        <template x-if="multiple">
            <div>
                <button
                    type="button"
                    @click.stop="prev()"
                    aria-label="Imagen anterior"
                    class="absolute left-5 top-1/2 z-20 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-neutral-800 opacity-0 shadow-md transition focus-visible:opacity-100 group-hover:opacity-100 sm:flex"
                >
                    <span class="material-symbols-outlined">chevron_left</span>
                </button>

                <button
                    type="button"
                    @click.stop="next()"
                    aria-label="Imagen siguiente"
                    class="absolute right-5 top-1/2 z-20 hidden h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 text-neutral-800 opacity-0 shadow-md transition focus-visible:opacity-100 group-hover:opacity-100 sm:flex"
                >
                    <span class="material-symbols-outlined">chevron_right</span>
                </button>
            </div>
        </template>
    </div>

    {{-- Miniaturas: carrusel horizontal en móvil, grid en pantallas grandes --}}
    <div
        x-show="multiple"
        class="-mx-4 flex snap-x gap-2 overflow-x-auto px-4 pb-1 [scrollbar-width:none] sm:mx-0 sm:grid sm:grid-cols-5 sm:gap-3 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-6 [&::-webkit-scrollbar]:hidden"
    >
        <template x-for="(image, i) in images" :key="i">
            <button
                type="button"
                @click="index = i"
                :aria-label="`Ver imagen ${i + 1}`"
                :aria-current="index === i"
                class="w-[4.5rem] shrink-0 snap-start overflow-hidden rounded-xl border bg-white transition sm:w-auto"
                :class="index === i
                    ? 'ring-2 ring-[#f5a623] border-[#f5a623] shadow-md'
                    : 'border-neutral-200 hover:border-neutral-400 hover:shadow-sm'"
            >
                <img
                    :src="image.url"
                    :alt="image.alt"
                    loading="lazy"
                    class="aspect-square w-full object-cover"
                >
            </button>
        </template>
    </div>

    {{-- LIGHTBOX --}}
    <div
        x-show="lightbox"
        x-transition.opacity
        class="fixed inset-0 z-[999] flex flex-col bg-black/95"
        style="display:none"
        role="dialog"
        aria-modal="true"
        aria-label="Galería de imágenes"
    >
        <div class="flex items-center justify-between p-4 sm:p-6">
            <span
                x-show="multiple"
                class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white tabular-nums"
                x-text="`${index + 1} / ${images.length}`"
            ></span>

            <button
                type="button"
                @click="closeLightbox()"
                aria-label="Cerrar"
                class="ml-auto flex h-11 w-11 items-center justify-center rounded-full bg-white text-neutral-900 shadow-lg transition hover:scale-105"
            >
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div
            class="relative flex min-h-0 flex-1 items-center justify-center px-2 sm:px-20"
            @click.self="closeLightbox()"
            @touchstart.passive="touchStart($event)"
            @touchend="touchEnd($event)"
        >
            <img
                :src="current.url"
                :alt="current.alt"
                class="max-h-full max-w-full select-none object-contain"
                draggable="false"
            >

            <template x-if="multiple">
                <div>
                    <button
                        type="button"
                        @click="prev()"
                        aria-label="Imagen anterior"
                        class="absolute left-6 top-1/2 hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white text-neutral-900 shadow-lg transition hover:scale-105 sm:flex"
                    >
                        <span class="material-symbols-outlined">chevron_left</span>
                    </button>

                    <button
                        type="button"
                        @click="next()"
                        aria-label="Imagen siguiente"
                        class="absolute right-6 top-1/2 hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white text-neutral-900 shadow-lg transition hover:scale-105 sm:flex"
                    >
                        <span class="material-symbols-outlined">chevron_right</span>
                    </button>
                </div>
            </template>
        </div>

        {{-- Controles inferiores en móvil (debajo de la imagen, no encima) --}}
        <div x-show="multiple" class="flex items-center justify-center gap-6 p-4 pb-[max(1rem,env(safe-area-inset-bottom))] sm:hidden">
            <button
                type="button"
                @click="prev()"
                aria-label="Imagen anterior"
                class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-neutral-900 shadow-lg"
            >
                <span class="material-symbols-outlined">chevron_left</span>
            </button>

            <button
                type="button"
                @click="next()"
                aria-label="Imagen siguiente"
                class="flex h-12 w-12 items-center justify-center rounded-full bg-white text-neutral-900 shadow-lg"
            >
                <span class="material-symbols-outlined">chevron_right</span>
            </button>
        </div>
    </div>

</div>

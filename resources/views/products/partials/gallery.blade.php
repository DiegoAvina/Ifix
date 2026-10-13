<div
    x-data="{
        images: @js($product->gallery),
        index: 0,
        lightbox: false,
        zoom: false,
        x:50,
        y:50,

        init() {
            if(this.images.length === 0){
                this.images = [{
                    url: @js($product->image?->url),
                    alt: @js($product->image?->alt)
                }];
            }
        },

        get current(){
            return this.images[this.index];
        },

        next(){
            this.index = (this.index + 1) % this.images.length;
        },

        prev(){
            this.index = (this.index - 1 + this.images.length) % this.images.length;
        },

        move(e){
            const rect = e.target.getBoundingClientRect();

            this.x = ((e.clientX - rect.left) / rect.width) * 100;
            this.y = ((e.clientY - rect.top) / rect.height) * 100;
        }
    }"

    @keydown.escape.window="lightbox=false"
    @keydown.arrow-right.window="if(lightbox) next()"
    @keydown.arrow-left.window="if(lightbox) prev()"

    class="space-y-6"
>

    {{-- Imagen principal --}}
    <div
        class="group relative overflow-hidden rounded-[2rem] border border-outline-variant/30 bg-gradient-to-b from-surface-container-low to-white shadow-sm"
    >

        {{-- Badges --}}
        @if(count($product->badges))

            <div class="absolute left-5 top-5 z-30 flex flex-wrap gap-2">

                @foreach($product->badges as $badge)

                    @php
                        $classes = match($badge->type) {

                            'danger' => 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',

                            'warning' => 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200',

                            'success' => 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-200',

                            'info' => 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200',

                            default => 'bg-white text-on-surface ring-1 ring-inset ring-outline-variant',

                        };
                    @endphp

                    <span class="rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-wide shadow-sm {{ $classes }}">

                        {{ $badge->label }}

                    </span>

                @endforeach

            </div>

        @endif

        <div

            class="relative aspect-square overflow-hidden cursor-zoom-in bg-white"

            @mouseenter="zoom=true"

            @mouseleave="zoom=false"

            @mousemove="move($event)"

            @click="lightbox=true"

        >

            <img

                :src="current.url"

                :alt="current.alt"

                class="h-full w-full object-contain p-10 transition duration-300 ease-out"

                :style="zoom
                    ? `transform:scale(2);
                       transform-origin:${x}% ${y}%`
                    : 'transform:scale(1)'"

                draggable="false"

            >

        </div>

    </div>

    {{-- Miniaturas --}}

    <div class="grid grid-cols-4 gap-4 sm:grid-cols-5 lg:grid-cols-6">

        <template

            x-for="(image,i) in images"

            :key="i"

        >

            <button

                @click="index=i"

                class="overflow-hidden rounded-2xl border bg-white transition"

                :class="index===i
                    ? 'ring-2 ring-primary border-primary shadow-lg scale-105'
                    : 'border-outline-variant/50 hover:border-primary/40 hover:shadow'"

            >

                <img

                    :src="image.url"

                    :alt="image.alt"

                    class="aspect-square w-full object-cover"

                >

            </button>

        </template>

    </div>

    {{-- LIGHTBOX --}}

    <div

        x-show="lightbox"

        x-transition.opacity

        class="fixed inset-0 z-[999] flex items-center justify-center bg-black/90"

        style="display:none"

    >

        {{-- Cerrar --}}

        <button

            @click="lightbox=false"

            aria-label="Cerrar"

            class="absolute right-6 top-6 flex h-11 w-11 items-center justify-center rounded-full bg-white text-on-surface shadow-lg transition hover:scale-105"

        >

            <span class="material-symbols-outlined">close</span>

        </button>

        {{-- Flecha izquierda --}}

        <button

            @click="prev()"

            aria-label="Imagen anterior"

            class="absolute left-6 flex h-12 w-12 items-center justify-center rounded-full bg-white text-on-surface shadow-lg transition hover:scale-105"

        >

            <span class="material-symbols-outlined">chevron_left</span>

        </button>

        {{-- Imagen --}}

        <img

            :src="current.url"

            :alt="current.alt"

            class="max-h-[90vh] max-w-[90vw] object-contain"

        >

        {{-- Flecha derecha --}}

        <button

            @click="next()"

            aria-label="Imagen siguiente"

            class="absolute right-6 flex h-12 w-12 items-center justify-center rounded-full bg-white text-on-surface shadow-lg transition hover:scale-105"

        >

            <span class="material-symbols-outlined">chevron_right</span>

        </button>

    </div>

</div>
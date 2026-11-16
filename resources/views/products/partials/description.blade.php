<section class="scroll-mt-28 rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm sm:p-7">

    <h2 class="font-heading text-xl font-extrabold text-neutral-950 sm:text-2xl">
        Descripción
    </h2>

    @if($product->description)
        {{--
            En móvil una descripción larga empuja especificaciones y relacionados muy abajo:
            se recorta a ~20 rem con "Ver más". Sin JS (o en desktop) se muestra completa.
        --}}
        <div
            x-data="{
                open: false,
                long: false,
                measure() {
                    this.long = window.matchMedia('(max-width: 1023px)').matches
                        && this.$refs.body.scrollHeight > 420;
                }
            }"
            x-init="$nextTick(() => measure())"
            @resize.window.debounce.200ms="measure()"
        >
            <div
                x-ref="body"
                id="product-description"
                class="relative overflow-hidden"
                :class="long && !open ? 'max-h-80' : ''"
            >
                {{-- En móvil: texto algo más chico, palabras/URLs largas se cortan, tablas con scroll propio e imágenes/videos al ancho. --}}
                <div class="prose prose-sm mt-4 max-w-none break-words sm:prose-base prose-img:mx-auto prose-img:max-w-full prose-img:rounded-xl [&_iframe]:aspect-video [&_iframe]:h-auto [&_iframe]:w-full [&_table]:block [&_table]:max-w-full [&_table]:overflow-x-auto [&_video]:h-auto [&_video]:w-full prose-p:text-neutral-600 prose-headings:font-heading prose-headings:text-neutral-950 prose-strong:text-neutral-900 prose-a:text-[#a76500]">
                    {!! \App\Support\SafeHtml::clean($product->description) !!}
                </div>

                <div
                    x-show="long && !open"
                    class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-white to-transparent dark:from-[#17191b]"
                    aria-hidden="true"
                ></div>
            </div>

            <button
                type="button"
                x-show="long"
                x-cloak
                @click="open = !open; if (!open) $el.closest('section').scrollIntoView({ block: 'start', behavior: 'smooth' })"
                :aria-expanded="open.toString()"
                aria-controls="product-description"
                class="mt-3 inline-flex min-h-11 items-center gap-1 rounded-full px-1 text-sm font-bold text-[#a76500]"
            >
                <span x-text="open ? 'Ver menos' : 'Ver descripción completa'">Ver descripción completa</span>
                <span class="material-symbols-outlined text-xl transition-transform" :class="open && 'rotate-180'" aria-hidden="true">expand_more</span>
            </button>
        </div>
    @else
        <p class="mt-4 text-sm leading-6 text-neutral-600">
            Este producto no tiene descripción.
        </p>
    @endif

</section>

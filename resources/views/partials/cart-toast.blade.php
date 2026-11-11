{{--
    Aviso "Agregado al carrito" con la foto y el nombre del producto.
    Lo dispara window.ifixCartAdded() (resources/js/app.js) con el evento
    cart-added, solo después de que el servidor confirmó el producto.
--}}
<div
    x-data="{ show: false, item: { name: '', image: null, quantity: 1 }, timer: null }"
    @cart-added.window="
        item = $event.detail;
        show = true;
        clearTimeout(timer);
        timer = setTimeout(() => show = false, 4500);
    "
    @keydown.escape.window="show = false"
    class="pointer-events-none fixed inset-x-0 bottom-0 z-[60] flex justify-center p-4 sm:inset-x-auto sm:right-0 sm:justify-end sm:p-6"
    role="status"
    aria-live="polite">

    <div
        x-show="show"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-y-6 opacity-0 sm:translate-x-6 sm:translate-y-0"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="translate-y-4 opacity-0"
        @mouseenter="clearTimeout(timer)"
        @mouseleave="timer = setTimeout(() => show = false, 2500)"
        class="pointer-events-auto relative w-full max-w-sm overflow-hidden rounded-xl border border-white/10 bg-[#17191b] p-4 text-white shadow-2xl shadow-black/50">

        <div class="flex gap-4">
            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[#f4f4f2]">
                <template x-if="item.image">
                    <img :src="item.image" alt="" class="h-full w-full object-contain p-1.5">
                </template>
                <template x-if="!item.image">
                    <span class="material-symbols-outlined text-3xl text-neutral-400" aria-hidden="true">inventory_2</span>
                </template>
            </div>

            <div class="min-w-0 flex-1 pr-6">
                <p class="flex items-center gap-1.5 text-xs font-extrabold uppercase tracking-wide text-emerald-400">
                    <span class="material-symbols-outlined text-base" aria-hidden="true">check_circle</span>
                    Agregado al carrito
                </p>
                <p class="mt-1 line-clamp-2 text-sm font-bold leading-snug" x-text="item.name"></p>
                <p class="mt-0.5 text-xs text-neutral-400">Cantidad: <span x-text="item.quantity"></span></p>

                <div class="mt-3 flex flex-wrap gap-2">
                    <a href="{{ route('cart.index') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-[#f5a623] px-3 py-2 text-xs font-extrabold text-neutral-950 transition hover:bg-amber-400 active:scale-95">
                        <span class="material-symbols-outlined text-base" aria-hidden="true">shopping_cart</span>
                        Ver carrito
                    </a>
                    <button type="button" @click="show = false"
                        class="rounded-lg border border-white/15 px-3 py-2 text-xs font-bold text-neutral-200 transition hover:border-white/40 hover:text-white active:scale-95">
                        Seguir comprando
                    </button>
                </div>
            </div>
        </div>

        <button type="button" @click="show = false" aria-label="Cerrar aviso"
            class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-lg text-neutral-400 transition hover:bg-white/10 hover:text-white">
            <span class="material-symbols-outlined text-lg" aria-hidden="true">close</span>
        </button>

        {{-- Barra que se vacía mientras el aviso sigue abierto. --}}
        <span x-show="show" class="cart-toast-timer absolute inset-x-0 bottom-0 h-0.5 origin-left bg-[#f5a623]" aria-hidden="true"></span>
    </div>
</div>

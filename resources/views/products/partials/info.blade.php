@php
$variantId = $product->variantId;

$inventory = $product->inventory;

$stock = (int) ($inventory->stock ?? 0);

$minQuantity = max(1, (int) ($inventory->minQuantity ?? 1));

$quantityIncrement = max(1, (int) ($inventory->quantityIncrement ?? 1));

$maxQuantity = (int) ($inventory->maxQuantity ?? 0);

/*
|--------------------------------------------------------------------------
| Disponibilidad real
|--------------------------------------------------------------------------
|
| $inventory->available ya viene calculado con la API real de Lunar
| (ProductVariant::canBeFulfilledAtQuantity), y contempla las tres
| reglas de "purchasable" ('always', 'in_stock', 'in_stock_or_on_backorder').
|
| stock > 0
| → Disponible
|
| stock = 0 pero available (always / in_stock_or_on_backorder)
| → Disponible bajo pedido
|
| no available
| → Agotado
|
*/

$available = (bool) $inventory->available;

$canPurchase = $variantId && $available;

$initialQty = $minQuantity + (($minQuantity % $quantityIncrement === 0)
    ? 0
    : $quantityIncrement - ($minQuantity % $quantityIncrement));
@endphp



<div
    x-data='{
    qty: {{ $initialQty }},

    loading: false,

    added: false,

    error: null,

    maxStock: {{ $maxQuantity }},

    minQuantity: {{ $minQuantity }},

    quantityIncrement: {{ $quantityIncrement }},

    variantId: {{ $variantId ?? "null" }},

    showBar: false,


    /*
    |--------------------------------------------------------------------------
    | Barra de compra fija (solo móvil)
    |--------------------------------------------------------------------------
    |
    | Aparece cuando los botones principales no están en pantalla.
    */

    init() {

        if (!("IntersectionObserver" in window) || !this.$refs.actions) {
            return;
        }

        new IntersectionObserver(([entry]) => {
            this.showBar = !entry.isIntersecting;
        }).observe(this.$refs.actions);

    },


    /*
    |--------------------------------------------------------------------------
    | Agregar al carrito
    |--------------------------------------------------------------------------
    */

    async addToCart() {

        if (this.loading) {
            return;
        }

        if (!this.variantId) {

            this.error =
                "Este producto no tiene una variante disponible.";

            return;
        }

        this.validateQuantity();

        if (this.error) {
            return;
        }

        this.loading = true;

        this.added = false;

        this.error = null;


        try {

            const csrfElement =
                document.querySelector(
                    "meta[name=\"csrf-token\"]"
                );


            if (!csrfElement) {

                throw new Error(
                    "No se encontró el token CSRF."
                );

            }


            const response = await fetch(
                "{{ $product->addToCartUrl }}",
                {
                    method: "POST",

                    headers: {
                        "Content-Type": "application/json",

                        "Accept": "application/json",

                        "X-CSRF-TOKEN":
                            csrfElement.content
                    },

                    body: JSON.stringify({

                        variant_id:
                            this.variantId,

                        quantity:
                            this.qty

                    })
                }
            );

const contentType =
    response.headers.get("content-type") || "";

let data;

if (contentType.includes("application/json")) {

    data = await response.json();

} else {

    const text = await response.text();

    console.error(
        "Respuesta no JSON del servidor:",
        text
    );

    throw new Error(
        "El servidor devolvió una respuesta inesperada."
    );
}


console.log(
    "CARRITO:",
    data
);

            if (!response.ok) {

                throw new Error(
                    data?.message ||
                    "No se pudo agregar el producto al carrito."
                );

            }


            if (!data.success) {

                throw new Error(
                    data?.message ||
                    "No se pudo agregar el producto al carrito."
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Producto agregado correctamente
            |--------------------------------------------------------------------------
            */

            this.added = true;


            /*
            |--------------------------------------------------------------------------
            | Notificar al resto de la tienda
            |--------------------------------------------------------------------------
            */

            window.dispatchEvent(

                new CustomEvent(
                    "cart-updated",
                    {
                        detail: data.cart
                    }
                )

            );

            window.ifixCartAdded({
                sourceImage: document.querySelector("[data-product-main-image]"),
                {{-- x-data va entre comillas simples: sin comillas ni < > & sueltos. --}}
                name: {!! json_encode($product->name, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!},
                quantity: this.qty,
            });


            /*
            |--------------------------------------------------------------------------
            | Ocultar mensaje después de unos segundos
            |--------------------------------------------------------------------------
            */

            setTimeout(() => {

                this.added = false;

            }, 3000);


        } catch (error) {

            console.error(
                "Error agregando al carrito:",
                error
            );


            this.error =
                error.message ||
                "Ocurrió un error al agregar el producto.";


        } finally {

            this.loading = false;

        }

    },


    /*
    |--------------------------------------------------------------------------
    | Comprar ahora
    |--------------------------------------------------------------------------
    */

    async buyNow() {

        if (this.loading) {
            return;
        }


        if (!this.variantId) {

            this.error =
                "Este producto no tiene una variante disponible.";

            return;
        }


        this.validateQuantity();


        if (this.error) {
            return;
        }


        this.loading = true;

        this.error = null;


        try {

            const csrfElement =
                document.querySelector(
                    "meta[name=\"csrf-token\"]"
                );


            if (!csrfElement) {

                throw new Error(
                    "No se encontró el token CSRF."
                );

            }


            const response = await fetch(
                "{{ $product->addToCartUrl }}",
                {
                    method: "POST",

                    headers: {

                        "Content-Type":
                            "application/json",

                        "Accept":
                            "application/json",

                        "X-CSRF-TOKEN":
                            csrfElement.content

                    },

                    body: JSON.stringify({

                        variant_id:
                            this.variantId,

                        quantity:
                            this.qty

                    })

                }
            );


            const contentType =
    response.headers.get("content-type") || "";

let data;

if (contentType.includes("application/json")) {

    data = await response.json();

} else {

    const text = await response.text();

    console.error(
        "Respuesta no JSON del servidor:",
        text
    );

    throw new Error(
        "El servidor devolvió una respuesta inesperada."
    );
}


console.log(
    "COMPRA AHORA:",
    data
);


if (!response.ok) {

    throw new Error(
        data?.message ||
        "No se pudo agregar el producto al carrito."
    );

}


if (!data.success) {

    throw new Error(
        data?.message ||
        "No se pudo agregar el producto al carrito."
    );

}

            /*
            |--------------------------------------------------------------------------
            | Actualizar carrito
            |--------------------------------------------------------------------------
            */

            window.dispatchEvent(

                new CustomEvent(
                    "cart-updated",
                    {
                        detail: data.cart
                    }
                )

            );


            /*
            |--------------------------------------------------------------------------
            | Ir al checkout
            |--------------------------------------------------------------------------
            */

            window.location.href =
                "{{ route('checkout.index') }}";


        } catch (error) {

            console.error(
                "Error en comprar ahora:",
                error
            );


            this.error =
                error.message ||
                "Ocurrió un error al procesar la compra.";


        } finally {

            this.loading = false;

        }

    },


    /*
    |--------------------------------------------------------------------------
    | Aumentar cantidad
    |--------------------------------------------------------------------------
    |
    | Avanza según quantity_increment del variant, no
    | necesariamente de 1 en 1. La validación real siempre
    | ocurre también en el servidor.
    */
    increase() {

        if (this.loading) {
            return;
        }

        this.qty = window.quantityRules.next(
            this.qty,
            this.minQuantity,
            this.quantityIncrement,
            this.maxStock
        );
    },


    /*
    |--------------------------------------------------------------------------
    | Disminuir cantidad
    |--------------------------------------------------------------------------
    */

    decrease() {

        if (this.loading) {
            return;
        }

        this.qty = window.quantityRules.prev(
            this.qty,
            this.minQuantity,
            this.quantityIncrement
        );
    },


    /*
    |--------------------------------------------------------------------------
    | Validar cantidad
    |--------------------------------------------------------------------------
    |
    | Corrige en el frontend cualquier cantidad que no respete
    | min_quantity / quantity_increment / stock disponible. El
    | backend (CartService + validadores de Lunar) siempre vuelve
    | a validar esto de forma independiente.
    */

    validateQuantity() {

        if (this.maxStock <= 0) {

            this.qty = window.quantityRules.floor(
                this.minQuantity,
                this.quantityIncrement
            );

            this.error =
                "Este producto está agotado.";

            return;
        }


        const normalized = window.quantityRules.normalize(
            this.qty || 0,
            this.minQuantity,
            this.quantityIncrement
        );


        if (normalized > this.maxStock) {

            this.qty = window.quantityRules.clampToMax(
                this.minQuantity,
                this.quantityIncrement,
                this.maxStock
            );

            this.error =
                `Solo hay ${this.maxStock} piezas disponibles.`;

            return;
        }


        if (normalized !== this.qty) {

            this.qty = normalized;

            this.error = this.quantityIncrement > 1
                ? `La cantidad debe ser en incrementos de ${this.quantityIncrement}.`
                : null;

            return;
        }


        this.error = null;

    }

}'

    class="min-w-0 rounded-2xl border border-neutral-200 bg-white p-5 shadow-sm sm:p-7 lg:sticky lg:top-28">


    {{-- ================================================================ --}}
    {{-- MARCA --}}
    {{-- ================================================================ --}}

    <p
        class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-[10px] font-extrabold uppercase tracking-[0.16em] text-amber-900">
        {{ $product->brand->name ?? 'IFIX' }}
    </p>


    {{-- ================================================================ --}}
    {{-- NOMBRE --}}
    {{-- ================================================================ --}}

    <h1
        class="mt-3 break-words font-heading text-[1.4rem] font-extrabold leading-tight tracking-tight text-neutral-950 [hyphens:auto] sm:text-3xl lg:text-4xl" lang="es">
        {{ $product->name }}
    </h1>


    {{-- ================================================================ --}}
    {{-- SKU --}}
    {{-- ================================================================ --}}

    @if($product->inventory->sku)

    <div
        class="mt-3 flex min-w-0 items-center gap-2 break-all text-xs text-neutral-500">

        <span class="font-medium">
            SKU:
        </span>

        <span>
            {{ $product->inventory->sku }}
        </span>

    </div>

    @endif


    {{-- ================================================================ --}}
    {{-- SEPARADOR --}}
    {{-- ================================================================ --}}

    <div class="my-5 border-t border-neutral-100 sm:my-6"></div>


    {{-- ================================================================ --}}
    {{-- PRECIO --}}
    {{-- ================================================================ --}}

    <div>

        <p
            class="font-heading text-3xl font-extrabold tracking-tight text-neutral-950 tabular-nums sm:text-4xl">
            {{ $product->price->formatted }}
        </p>

        @if ($product->price->compareFormatted)
            <p
                class="mt-1 flex flex-wrap items-baseline gap-x-2 text-sm tabular-nums text-neutral-500 sm:text-base">
                <span class="sr-only">Precio anterior:</span>
                <s>{{ $product->price->compareFormatted }}</s>
                <span class="font-extrabold text-red-700 dark:text-red-400">-{{ $product->price->discountPercent }}%</span>
            </p>
        @endif

        <p
            class="mt-1.5 text-xs text-neutral-500">
            Precio con IVA incluido.
        </p>

    </div>


    {{-- ================================================================ --}}
    {{-- DISPONIBILIDAD --}}
    {{-- ================================================================ --}}

    <div class="mt-5 rounded-xl bg-[#f5f5f3] p-4 sm:mt-6" role="status">

        @if($available && $stock > 0)

        <div class="flex items-center gap-3">

            <span class="relative flex h-3 w-3">
                <span class="absolute inline-flex h-full w-full motion-safe:animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-500"></span>
            </span>

            <span
                class="text-sm font-bold text-emerald-700">
                Disponible
            </span>

        </div>

        <p class="mt-1.5 text-xs text-neutral-600">

            {{ $stock }}

            {{ $stock === 1 ? 'pieza disponible' : 'piezas disponibles' }}

            para entrega.

        </p>

        @elseif($available)

        <div class="flex items-center gap-3">

            <span
                class="h-3 w-3 rounded-full bg-amber-500"></span>

            <span
                class="text-sm font-bold text-amber-800">
                Disponible bajo pedido
            </span>

        </div>

        <p class="mt-1.5 text-xs text-neutral-600">
            El tiempo de entrega puede variar según disponibilidad del proveedor.
        </p>

        @else

        <div class="flex items-center gap-3">

            <span
                class="h-3 w-3 rounded-full bg-red-500"></span>

            <span
                class="text-sm font-bold text-red-700">
                Agotado
            </span>

        </div>

        <p class="mt-1.5 text-xs text-neutral-600">
            Actualmente no tenemos unidades disponibles.
        </p>

        @endif

    </div>


    {{-- ================================================================ --}}
    {{-- SEPARADOR --}}
    {{-- ================================================================ --}}

    <div class="my-5 border-t border-neutral-100 sm:my-6"></div>


    {{-- ================================================================ --}}
    {{-- CANTIDAD --}}
    {{-- ================================================================ --}}

    <div>

        <label
            for="product-qty" class="mb-2.5 block text-sm font-bold text-neutral-800">
            Cantidad
        </label>


        <div
            class="flex w-40 items-center overflow-hidden rounded-xl border border-neutral-200 bg-white">

            <button
                type="button"
                @click="decrease()"
                :disabled="loading || qty <= quantityRules.floor(minQuantity, quantityIncrement)"
                class="flex h-11 w-11 items-center justify-center text-xl font-semibold text-neutral-800 transition hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-40"
                aria-label="Disminuir cantidad">
                −
            </button>


            <input
                id="product-qty"
                x-model.number="qty"
                @input="validateQuantity()"
                @change="validateQuantity()"
                type="number"
                min="{{ $minQuantity }}"
                step="{{ $quantityIncrement }}"
                :disabled="
        loading ||
        {{ $canPurchase ? 'false' : 'true' }}
    "
                inputmode="numeric"
                class="w-full min-w-0 border-0 bg-transparent text-center text-base font-semibold text-neutral-900 [appearance:textfield] focus:ring-0 sm:text-sm [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                aria-label="Cantidad">


            <button
                type="button"
                @click="increase()"
                :disabled="loading || qty >= maxStock"
                class="flex h-11 w-11 items-center justify-center text-xl font-semibold text-neutral-800 transition hover:bg-neutral-100 disabled:cursor-not-allowed disabled:opacity-40"
                aria-label="Aumentar cantidad">
                +
            </button>

        </div>

    </div>


    {{-- ================================================================ --}}
    {{-- ERROR --}}
    {{-- ================================================================ --}}

    <template x-if="error">

        <div
            role="alert" class="mt-5 flex items-start gap-3 rounded-2xl border border-error/20 bg-error-container px-4 py-4 text-sm text-on-error-container">

            <span class="material-symbols-outlined">
                error
            </span>

            <p x-text="error"></p>

        </div>

    </template>


    {{-- ================================================================ --}}
    {{-- BOTONES --}}
    {{-- ================================================================ --}}

    <div x-ref="actions" class="mt-6 space-y-3">

        <p class="sr-only" aria-live="polite" x-text="added ? 'Producto agregado al carrito' : ''"></p>


        {{-- AGREGAR AL CARRITO --}}
        {{-- Agregar al carrito --}}
        <button
            type="button"
            @click="addToCart()"
            :disabled="
        loading ||
        {{ $canPurchase ? 'false' : 'true' }}
    "
            class="flex min-h-12 w-full items-center justify-center gap-2 rounded-full bg-[#f5a623] px-6 py-3.5 text-sm font-extrabold text-neutral-950 shadow-sm transition hover:bg-amber-400 active:scale-[.99] disabled:cursor-not-allowed disabled:bg-neutral-200 disabled:text-neutral-500 disabled:shadow-none">
            <template x-if="loading">
                <svg
                    class="h-5 w-5 animate-spin"
                    viewBox="0 0 24 24"
                    fill="none">
                    <circle
                        cx="12"
                        cy="12"
                        r="9"
                        stroke="currentColor"
                        stroke-width="3"
                        class="opacity-30" />

                    <path
                        d="M21 12a9 9 0 0 1-9 9"
                        stroke="currentColor"
                        stroke-width="3"
                        stroke-linecap="round" />
                </svg>
            </template>

            <template x-if="!loading && !added">
                <span class="material-symbols-outlined">
                    shopping_cart
                </span>
            </template>

            <template x-if="!loading && added">
                <span class="material-symbols-outlined">
                    check_circle
                </span>
            </template>

            <span
                x-text="
            loading
                ? 'Agregando...'
                : added
                    ? 'Agregado al carrito'
                    : 'Agregar al carrito'
        "></span>
        </button>


        {{-- Comprar ahora --}}
        <button
            type="button"
            @click="buyNow()"
            :disabled="
        loading ||
        {{ $canPurchase ? 'false' : 'true' }}
    "
            class="flex min-h-12 w-full items-center justify-center gap-2 rounded-full border border-neutral-300 px-6 py-3 text-sm font-bold text-neutral-900 transition active:bg-neutral-100 [@media(hover:hover)]:hover:border-neutral-950 [@media(hover:hover)]:hover:bg-neutral-950 [@media(hover:hover)]:hover:text-white disabled:cursor-not-allowed disabled:border-neutral-200 disabled:text-neutral-400">
            <span class="material-symbols-outlined">
                bolt
            </span>

            Comprar ahora
        </button>








    </div>


    {{-- ================================================================ --}}
    {{-- ENVÍO --}}
    {{-- ================================================================ --}}

    @include('products.partials.shipping')


    {{-- ================================================================ --}}
    {{-- BENEFICIOS --}}
    {{-- ================================================================ --}}

    <div
        class="mt-6 rounded-2xl bg-neutral-950 p-5 text-white sm:p-6">

        <h3
            class="mb-4 font-heading text-sm font-extrabold text-white">
            ¿Por qué comprar con nosotros?
        </h3>


        <div class="space-y-4">

            @foreach([
                ['title' => 'Pago 100% seguro', 'text' => 'Todas las transacciones están protegidas.'],
                ['title' => 'Envíos a todo México', 'text' => 'Entregas mediante paqueterías confiables.'],
                ['title' => 'Garantía del fabricante', 'text' => 'Todos nuestros productos cuentan con garantía oficial.'],
                ['title' => 'Atención personalizada', 'text' => 'Nuestro equipo puede ayudarte antes y después de tu compra.'],
            ] as $reason)

            <div class="flex items-start gap-3">

                <span class="material-symbols-outlined text-lg text-[#f5a623]" aria-hidden="true">
                    check_circle
                </span>

                <div>

                    <p class="text-sm font-bold text-white">
                        {{ $reason['title'] }}
                    </p>

                    <p class="mt-0.5 text-xs leading-5 text-neutral-300">
                        {{ $reason['text'] }}
                    </p>

                </div>

            </div>

            @endforeach

        </div>

    </div>


    {{-- ================================================================ --}}
    {{-- BARRA DE COMPRA FIJA (MÓVIL) --}}
    {{-- ================================================================ --}}

    @if ($canPurchase)
    <div
        x-show="showBar"
        x-transition:enter="transition duration-200 ease-out"
        x-transition:enter-start="translate-y-full"
        x-transition:enter-end="translate-y-0"
        x-transition:leave="transition duration-150 ease-in"
        x-transition:leave-start="translate-y-0"
        x-transition:leave-end="translate-y-full"
        class="fixed inset-x-0 bottom-0 z-40 border-t border-neutral-200 bg-white/95 px-4 pt-3 dark:bg-[#17191b]/95 pb-[max(0.75rem,env(safe-area-inset-bottom))] shadow-[0_-8px_24px_rgba(0,0,0,.08)] backdrop-blur lg:hidden"
        style="display:none">

        <div class="mx-auto flex max-w-xl items-center gap-3">

            <div class="min-w-0 flex-1">
                <p class="truncate text-xs text-neutral-500">{{ $product->name }}</p>
                <p class="font-heading text-lg font-extrabold leading-tight text-neutral-950 tabular-nums">
                    {{ $product->price->formatted }}
                </p>
            </div>

            <button
                type="button"
                @click="addToCart()"
                :disabled="loading"
                class="flex shrink-0 items-center justify-center gap-1.5 rounded-full bg-[#f5a623] px-5 py-3 text-sm font-extrabold text-neutral-950 shadow-sm transition active:scale-[.98] disabled:opacity-60">
                <span class="material-symbols-outlined text-xl" aria-hidden="true"
                    x-text="added ? 'check_circle' : 'shopping_cart'">shopping_cart</span>
                <span x-text="loading ? 'Agregando...' : added ? 'Agregado' : 'Agregar'">Agregar</span>
            </button>

        </div>

    </div>
    @endif

</div>
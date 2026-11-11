import Alpine from 'alpinejs';

window.Alpine = Alpine;


// ============================================================
// SWIPER
// ============================================================

import Swiper from 'swiper';

import {
    Navigation,
    Pagination,
    Autoplay,
    EffectFade
} from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';
import 'swiper/css/effect-fade';

window.Swiper = Swiper;

window.SwiperModules = {
    Navigation,
    Pagination,
    Autoplay,
};


// ============================================================
// CARRUSEL DEL HERO
// ============================================================
//
// Los slides fuera de pantalla quedan "inert" para que el foco
// con teclado no llegue a sus enlaces y desacomode el carrusel.

//
// Transición en fundido; la entrada del texto y el acercamiento de la
// foto los anima el CSS del slide activo (ver .hero-* en app.css). El
// anillo del botón "siguiente" muestra cuánto falta para cambiar de
// oferta, y el contador indica en cuál vas.

const syncHeroSlides = (swiper) => {
    swiper.slides.forEach((slide, index) => {
        slide.inert = index !== swiper.activeIndex;
    });

    const current = swiper.el.querySelector('[data-hero-current]');

    if (current) {
        current.textContent = String(swiper.realIndex + 1).padStart(2, '0');
    }
};

document.querySelectorAll('[data-hero-carousel]').forEach((carousel) => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    new Swiper(carousel, {
        modules: [Navigation, Pagination, Autoplay, EffectFade],
        effect: 'fade',
        fadeEffect: { crossFade: true },
        rewind: true,
        speed: 800,
        autoplay: reduceMotion
            ? false
            : { delay: 6000, disableOnInteraction: false, pauseOnMouseEnter: true },
        navigation: {
            prevEl: carousel.querySelector('[data-hero-prev]'),
            nextEl: carousel.querySelector('[data-hero-next]'),
        },
        pagination: {
            el: carousel.querySelector('.swiper-pagination'),
            clickable: true,
        },
        on: {
            init: syncHeroSlides,
            slideChange: syncHeroSlides,
            // progress va de 1 a 0 mientras corre el tiempo del slide.
            autoplayTimeLeft: (swiper, timeLeft, progress) => {
                carousel.style.setProperty('--hero-progress', String(1 - progress));
            },
        },
    });
});


// ============================================================
// REGLAS DE CANTIDAD (min_quantity / quantity_increment)
// ============================================================
//
// Espejo en JS de App\Support\VariantQuantityRules — misma
// regla que valida Lunar en el servidor (CartLineQuantity):
// la cantidad debe ser múltiplo de "increment" y no menor a "min".
// Esto es solo para que la UI no proponga cantidades inválidas;
// la validación real sigue ocurriendo siempre en el backend.

window.quantityRules = {

    normalize(quantity, min, increment) {

        min = Math.max(1, parseInt(min) || 1);

        increment = Math.max(1, parseInt(increment) || 1);

        quantity = Math.max(parseInt(quantity) || 0, min);

        const remainder = quantity % increment;

        return remainder === 0
            ? quantity
            : quantity + (increment - remainder);
    },


    floor(min, increment) {

        return this.normalize(1, min, increment);
    },


    next(quantity, min, increment, max) {

        const step = Math.max(1, parseInt(increment) || 1);

        const value =
            this.normalize(quantity, min, increment) + step;

        return (max || max === 0)
            ? Math.min(value, max)
            : value;
    },


    prev(quantity, min, increment) {

        const step = Math.max(1, parseInt(increment) || 1);

        const floor = this.floor(min, increment);

        const value =
            this.normalize(quantity, min, increment) - step;

        return Math.max(value, floor);
    },


    /**
     * La cantidad válida más grande que no exceda "max".
     * Si ni siquiera la cantidad mínima cabe en "max", regresa
     * la cantidad mínima de todas formas (el llamador debe usar
     * "available" para decidir si mostrar el selector).
     */
    clampToMax(min, increment, max) {

        const step = Math.max(1, parseInt(increment) || 1);

        const floor = this.floor(min, increment);

        max = Math.max(0, parseInt(max) || 0);

        if (max < floor) {
            return floor;
        }

        return Math.max(
            Math.floor(max / step) * step,
            floor
        );
    },

};


// ============================================================
// CART WIDGET
// ============================================================

window.cartWidget = function () {

    return {

        dataUrl: null,

        quantity: 0,

        items: [],

        subtotal: '$0.00',

        tax: '$0.00',

        shipping: '$0.00',

        freeShipping: false,

        subtotalAmount: 0,

        amountUntilFreeShipping: 0,

        freeShippingFrom: 0,

        estimatedShipping: 0,

        get freeShippingProgress() {
            if (this.freeShippingFrom <= 0) {
                return 100;
            }

            return Math.min(
                100,
                (this.subtotalAmount / this.freeShippingFrom) * 100
            );
        },

        get estimatedShippingLabel() {
            return this.freeShipping
                ? 'Gratis'
                : `$${this.estimatedShipping.toFixed(2)} MXN`;
        },

        total: '$0.00',

        open: false,

        loading: false,


        init(dataUrl, freeShippingFrom, estimatedShipping, initialCart = null) {

            this.dataUrl = dataUrl;
            this.freeShippingFrom = freeShippingFrom;
            this.estimatedShipping = estimatedShipping;

            console.log(
                'CART INIT:',
                this.dataUrl
            );

            const cart = initialCart || window.ifixCartState;

            if (cart) {
                this.applyCart(cart);
            } else {
                this.refresh();
            }

            window.addEventListener(
                'cart-updated',
                (event) => {
                    if (event.detail) {
                        this.applyCart(event.detail);
                    } else {
                        this.refresh();
                    }
                }
            );
        },


        async refresh() {

            if (!this.dataUrl) {

                console.error(
                    'CART WIDGET: No se recibió la URL del carrito.'
                );

                return;
            }


            try {

                console.log(
                    'CONSULTANDO CARRITO:',
                    this.dataUrl
                );


                const response = await fetch(
                    this.dataUrl,
                    {
                        method: 'GET',

                        headers: {
                            'Accept': 'application/json'
                        },

                        credentials: 'same-origin',

                        cache: 'no-store'
                    }
                );


                const contentType =
                    response.headers.get('content-type') || '';


                if (!response.ok) {

                    const text = await response.text();

                    console.error(
                        'RESPUESTA ERROR CART:',
                        text
                    );

                    throw new Error(
                        `Error HTTP ${response.status}`
                    );
                }


                if (!contentType.includes('application/json')) {

                    throw new Error(
                        'El servidor no devolvió JSON.'
                    );
                }


                const data =
                    await response.json();


                console.log(
                    'RESPUESTA CART:',
                    data
                );


                if (
                    !data.success ||
                    !data.cart
                ) {

                    throw new Error(
                        'Respuesta inválida del carrito.'
                    );
                }


                this.applyCart(
                    data.cart
                );


            } catch (error) {

                console.error(
                    'ERROR CART WIDGET:',
                    error
                );

            }

        },


        applyCart(cart) {

            window.ifixCartState = cart;

            this.quantity =
                Number(
                    cart?.quantity ?? 0
                );


            this.items =
                Array.isArray(cart?.items)
                    ? cart.items
                    : [];


            this.subtotal =
                cart?.subtotal ?? '$0.00';


            this.tax =
                cart?.tax ?? '$0.00';


            this.shipping =
                cart?.shipping ?? '$0.00';

            this.freeShipping =
                Boolean(cart?.freeShipping);

            this.subtotalAmount =
                Number(cart?.subtotalAmount ?? 0);

            this.amountUntilFreeShipping =
                Number(cart?.amountUntilFreeShipping ?? 0);

            this.freeShippingFrom =
                Number(cart?.freeShippingFrom ?? this.freeShippingFrom);

            this.estimatedShipping =
                Number(cart?.estimatedShipping ?? this.estimatedShipping);


            this.total =
                cart?.total ?? '$0.00';


            console.log(
                'CART STATE:',
                {
                    quantity: this.quantity,
                    items: this.items
                }
            );
        },


        async updateItem(
            lineId,
            quantity
        ) {

            if (this.loading) {
                return;
            }


            this.loading = true;


            try {

                const response = await fetch(
                    `/carrito/items/${lineId}`,
                    {
                        method: 'PATCH',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                document.querySelector(
                                    'meta[name="csrf-token"]'
                                )?.content || ''
                        },

                        credentials: 'same-origin',

                        body: JSON.stringify({
                            quantity: quantity
                        })
                    }
                );


                const contentType =
                    response.headers.get('content-type') || '';


                const data =
                    contentType.includes('application/json')
                        ? await response.json()
                        : null;


                if (!response.ok) {

                    throw new Error(
                        data?.message ||
                        'No se pudo actualizar el producto.'
                    );
                }


                if (!data?.success) {

                    throw new Error(
                        data?.message ||
                        'No se pudo actualizar el producto.'
                    );
                }


                this.applyCart(
                    data.cart
                );


                window.dispatchEvent(
                    new CustomEvent(
                        'cart-updated',
                        {
                            detail: data.cart
                        }
                    )
                );


            } catch (error) {

                console.error(
                    'Error actualizando cantidad:',
                    error
                );

                alert(
                    error.message ||
                    'No se pudo actualizar la cantidad.'
                );


            } finally {

                this.loading = false;

            }
        },


        async removeItem(lineId) {

            if (this.loading) {
                return;
            }


            this.loading = true;


            try {

                const response = await fetch(
                    `/carrito/items/${lineId}`,
                    {
                        method: 'DELETE',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                document.querySelector(
                                    'meta[name="csrf-token"]'
                                )?.content || ''
                        },

                        credentials: 'same-origin'
                    }
                );


                const contentType =
                    response.headers.get('content-type') || '';


                const data =
                    contentType.includes('application/json')
                        ? await response.json()
                        : null;


                if (!response.ok) {

                    throw new Error(
                        data?.message ||
                        'No se pudo eliminar el producto.'
                    );
                }


                if (!data?.success) {

                    throw new Error(
                        data?.message ||
                        'No se pudo eliminar el producto.'
                    );
                }


                this.applyCart(
                    data.cart
                );


                window.dispatchEvent(
                    new CustomEvent(
                        'cart-updated',
                        {
                            detail: data.cart
                        }
                    )
                );


            } catch (error) {

                console.error(
                    'Error eliminando producto:',
                    error
                );

                alert(
                    error.message ||
                    'No se pudo eliminar el producto.'
                );


            } finally {

                this.loading = false;

            }
        },


        async clearCart() {

            if (this.loading) {
                return;
            }


            if (this.items.length === 0) {
                return;
            }


            if (
                !confirm(
                    '¿Seguro que quieres vaciar el carrito?'
                )
            ) {
                return;
            }


            this.loading = true;


            try {

                const response = await fetch(
                    `/carrito`,
                    {
                        method: 'DELETE',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                document.querySelector(
                                    'meta[name="csrf-token"]'
                                )?.content || ''
                        },

                        credentials: 'same-origin'
                    }
                );


                const contentType =
                    response.headers.get('content-type') || '';


                const data =
                    contentType.includes('application/json')
                        ? await response.json()
                        : null;


                if (!response.ok) {

                    throw new Error(
                        data?.message ||
                        'No se pudo vaciar el carrito.'
                    );
                }


                if (!data?.success) {

                    throw new Error(
                        data?.message ||
                        'No se pudo vaciar el carrito.'
                    );
                }


                this.applyCart(
                    data.cart
                );


                window.dispatchEvent(
                    new CustomEvent(
                        'cart-updated',
                        {
                            detail: data.cart
                        }
                    )
                );


            } catch (error) {

                console.error(
                    'Error vaciando carrito:',
                    error
                );

                alert(
                    error.message ||
                    'No se pudo vaciar el carrito.'
                );


            } finally {

                this.loading = false;

            }
        }

    };

};


// ============================================================
// ANIMACIÓN AL AGREGAR AL CARRITO
// ============================================================
//
// Una copia de la foto del producto "vuela" hasta el carrito del
// navbar ([data-cart-target]), el icono rebota y el aviso de
// partials/cart-toast muestra qué se agregó. Se llama solo después de
// que el servidor confirmó el producto en el carrito.

const flyToCart = (sourceImage) => {
    const target = [...document.querySelectorAll('[data-cart-target]')]
        .find((element) => element.offsetParent !== null);

    if (!target) {
        return;
    }

    const bump = () => target.animate(
        [{ transform: 'scale(1)' }, { transform: 'scale(1.22)' }, { transform: 'scale(0.94)' }, { transform: 'scale(1)' }],
        { duration: 450, easing: 'ease-out' }
    );

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const from = sourceImage?.getBoundingClientRect();

    if (reduceMotion || !from || from.width === 0) {
        bump();
        return;
    }

    const to = target.getBoundingClientRect();
    const size = Math.min(from.width, from.height, 220);
    const startX = from.left + from.width / 2 - size / 2;
    const startY = from.top + from.height / 2 - size / 2;
    const dx = to.left + to.width / 2 - (startX + size / 2);
    const dy = to.top + to.height / 2 - (startY + size / 2);

    const flyer = document.createElement('img');
    flyer.src = sourceImage.currentSrc || sourceImage.src;
    flyer.alt = '';
    flyer.setAttribute('aria-hidden', 'true');
    Object.assign(flyer.style, {
        position: 'fixed',
        left: `${startX}px`,
        top: `${startY}px`,
        width: `${size}px`,
        height: `${size}px`,
        objectFit: 'contain',
        padding: '10px',
        background: '#f4f4f2',
        borderRadius: '18px',
        boxShadow: '0 20px 50px -12px rgba(0, 0, 0, 0.55), 0 0 0 2px #f5a623',
        zIndex: '9999',
        pointerEvents: 'none',
    });
    document.body.appendChild(flyer);

    // Sube un poco y luego cae en arco hacia el carrito mientras se encoge.
    flyer.animate([
        { transform: 'translate(0, 0) scale(1)', opacity: 1 },
        { transform: 'translate(0, -24px) scale(1.06)', opacity: 1, offset: 0.15 },
        { transform: `translate(${dx * 0.55}px, ${dy * 0.55 - 90}px) scale(0.55)`, opacity: 1, offset: 0.6 },
        { transform: `translate(${dx}px, ${dy}px) scale(0.1)`, opacity: 0.35 },
    ], { duration: 850, easing: 'cubic-bezier(0.55, 0, 0.35, 1)' }).finished
        .catch(() => {})
        .finally(() => {
            flyer.remove();
            bump();
        });
};

window.ifixCartAdded = ({ sourceImage = null, name = '', image = null, quantity = 1 } = {}) => {
    flyToCart(sourceImage);

    window.dispatchEvent(new CustomEvent('cart-added', {
        detail: { name, image: image || sourceImage?.currentSrc || sourceImage?.src || null, quantity },
    }));
};


// ============================================================
// AGREGAR AL CARRITO (cards de producto)
// ============================================================

window.addToCartButton = function (url, variantId, minQuantity = 1, quantityIncrement = 1, product = {}) {

    return {

        loading: false,

        added: false,

        variantId: variantId,


        async add() {

            if (this.loading || !this.variantId) {
                return;
            }

            this.loading = true;


            const quantity = window.quantityRules.floor(
                minQuantity,
                quantityIncrement
            );


            try {

                const response = await fetch(
                    url,
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',

                            'Accept': 'application/json',

                            'X-CSRF-TOKEN':
                                document.querySelector(
                                    'meta[name="csrf-token"]'
                                )?.content || ''
                        },

                        credentials: 'same-origin',

                        body: JSON.stringify({
                            variant_id: this.variantId,
                            quantity: quantity
                        })
                    }
                );


                const contentType =
                    response.headers.get('content-type') || '';

                const data =
                    contentType.includes('application/json')
                        ? await response.json()
                        : null;


                if (!response.ok || !data?.success) {

                    throw new Error(
                        data?.message ||
                        'No se pudo agregar el producto al carrito.'
                    );
                }


                this.added = true;

                window.dispatchEvent(
                    new CustomEvent(
                        'cart-updated',
                        {
                            detail: data.cart
                        }
                    )
                );

                window.ifixCartAdded({
                    sourceImage: this.$el.closest('article')?.querySelector('[data-product-image]'),
                    name: product.name,
                    image: product.image,
                    quantity,
                });

                setTimeout(() => {
                    this.added = false;
                }, 2000);


            } catch (error) {

                console.error(
                    'Error agregando al carrito:',
                    error
                );

                alert(
                    error.message ||
                    'No se pudo agregar el producto al carrito.'
                );

            } finally {

                this.loading = false;

            }
        }

    };

};


// ============================================================
// BARRA DE BÚSQUEDA (sugerencias mientras se escribe)
// ============================================================
//
// El servidor hace la coincidencia aproximada (errores de escritura,
// sinónimos); aquí solo se consulta con debounce y se muestran los
// productos debajo de la barra.

window.searchBox = function (suggestionsUrl) {

    return {

        query: '',

        results: [],

        open: false,

        loading: false,

        searched: false,

        active: -1,

        controller: null,


        init() {
            this.query = this.$refs.input.value;
        },


        async fetchResults() {

            const search = this.query.trim();

            this.controller?.abort();

            if (search.length < 2) {
                this.results = [];
                this.searched = false;
                this.open = false;
                this.loading = false;
                return;
            }

            this.controller = new AbortController();

            this.loading = true;

            try {

                const url = new URL(suggestionsUrl, window.location.origin);

                url.searchParams.set('search', search);

                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    signal: this.controller.signal,
                });

                if (!response.ok) {
                    throw new Error(`Error HTTP ${response.status}`);
                }

                const data = await response.json();

                this.results = Array.isArray(data.products) ? data.products : [];
                this.active = -1;
                this.searched = true;
                this.open = true;
                this.loading = false;

            } catch (error) {

                if (error.name === 'AbortError') {
                    return;
                }

                console.error('Error buscando sugerencias:', error);

                this.loading = false;
            }
        },


        move(step) {

            if (!this.results.length) {
                return;
            }

            this.open = true;

            this.active = (this.active + step + this.results.length) % this.results.length;
        },


        submit(event) {

            // Con flecha seleccionada, Enter abre ese producto.
            if (this.open && this.results[this.active]) {
                event.preventDefault();
                window.location.href = this.results[this.active].url;
            }
        },


        catalogUrl() {

            const url = new URL(this.$root.action, window.location.origin);

            url.searchParams.set('search', this.query.trim());

            return url.toString();
        },


        close() {
            this.open = false;
            this.active = -1;
        },

    };

};


// ============================================================
// ACCIONES DELEGADAS (reemplazan onclick/onchange inline, que la
// CSP bloquea con script-src sin 'unsafe-inline')
// ============================================================

document.addEventListener('change', (event) => {
    if (event.target.matches('[data-autosubmit]')) {
        event.target.form?.submit();
    }
});

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-action="reload-page"]')) {
        window.location.reload();
    }
});


// ============================================================
// ALPINE
// ============================================================

Alpine.start();
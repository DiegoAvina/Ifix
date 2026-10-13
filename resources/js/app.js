import Alpine from 'alpinejs';

window.Alpine = Alpine;


// ============================================================
// SWIPER
// ============================================================

import Swiper from 'swiper';

import {
    Navigation,
    Pagination,
    Autoplay
} from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

window.Swiper = Swiper;

window.SwiperModules = {
    Navigation,
    Pagination,
    Autoplay,
};


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

        total: '$0.00',

        open: false,

        loading: false,


        init(dataUrl) {

            this.dataUrl = dataUrl;

            console.log(
                'CART INIT:',
                this.dataUrl
            );

            this.refresh();

            window.addEventListener(
                'cart-updated',
                () => {
                    this.refresh();
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
// AGREGAR AL CARRITO (cards de producto)
// ============================================================

window.addToCartButton = function (url, variantId, minQuantity = 1, quantityIncrement = 1) {

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
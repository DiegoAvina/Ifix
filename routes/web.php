<?php

use Illuminate\Support\Facades\Route;
use Lunar\Models\Product;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\SearchSuggestionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Payment\MercadoPagoController;
use App\Http\Controllers\Payment\PaymentWebhookController;



/*
|--------------------------------------------------------------------------
| TIENDA
|--------------------------------------------------------------------------
*/

Route::bind('product', function (string $value): Product {
    if (ctype_digit($value)) {
        return Product::query()->findOrFail((int) $value);
    }

    return Product::query()
        ->whereHas('urls', fn ($query) => $query->where('slug', $value)->where('default', true))
        ->firstOrFail();
});

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('profile.edit');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/productos', [CatalogController::class, 'index'])
    ->name('catalog.index');


Route::get('/productos/{product}', ProductController::class)
    ->name('catalog.show');


/*
|--------------------------------------------------------------------------
| CARRITO
|--------------------------------------------------------------------------
*/

Route::get('/carrito', [CartController::class, 'index'])
    ->name('cart.index');


Route::get('/carrito/data', [CartController::class, 'data'])
    ->name('cart.data');


Route::post('/carrito/items', [CartController::class, 'store'])
    ->name('cart.items.store');


Route::patch('/carrito/items/{line}', [CartController::class, 'update'])
    ->name('cart.items.update');


Route::delete('/carrito/items/{line}', [CartController::class, 'destroy'])
    ->name('cart.items.destroy');


Route::delete('/carrito', [CartController::class, 'clear'])
    ->name('cart.clear');


/*
|--------------------------------------------------------------------------
| CHECKOUT
|--------------------------------------------------------------------------
|
| El checkout requiere que el cliente haya iniciado sesión.
|
*/




/*
|--------------------------------------------------------------------------
| ENVÍO
|--------------------------------------------------------------------------
*/

Route::post(
    '/shipping/postal-code',
    [ShippingController::class, 'update']
)->name('shipping.postal-code');


Route::delete(
    '/shipping/postal-code',
    [ShippingController::class, 'destroy']
)->name('shipping.postal-code.destroy');


/*
|--------------------------------------------------------------------------
| BÚSQUEDA
|--------------------------------------------------------------------------
*/

Route::get(
    '/api/search/suggestions',
    SearchSuggestionController::class
)->middleware('throttle:search-suggestions')
    ->name('search.suggestions');


/*
|--------------------------------------------------------------------------
| PERFIL DEL CLIENTE
|--------------------------------------------------------------------------
|
| Estas rutas requieren autenticación.
|
*/

Route::middleware('auth')->group(function () {

    Route::get(
        '/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');


    Route::patch(
        '/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');


    Route::delete(
        '/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');

});


/*
|--------------------------------------------------------------------------
| AUTENTICACIÓN BREEZE
|--------------------------------------------------------------------------
|
| /login
| /register
| /logout
| recuperación de contraseña
| verificación de correo
|
*/
Route::middleware('auth')->group(function () {

    Route::get('/checkout', [CheckoutController::class, 'index'])
        ->name('checkout.index');

    Route::post('/checkout/address', [CheckoutController::class, 'storeAddress'])
        ->name('checkout.address.store');

    Route::post('/checkout/address/{address}/shipping', [CheckoutController::class, 'useShippingAddress'])
        ->name('checkout.address.shipping');

    Route::post('/checkout/address/{address}/billing', [CheckoutController::class, 'useBillingAddress'])
        ->name('checkout.address.billing');

    Route::post('/checkout/place', [CheckoutController::class, 'place'])
        ->middleware('throttle:checkout-place')
        ->name('checkout.place');

    Route::get('/checkout/confirmacion/{order}', [OrderController::class, 'show'])
        ->name('checkout.confirmation');

    Route::get('/mis-pedidos', [OrderController::class, 'index'])
        ->name('orders.index');

});


/*
|--------------------------------------------------------------------------
| PAGOS — MERCADO PAGO (Checkout Pro)
|--------------------------------------------------------------------------
|
| Iniciar el pago y las return URLs requieren sesión (son la Order del
| propio usuario). El webhook NO lleva sesión ni CSRF — lo llama el
| servidor de Mercado Pago, y su autenticidad se valida con x-signature
| (ver PaymentWebhookController y bootstrap/app.php).
|
*/

Route::middleware('auth')->group(function () {

    Route::post('/pagos/mercadopago/{orderId}', [PaymentController::class, 'payWithMercadoPago'])
        ->name('payments.mercadopago.pay');

    Route::get('/pagos/mercadopago/success', [MercadoPagoController::class, 'success'])
        ->name('payments.mercadopago.success');

    Route::get('/pagos/mercadopago/failure', [MercadoPagoController::class, 'failure'])
        ->name('payments.mercadopago.failure');

    Route::get('/pagos/mercadopago/pending', [MercadoPagoController::class, 'pending'])
        ->name('payments.mercadopago.pending');

});


Route::post('/payments/mercadopago/webhook', [PaymentWebhookController::class, 'handle'])
    ->middleware('throttle:mercadopago-webhook')
    ->name('payments.mercadopago.webhook');


require __DIR__.'/auth.php';
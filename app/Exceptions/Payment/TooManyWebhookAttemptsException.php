<?php

namespace App\Exceptions\Payment;

use Exception;

/**
 * Se volvió a consultar el mismo pago en Mercado Pago (mismo data_id)
 * más veces de lo razonable dentro de la ventana en la que su firma
 * sigue siendo válida. No implica firma inválida ni datos corruptos
 * — el controller la traduce a 429, no a 401 ni a 200.
 */
class TooManyWebhookAttemptsException extends Exception {}

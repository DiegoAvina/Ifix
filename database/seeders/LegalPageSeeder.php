<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

/**
 * Crea el aviso de privacidad con un texto base si aún no existe. Nunca
 * sobrescribe lo que se haya editado en el panel de Lunar.
 *
 * Los textos entre [CORCHETES] son datos del negocio que hay que completar;
 * conviene que un abogado revise la versión final.
 */
class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        LegalPage::query()->firstOrCreate(
            ['slug' => LegalPage::PRIVACY],
            [
                'title' => 'Aviso de privacidad',
                'content' => $this->privacyNotice(),
                'published' => true,
            ],
        );
    }

    private function privacyNotice(): string
    {
        return <<<'HTML'
<p><strong>[NOMBRE O RAZÓN SOCIAL]</strong> (en adelante, "IFIX"), con domicilio en <strong>[DOMICILIO COMPLETO]</strong>, es responsable del tratamiento de sus datos personales conforme a la Ley Federal de Protección de Datos Personales en Posesión de los Particulares y demás normativa aplicable.</p>

<h2>Datos personales que recabamos</h2>
<p>Para las finalidades señaladas en este aviso, recabamos los siguientes datos cuando crea una cuenta, realiza una compra o se comunica con nosotros:</p>
<ul>
<li>Datos de identificación y contacto: nombre, correo electrónico y número de teléfono.</li>
<li>Datos de entrega y facturación: domicilio de envío y, en su caso, datos fiscales para emitir factura.</li>
<li>Datos de su cuenta y de sus pedidos: historial de compras, productos en su carrito y estado de sus pedidos.</li>
</ul>
<p>No recabamos datos personales sensibles. Los datos de su tarjeta los captura y procesa directamente Mercado Pago; IFIX no los recibe ni los almacena.</p>

<h2>Finalidades del tratamiento</h2>
<p><strong>Finalidades necesarias</strong> para la relación con usted:</p>
<ul>
<li>Crear y administrar su cuenta.</li>
<li>Procesar, cobrar, enviar y dar seguimiento a sus pedidos.</li>
<li>Enviarle confirmaciones de compra, de pago y de envío.</li>
<li>Emitir facturas cuando las solicite.</li>
<li>Atender dudas, aclaraciones, devoluciones y garantías.</li>
</ul>
<p><strong>Finalidades adicionales</strong>, que no son necesarias para la compra: enviarle promociones y novedades de productos. Si no desea que sus datos se usen para estas finalidades, escríbanos a <strong>[CORREO DE PRIVACIDAD]</strong>; su negativa no afectará sus compras.</p>

<h2>Transferencias de datos</h2>
<p>Para cumplir las finalidades necesarias, compartimos los datos indispensables con:</p>
<ul>
<li>Mercado Pago, para procesar los pagos.</li>
<li>Empresas de paquetería, para entregar sus pedidos.</li>
<li>Proveedores de servicios tecnológicos (alojamiento del sitio y envío de correos), que los tratan por cuenta de IFIX.</li>
</ul>
<p>Estas transferencias no requieren su consentimiento conforme a la ley. No vendemos ni rentamos sus datos personales.</p>

<h2>Derechos ARCO y revocación del consentimiento</h2>
<p>Usted puede acceder a sus datos, rectificarlos, cancelarlos u oponerse a su uso (derechos ARCO), así como revocar su consentimiento, enviando una solicitud a <strong>[CORREO DE PRIVACIDAD]</strong> que incluya: su nombre y un medio para responderle, una copia de su identificación oficial, la descripción clara de los datos y del derecho que desea ejercer, y cualquier documento que facilite localizar sus datos.</p>
<p>Le responderemos en un plazo máximo de 20 días hábiles. También puede actualizar su nombre y correo directamente en la sección "Mi cuenta" de la tienda.</p>

<h2>Cookies y tecnologías similares</h2>
<p>Nuestro sitio usa cookies necesarias para mantener su sesión iniciada, recordar su carrito de compras y proteger los formularios. Puede eliminarlas desde la configuración de su navegador, aunque algunas funciones de la tienda podrían dejar de funcionar.</p>

<h2>Cambios a este aviso</h2>
<p>Cualquier cambio a este aviso de privacidad se publicará en esta misma página, donde siempre encontrará la fecha de su última actualización.</p>

<h2>Contacto</h2>
<p>Para cualquier duda sobre el tratamiento de sus datos personales, escríbanos a <strong>[CORREO DE PRIVACIDAD]</strong> o llámenos al <strong>[TELÉFONO]</strong>.</p>
HTML;
    }
}

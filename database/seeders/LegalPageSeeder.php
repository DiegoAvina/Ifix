<?php

namespace Database\Seeders;

use App\Models\LegalPage;
use Illuminate\Database\Seeder;

/**
 * Crea el aviso de privacidad y los términos de servicio con un texto base si aún no existen. Nunca
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

        LegalPage::query()->firstOrCreate(
            ['slug' => LegalPage::TERMS],
            [
                'title' => 'Términos de servicio',
                'content' => $this->termsOfService(),
                'published' => true,
            ],
        );
    }

    private function termsOfService(): string
    {
        return <<<'HTML'
<p>Estos términos regulan el uso de la tienda en línea de <strong>[NOMBRE O RAZÓN SOCIAL]</strong> (en adelante, "IFIX"), con domicilio en <strong>[DOMICILIO COMPLETO]</strong> y RFC <strong>[RFC]</strong>, y la compra de sus productos para detallado automotriz. Al crear una cuenta o realizar un pedido, usted acepta estos términos y nuestro <a href="/aviso-de-privacidad">aviso de privacidad</a>.</p>

<h2>Cuenta de cliente</h2>
<ul>
<li>Para comprar necesita una cuenta con un correo electrónico verificado.</li>
<li>Usted es responsable de mantener la confidencialidad de su contraseña y de la información que registre, la cual debe ser verdadera y estar actualizada.</li>
<li>IFIX puede suspender cuentas usadas de forma fraudulenta o contraria a estos términos.</li>
</ul>

<h2>Productos e información</h2>
<ul>
<li>Las fotografías son ilustrativas; la presentación del envase puede variar sin afectar el contenido ni la calidad del producto.</li>
<li>Los rendimientos y tiempos de duración indicados son aproximados y dependen de la superficie, el clima, el mantenimiento y la forma de aplicación.</li>
<li>Son productos químicos para uso automotriz: léalos y aplíquelos siguiendo las instrucciones de la etiqueta, manténgalos fuera del alcance de los niños y haga una prueba en un área poco visible antes de usarlos. Algunos productos, como los recubrimientos cerámicos, requieren experiencia previa.</li>
</ul>

<h2>Precios y pagos</h2>
<ul>
<li>Los precios están en pesos mexicanos (MXN) y <strong>[incluyen / no incluyen]</strong> IVA. El costo de envío se muestra antes de confirmar el pedido.</li>
<li>Los pagos se procesan a través de Mercado Pago. IFIX no recibe ni almacena los datos de su tarjeta.</li>
<li>Si un precio se publicó con un error evidente, IFIX se lo notificará y podrá cancelar el pedido con el reembolso total de lo pagado.</li>
</ul>

<h2>Pedidos</h2>
<ul>
<li>Su pedido se confirma cuando Mercado Pago aprueba el pago; le enviaremos la confirmación por correo electrónico.</li>
<li>Los pedidos no pagados dentro del plazo indicado se cancelan automáticamente y no generan ningún cargo.</li>
<li>Todos los pedidos están sujetos a disponibilidad. Si un producto se agota después de su pago, le ofreceremos un cambio o el reembolso total.</li>
</ul>

<h2>Envíos</h2>
<ul>
<li>Enviamos a todo México por medio de empresas de paquetería. El tiempo estimado de entrega es de <strong>[PLAZO DE ENTREGA]</strong> días hábiles a partir de la confirmación del pago.</li>
<li>El envío es gratuito en compras desde <strong>[MONTO MÍNIMO]</strong>; en las demás compras se cobra la tarifa indicada en el checkout.</li>
<li>Verifique que la dirección sea correcta: los retrasos o costos por una dirección incorrecta o por ausencia en el domicilio corren por cuenta del cliente.</li>
<li>Revise su paquete al recibirlo y repórtenos cualquier daño en un plazo de <strong>[PLAZO PARA REPORTAR DAÑOS]</strong> días naturales, con fotografías del empaque y del producto.</li>
</ul>

<h2>Cancelaciones y devoluciones</h2>
<ul>
<li>Conforme a la Ley Federal de Protección al Consumidor, puede revocar su compra dentro de los 5 días hábiles siguientes a la entrega, sin responsabilidad alguna, siempre que el producto esté sin abrir, sin usar y en su empaque original. Los gastos de envío de la devolución corren por su cuenta.</li>
<li>Por razones de seguridad e higiene, no se aceptan devoluciones de productos abiertos o usados, salvo por defecto de fabricación.</li>
<li>Los reembolsos se hacen por el mismo medio de pago en un plazo máximo de <strong>[PLAZO DE REEMBOLSO]</strong> días hábiles después de recibir y revisar el producto.</li>
<li>Para iniciar una cancelación o devolución, escríbanos a <strong>[CORREO DE ATENCIÓN]</strong> con su número de pedido.</li>
</ul>

<h2>Garantía</h2>
<p>Nuestros productos están garantizados contra defectos de fabricación por <strong>[PLAZO DE GARANTÍA]</strong> a partir de su entrega. La garantía no cubre daños por uso distinto al indicado en la etiqueta, mezcla con otros productos, almacenamiento inadecuado ni aplicación sobre superficies no compatibles.</p>

<h2>Uso del sitio y propiedad intelectual</h2>
<p>Las marcas, logotipos, fotografías y textos de este sitio son propiedad de IFIX o de sus respectivos titulares y no pueden usarse sin autorización. Queda prohibido usar el sitio para fines ilícitos o intentar afectar su funcionamiento o seguridad.</p>

<h2>Cambios a estos términos</h2>
<p>IFIX puede actualizar estos términos; los cambios se publicarán en esta página con su fecha de actualización. Cada pedido se rige por los términos vigentes al momento de realizarlo.</p>

<h2>Legislación aplicable y quejas</h2>
<p>Estos términos se rigen por las leyes de los Estados Unidos Mexicanos, en particular la Ley Federal de Protección al Consumidor. Para cualquier controversia, las partes se someten a los tribunales de <strong>[CIUDAD Y ESTADO]</strong>, sin perjuicio de su derecho a acudir a la Procuraduría Federal del Consumidor (PROFECO).</p>

<h2>Contacto</h2>
<p>Para dudas, aclaraciones o quejas, escríbanos a <strong>[CORREO DE ATENCIÓN]</strong> o llámenos al <strong>[TELÉFONO]</strong>, de <strong>[HORARIO DE ATENCIÓN]</strong>.</p>
HTML;
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

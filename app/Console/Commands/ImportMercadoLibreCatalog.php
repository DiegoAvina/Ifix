<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use App\Services\Storefront\StorefrontAvailabilityQuery;
use Lunar\FieldTypes\Text;
use Lunar\FieldTypes\TranslatedText;
use Lunar\Models\Brand;
use Lunar\Models\Channel;
use Lunar\Models\Collection;
use Lunar\Models\CollectionGroup;
use Lunar\Models\Currency;
use Lunar\Models\CustomerGroup;
use Lunar\Models\Language;
use Lunar\Models\Price;
use Lunar\Models\Product;
use Lunar\Models\ProductOption;
use Lunar\Models\ProductType;
use Lunar\Models\ProductVariant;
use Lunar\Models\TaxClass;

class ImportMercadoLibreCatalog extends Command
{
    protected $signature = 'ifix:catalog:import
        {file : Local CSV file to stage}
        {--publish : Create draft Lunar products and publish only after images are uploaded}';

    protected $description = 'Stage reviewed Mercado Libre product data without downloading images';

    private const TABLE = 'ifix_catalog_imports';

    private const HEADERS = [
        'source_id',
        'source_url',
        'name',
        'brand',
        'description',
        'category_path',
        'presentation',
        'sku',
        'marketplace_price_mxn',
        'marketplace_compare_price_mxn',
        'marketplace_price_observed_at',
        'image_url',
        'image_rights_verified',
        'store_price_mxn',
        'store_compare_price_mxn',
        'stock',
        'data_verified',
        'stock_verified',
    ];

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! is_file($file) || ! is_readable($file)) {
            $this->error('No se puede leer el archivo CSV indicado.');

            return self::FAILURE;
        }

        if (filesize($file) > 5 * 1024 * 1024) {
            $this->error('El archivo supera el límite permitido de 5 MB.');

            return self::FAILURE;
        }

        $stream = fopen($file, 'rb');

        if ($stream === false) {
            $this->error('No se pudo abrir el archivo CSV.');

            return self::FAILURE;
        }

        $report = [
            'created' => 0,
            'updated' => 0,
            'omitted' => 0,
            'failed' => 0,
            'lunar_created' => 0,
            'published' => 0,
        ];

        try {
            $headers = fgetcsv($stream);

            if ($headers === false) {
                $this->error('El archivo CSV está vacío.');

                return self::FAILURE;
            }

            $headers[0] = ltrim((string) $headers[0], "\xEF\xBB\xBF");
            $headers = array_map(static fn ($header) => trim((string) $header), $headers);

            if (count($headers) !== count(array_unique($headers))
                || array_diff(self::HEADERS, $headers) !== []) {
                $this->error('El CSV debe incluir una sola vez todos los encabezados de la plantilla.');

                return self::FAILURE;
            }

            $line = 1;

            while (($values = fgetcsv($stream)) !== false) {
                $line++;

                if ($values === [null]) {
                    continue;
                }

                if (count($values) !== count($headers)) {
                    $report['failed']++;
                    $this->error("Línea {$line}: número de columnas incorrecto.");

                    continue;
                }

                try {
                    $row = $this->normalizeRow(array_combine($headers, $values));
                    [$importId, $created] = $this->stage($row);
                    $report[$created ? 'created' : 'updated']++;

                    if ($this->option('publish')) {
                        $result = $this->createOrPublish($importId);
                        $report['lunar_created'] += $result['created'] ? 1 : 0;
                        $report['published'] += $result['published'] ? 1 : 0;

                        if (! $result['published']) {
                            $report['omitted']++;
                            $this->warn("Línea {$line}: {$result['message']}");
                        }
                    }
                } catch (InvalidArgumentException|ValidationException $exception) {
                    $report['failed']++;
                    $message = $exception instanceof ValidationException
                        ? implode(' ', $exception->validator->errors()->all())
                        : $exception->getMessage();

                    $this->error("Línea {$line}: {$message}");
                }
            }
        } finally {
            fclose($stream);
        }

        $this->newLine();
        $this->info(sprintf(
            'Reporte: creados %d, actualizados %d, omitidos %d, fallidos %d; Lunar creados %d, publicados %d.',
            $report['created'],
            $report['updated'],
            $report['omitted'],
            $report['failed'],
            $report['lunar_created'],
            $report['published'],
        ));

        return $report['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @param  array<string, string|null>  $input
     * @return array<string, mixed>
     */
    private function normalizeRow(array $input): array
    {
        $row = [];

        foreach (self::HEADERS as $header) {
            $value = trim((string) ($input[$header] ?? ''));
            $row[$header] = $value === '' ? null : $value;
        }

        Validator::make($row, [
            'source_id' => ['required', 'regex:/^MLM[0-9]+(?:[:_-][A-Za-z0-9_-]+)?$/i', 'max:80'],
            'source_url' => ['required', 'url', 'max:2048'],
            'name' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'category_path' => ['nullable', 'string', 'max:255'],
            'presentation' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'marketplace_price_observed_at' => ['nullable', 'date'],
            'image_url' => ['nullable', 'url', 'max:2048'],
        ])->validate();

        $sourceHost = strtolower((string) parse_url($row['source_url'], PHP_URL_HOST));

        if (! $this->isMercadoLibreHost($sourceHost) || parse_url($row['source_url'], PHP_URL_SCHEME) !== 'https') {
            throw new InvalidArgumentException('La URL de origen debe ser HTTPS y pertenecer a mercadolibre.com.mx.');
        }

        if ($row['image_url'] !== null) {
            $imageHost = strtolower((string) parse_url($row['image_url'], PHP_URL_HOST));

            if (! $this->isMercadoLibreHost($imageHost)
                && $imageHost !== 'mlstatic.com'
                && ! str_ends_with($imageHost, '.mlstatic.com')) {
                throw new InvalidArgumentException('La URL de imagen debe pertenecer a Mercado Libre o mlstatic.com.');
            }

            if (parse_url($row['image_url'], PHP_URL_SCHEME) !== 'https') {
                throw new InvalidArgumentException('La URL de imagen debe usar HTTPS.');
            }
        }

        $row['image_rights_verified'] = $this->parseBoolean($row['image_rights_verified'], 'image_rights_verified');
        $row['data_verified'] = $this->parseBoolean($row['data_verified'], 'data_verified');
        $row['stock_verified'] = $this->parseBoolean($row['stock_verified'], 'stock_verified');

        foreach ([
            'marketplace_price_mxn',
            'marketplace_compare_price_mxn',
            'store_price_mxn',
            'store_compare_price_mxn',
        ] as $field) {
            $row[$field] = $this->moneyToMinor($row[$field], $field);
        }

        if ($row['marketplace_price_mxn'] !== null && $row['marketplace_price_observed_at'] === null) {
            throw new InvalidArgumentException('El precio observado requiere su fecha de consulta.');
        }
        if ($row['marketplace_compare_price_mxn'] !== null && $row['marketplace_price_observed_at'] === null) {
            throw new InvalidArgumentException('El precio comparativo observado requiere su fecha de consulta.');
        }

        foreach ([
            'marketplace_price_mxn',
            'marketplace_compare_price_mxn',
            'store_price_mxn',
            'store_compare_price_mxn',
        ] as $priceField) {
            if ($row[$priceField] !== null && $row[$priceField] <= 0) {
                throw new InvalidArgumentException("El importe {$priceField} debe ser mayor que cero.");
            }
        }

        if ($row['marketplace_compare_price_mxn'] !== null
            && $row['marketplace_price_mxn'] !== null
            && $row['marketplace_compare_price_mxn'] <= $row['marketplace_price_mxn']) {
            throw new InvalidArgumentException('El precio comparativo observado debe ser mayor que el precio observado.');
        }

        if ($row['stock'] !== null) {
            $stock = filter_var($row['stock'], FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0, 'max_range' => 4294967295],
            ]);

            if ($stock === false) {
                throw new InvalidArgumentException('El inventario debe ser un entero no negativo.');
            }

            $row['stock'] = $stock;
        }

        if ($row['stock_verified'] && $row['stock'] === null) {
            throw new InvalidArgumentException('No se puede marcar inventario verificado sin una cantidad.');
        }

        if ($row['store_compare_price_mxn'] !== null
            && $row['store_price_mxn'] !== null
            && $row['store_compare_price_mxn'] <= $row['store_price_mxn']) {
            throw new InvalidArgumentException('El precio comparativo de iFix debe ser mayor que el precio de venta.');
        }

        if ($row['data_verified']) {
            foreach (['name', 'brand', 'description', 'category_path', 'presentation', 'sku'] as $field) {
                if ($row[$field] === null) {
                    throw new InvalidArgumentException("El dato {$field} es obligatorio para marcar el producto verificado.");
                }
            }

            if (array_filter(array_map('trim', explode('>', $row['category_path']))) === []) {
                throw new InvalidArgumentException('La clasificación verificada debe contener al menos una categoría.');
            }
        }

        if ($row['image_url'] !== null && ! $row['image_rights_verified'] && $row['data_verified']) {
            throw new InvalidArgumentException('Confirma los derechos de uso de la imagen antes de marcar el producto verificado.');
        }

        return $row;
    }

    private function isMercadoLibreHost(string $host): bool
    {
        return $host === 'mercadolibre.com.mx' || str_ends_with($host, '.mercadolibre.com.mx');
    }

    private function parseBoolean(?string $value, string $field): bool
    {
        return match (strtolower((string) $value)) {
            '1', 'true', 'yes', 'si', 'sí' => true,
            null, '', '0', 'false', 'no' => false,
            default => throw new InvalidArgumentException("El campo {$field} acepta 1/0, true/false, yes/no o si/no."),
        };
    }

    private function moneyToMinor(?string $value, string $field): ?int
    {
        if ($value === null) {
            return null;
        }

        if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $value)) {
            throw new InvalidArgumentException("El campo {$field} debe ser un importe MXN con hasta dos decimales.");
        }

        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        $whole = ltrim($whole, '0') ?: '0';
        $maximumWhole = (string) intdiv(PHP_INT_MAX, 100);
        $fractionMinor = (int) str_pad($fraction, 2, '0');

        if (strlen($whole) > strlen($maximumWhole)
            || (strlen($whole) === strlen($maximumWhole) && strcmp($whole, $maximumWhole) > 0)
            || ($whole === $maximumWhole && $fractionMinor > PHP_INT_MAX % 100)) {
            throw new InvalidArgumentException("El campo {$field} excede el importe admitido.");
        }

        return ((int) $whole * 100) + $fractionMinor;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{int, bool}
     */
    private function stage(array $row): array
    {
        if ($row['sku'] !== null && DB::table(self::TABLE)
            ->where('source', 'mercadolibre_mx')
            ->where('source_id', '!=', $row['source_id'])
            ->whereRaw('LOWER(sku) = ?', [strtolower($row['sku'])])
            ->exists()) {
            throw new InvalidArgumentException('El SKU ya está asociado a otra publicación importada.');
        }

        $existing = DB::table(self::TABLE)
            ->where('source', 'mercadolibre_mx')
            ->where('source_id', $row['source_id'])
            ->first();

        $values = [
            'source_url' => $row['source_url'],
            'name' => $row['name'],
            'brand' => $row['brand'],
            'description' => $row['description'],
            'category_path' => $row['category_path'],
            'presentation' => $row['presentation'],
            'sku' => $row['sku'],
            'observed_price_minor' => $row['marketplace_price_mxn'],
            'observed_compare_price_minor' => $row['marketplace_compare_price_mxn'],
            'price_observed_at' => $row['marketplace_price_observed_at']
                ? Carbon::parse($row['marketplace_price_observed_at'])
                : null,
            'image_url' => $row['image_url'],
            'image_rights_verified' => $row['image_rights_verified'],
            'store_price_minor' => $row['store_price_mxn'],
            'store_compare_price_minor' => $row['store_compare_price_mxn'],
            'stock' => $row['stock'],
            'data_verified' => $row['data_verified'],
            'stock_verified' => $row['stock_verified'],
            'updated_at' => now(),
        ];

        if ($existing) {
            if ($existing->lunar_product_id && $existing->sku !== $row['sku']) {
                throw new InvalidArgumentException(
                    'No se puede cambiar el SKU de una publicación ya vinculada a Lunar.',
                );
            }

            $status = $existing->lunar_product_id
                ? $existing->import_status
                : ($row['data_verified'] ? 'verified' : 'pending_review');

            DB::table(self::TABLE)
                ->where('id', $existing->id)
                ->update([...$values, 'import_status' => $status]);

            return [$existing->id, false];
        }

        $id = DB::table(self::TABLE)->insertGetId([
            ...$values,
            'source' => 'mercadolibre_mx',
            'source_id' => $row['source_id'],
            'import_status' => $row['data_verified'] ? 'verified' : 'pending_review',
            'created_at' => now(),
        ]);

        return [$id, true];
    }

    /**
     * @return array{created: bool, published: bool, message: string}
     */
    private function createOrPublish(int $importId): array
    {
        return DB::transaction(function () use ($importId): array {
            $import = DB::table(self::TABLE)->where('id', $importId)->lockForUpdate()->first();
            $reason = $this->publicationBlocker($import);

            if ($reason !== null) {
                DB::table(self::TABLE)->where('id', $importId)->update([
                    'import_status' => 'pending_review',
                    'last_import_message' => $reason,
                    'updated_at' => now(),
                ]);

                return ['created' => false, 'published' => false, 'message' => $reason];
            }

            if ($import->lunar_product_id !== null) {
                $product = Product::query()->find($import->lunar_product_id);

                if (! $product) {
                    $message = 'El producto Lunar vinculado ya no existe; no se creó un duplicado.';
                    DB::table(self::TABLE)->where('id', $importId)->update([
                        'import_status' => 'linked_product_missing',
                        'last_import_message' => $message,
                        'updated_at' => now(),
                    ]);

                    return ['created' => false, 'published' => false, 'message' => $message];
                }

                if ($product->status === 'published') {
                    return ['created' => false, 'published' => true, 'message' => 'Ya estaba publicado; no se modificó.'];
                }

                $variant = $product->variants()->where('sku', $import->sku)->first();
                $currency = Currency::query()->where('code', 'MXN')->first();
                $customerGroup = CustomerGroup::query()->where('default', true)->first();

                if (! $variant || ! $currency || ! $customerGroup
                    || ! $variant->prices()
                        ->where('currency_id', $currency->id)
                        ->where('customer_group_id', $customerGroup->id)
                        ->exists()) {
                    $message = 'Falta la variante o su precio MXN del grupo predeterminado; se requiere revisión manual.';
                    DB::table(self::TABLE)->where('id', $importId)->update([
                        'import_status' => 'configuration_required',
                        'last_import_message' => $message,
                        'updated_at' => now(),
                    ]);

                    return ['created' => false, 'published' => false, 'message' => $message];
                }

                if (! app(StorefrontAvailabilityQuery::class)
                    ->apply(Product::query()->whereKey($product->id), true)
                    ->exists()) {
                    $message = 'El canal o grupo de clientes no tiene disponibilidad de compra configurada.';
                    DB::table(self::TABLE)->where('id', $importId)->update([
                        'import_status' => 'configuration_required',
                        'last_import_message' => $message,
                        'updated_at' => now(),
                    ]);

                    return ['created' => false, 'published' => false, 'message' => $message];
                }

                if (! $product->images()->exists()) {
                    $message = 'Falta cargar y revisar la imagen en Lunar; el producto permanece en borrador.';
                    DB::table(self::TABLE)->where('id', $importId)->update([
                        'import_status' => 'awaiting_image',
                        'last_import_message' => $message,
                        'updated_at' => now(),
                    ]);

                    return ['created' => false, 'published' => false, 'message' => $message];
                }

                $product->update(['status' => 'published']);
                DB::table(self::TABLE)->where('id', $importId)->update([
                    'import_status' => 'published',
                    'last_import_message' => 'Publicado; precios e inventario Lunar se conservaron.',
                    'updated_at' => now(),
                ]);

                return ['created' => false, 'published' => true, 'message' => 'Publicado.'];
            }

            if (ProductVariant::query()
                ->whereRaw('LOWER(sku) = ?', [strtolower($import->sku)])
                ->exists()) {
                $message = 'El SKU ya existe en Lunar; se omitió para evitar duplicados o sobrescribir datos comerciales.';
                DB::table(self::TABLE)->where('id', $importId)->update([
                    'import_status' => 'sku_conflict',
                    'last_import_message' => $message,
                    'updated_at' => now(),
                ]);

                return ['created' => false, 'published' => false, 'message' => $message];
            }

            $language = Language::query()->where('code', 'es')->first();
            $currency = Currency::query()->where('code', 'MXN')->first();
            $channel = Channel::query()->where('handle', 'webstore')->first();
            $customerGroup = CustomerGroup::query()->where('default', true)->first();
            $taxClass = TaxClass::query()->where('default', true)->first();
            $productType = ProductType::query()->first();

            $missingConfiguration = [];

            if (! $language) {
                $missingConfiguration[] = 'idioma español';
            }
            if (! $currency || (int) $currency->decimal_places !== 2) {
                $missingConfiguration[] = 'moneda MXN con 2 decimales';
            }
            if (! $channel) {
                $missingConfiguration[] = 'canal Webstore';
            }
            if (! $customerGroup) {
                $missingConfiguration[] = 'grupo de clientes predeterminado';
            }
            if (! $taxClass) {
                $missingConfiguration[] = 'clase fiscal predeterminada';
            }
            if (! $productType) {
                $missingConfiguration[] = 'tipo de producto';
            }

            if ($missingConfiguration !== []) {
                $message = 'Falta configurar en Lunar: '.implode(', ', $missingConfiguration).'.';
                DB::table(self::TABLE)->where('id', $importId)->update([
                    'import_status' => 'configuration_required',
                    'last_import_message' => $message,
                    'updated_at' => now(),
                ]);

                return ['created' => false, 'published' => false, 'message' => $message];
            }

            $brand = Brand::query()->firstOrCreate(['name' => $import->brand]);
            $product = Product::query()->create([
                'product_type_id' => $productType->id,
                'brand_id' => $brand->id,
                'status' => 'draft',
                'attribute_data' => collect([
                    'name' => $this->translatedText($import->name, $language->code),
                    'description' => $this->translatedText($import->description, $language->code),
                ]),
            ]);

            $variant = $product->variants()->create([
                'tax_class_id' => $taxClass->id,
                'sku' => $import->sku,
                'unit_quantity' => 1,
                'min_quantity' => 1,
                'quantity_increment' => 1,
                'shippable' => true,
                'stock' => $import->stock,
                'purchasable' => 'in_stock',
            ]);

            Price::query()->create([
                'priceable_type' => ProductVariant::morphName(),
                'priceable_id' => $variant->id,
                'currency_id' => $currency->id,
                'customer_group_id' => $customerGroup->id,
                'min_quantity' => 1,
                'price' => $import->store_price_minor,
                'compare_price' => $import->store_compare_price_minor,
            ]);

            $option = ProductOption::query()->create([
                'name' => [$language->code => 'Presentación'],
            ]);
            $value = $option->values()->create([
                'name' => [$language->code => $import->presentation],
            ]);
            $product->productOptions()->attach($option->id, ['position' => 1]);
            $variant->values()->attach($value->id);

            $availability = [
                'enabled' => true,
                'starts_at' => now(),
                'ends_at' => null,
            ];
            $product->channels()->syncWithoutDetaching([$channel->id => $availability]);
            $product->customerGroups()->syncWithoutDetaching([
                $customerGroup->id => [...$availability, 'visible' => true, 'purchasable' => true],
            ]);

            $collection = $this->resolveCategory($import->category_path, $language->code);
            $collection->channels()->syncWithoutDetaching([$channel->id => $availability]);
            $collection->customerGroups()->syncWithoutDetaching([
                $customerGroup->id => [...$availability, 'visible' => true],
            ]);
            $position = (int) $collection->products()->max('position') + 1;
            $collection->products()->syncWithoutDetaching([
                $product->id => ['position' => $position],
            ]);

            $message = 'Producto creado como borrador; carga y revisa una imagen en Lunar antes de publicar.';
            DB::table(self::TABLE)->where('id', $importId)->update([
                'lunar_product_id' => $product->id,
                'import_status' => 'awaiting_image',
                'last_import_message' => $message,
                'updated_at' => now(),
            ]);

            return ['created' => true, 'published' => false, 'message' => $message];
        });
    }

    private function publicationBlocker(object $import): ?string
    {
        if (! $import->data_verified) {
            return 'Datos del producto pendientes de verificación.';
        }

        if (! $import->stock_verified || $import->stock === null) {
            return 'Inventario no verificado; el producto no se creó ni publicó.';
        }

        if ($import->store_price_minor === null) {
            return 'Falta definir el precio comercial propio de iFix.';
        }

        if (! $import->image_rights_verified) {
            return 'Los derechos de uso de la imagen no están verificados.';
        }

        return null;
    }

    private function translatedText(string $value, string $locale): TranslatedText
    {
        return new TranslatedText(collect([
            $locale => new Text($value),
        ]));
    }

    private function resolveCategory(string $path, string $locale): Collection
    {
        $group = CollectionGroup::query()->firstOrCreate(
            ['handle' => 'main'],
            ['name' => 'Principal'],
        );
        $parent = null;
        $current = null;

        foreach (array_filter(array_map('trim', explode('>', $path))) as $name) {
            $query = Collection::query()
                ->inGroup($group->id)
                ->where('parent_id', $parent?->id);

            if ($parent === null) {
                $query->whereNull('parent_id');
            }

            $current = $query->get()->first(
                fn (Collection $candidate) => $candidate->translateAttribute('name') === $name,
            );

            if (! $current) {
                $current = Collection::query()->create([
                    'collection_group_id' => $group->id,
                    'parent_id' => $parent?->id,
                    'attribute_data' => collect([
                        'name' => $this->translatedText($name, $locale),
                    ]),
                ]);
            }

            $parent = $current;
        }

        if (! $current) {
            throw new InvalidArgumentException('La clasificación verificada del producto está vacía.');
        }

        return $current;
    }
}

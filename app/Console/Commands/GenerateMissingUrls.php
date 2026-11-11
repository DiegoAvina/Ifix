<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Lunar\Models\Collection;
use Lunar\Models\Product;

/**
 * Crea la URL amigable (slug) de productos y categorías que no la tienen.
 *
 * Lunar la genera sola al crear desde el panel, pero no cuando se crean
 * con seeders (corren sin eventos de modelo) o scripts. Sin slug, la
 * página queda en /productos/{id}, que no dice nada a los buscadores.
 * Nunca cambia un slug existente: eso rompería enlaces ya indexados.
 */
class GenerateMissingUrls extends Command
{
    protected $signature = 'seo:generate-urls';

    protected $description = 'Genera la URL amigable de productos y categorías que no tienen una';

    public function handle(): int
    {
        $generator = app(config('lunar.urls.generator'));
        $created = 0;

        foreach ([Product::class, Collection::class] as $model) {
            $model::query()->whereDoesntHave('urls')->with('urls')->each(function ($record) use ($generator, &$created): void {
                $generator->handle($record);
                $slug = $record->urls()->where('default', true)->value('slug');

                if ($slug) {
                    $created++;
                    $this->line(class_basename($record).' #'.$record->getKey().' → '.$slug);
                }
            });
        }

        $this->info($created ? "URLs creadas: {$created}" : 'Todos los productos y categorías ya tienen URL.');

        return self::SUCCESS;
    }
}

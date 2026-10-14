<?php

namespace Tests\Feature;

use App\Services\Storefront\HomeService;
use Mockery\MockInterface;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    public function test_homepage_displays_the_automotive_storefront(): void
    {
        $this->mock(HomeService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('data')
                ->once()
                ->andReturn([
                    'slides' => [],
                    'brands' => [],
                    'products' => [],
                    'collections' => [],
                ]);
        });

        $this->get('/')
            ->assertOk()
            ->assertSee('El detalle hace la diferencia')
            ->assertSee('Categorías')
            ->assertSee('Protección cerámica');
    }
}

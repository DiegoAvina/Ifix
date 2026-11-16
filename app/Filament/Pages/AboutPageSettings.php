<?php

namespace App\Filament\Pages;

use App\Models\AboutPage;
use App\Services\Storefront\CategoryService;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Página "Nosotros" de la tienda (/nosotros): misión, visión, valores,
 * cifras e historia. Una sola fila en about_pages.
 */
class AboutPageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationLabel = 'Nosotros';

    protected static ?string $navigationGroup = 'Tienda en línea';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Nosotros: misión, visión y valores';

    protected static ?string $slug = 'nosotros';

    protected static string $view = 'filament.pages.about-page-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(AboutPage::current()->attributesToArray());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ver')
                ->label('Ver en la tienda')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('gray')
                ->url(route('about'))
                ->openUrlInNewTab(),
        ];
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Forms\Components\Section::make('Encabezado')
                    ->description('Lo primero que se ve al entrar a la página.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('eyebrow')
                            ->label('Etiqueta superior')
                            ->placeholder('Nosotros')
                            ->maxLength(60),
                        Forms\Components\Toggle::make('published')
                            ->label('Página publicada')
                            ->helperText('Si se apaga, /nosotros deja de verse y sale del menú.')
                            ->inline(false),
                        Forms\Components\TextInput::make('headline')
                            ->label('Título principal')
                            ->required()
                            ->maxLength(120)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('intro')
                            ->label('Introducción')
                            ->rows(3)
                            ->maxLength(600)
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Misión y visión')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Textarea::make('mission')
                            ->label('Misión')
                            ->helperText('Qué hacemos hoy y para quién.')
                            ->rows(5)
                            ->maxLength(800),
                        Forms\Components\Textarea::make('vision')
                            ->label('Visión')
                            ->helperText('A dónde queremos llegar.')
                            ->rows(5)
                            ->maxLength(800),
                    ]),

                Forms\Components\Section::make('Valores')
                    ->description('Agrega, quita o arrastra para cambiar el orden.')
                    ->schema([
                        Forms\Components\Repeater::make('values')
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\Select::make('icon')
                                    ->label('Ícono')
                                    ->options(AboutPage::ICONS)
                                    ->default('verified')
                                    ->required()
                                    ->native(false),
                                Forms\Components\TextInput::make('title')
                                    ->label('Valor')
                                    ->required()
                                    ->maxLength(40),
                                Forms\Components\Textarea::make('description')
                                    ->label('Descripción')
                                    ->rows(2)
                                    ->maxLength(220)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->addActionLabel('Agregar valor')
                            ->maxItems(12)
                            ->defaultItems(0),
                    ]),

                Forms\Components\Section::make('Proceso de detallado')
                    ->description('Los pasos del lavado al acabado espejo. Cada paso puede enlazar a una categoría de la tienda (muestra su número de productos). Si no hay pasos, la sección no aparece.')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Repeater::make('process')
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Paso')
                                    ->placeholder('Lavar')
                                    ->required()
                                    ->maxLength(30),
                                Forms\Components\Select::make('category')
                                    ->label('Categoría que enlaza')
                                    ->placeholder('Sin enlace')
                                    ->options(fn (): array => collect(app(CategoryService::class)->all())
                                        ->filter(fn (array $category) => $category['slug'])
                                        ->mapWithKeys(fn (array $category) => [$category['slug'] => $category['name']])
                                        ->all()),
                                Forms\Components\Textarea::make('text')
                                    ->label('Descripción')
                                    ->rows(2)
                                    ->maxLength(160)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                            ->addActionLabel('Agregar paso')
                            ->maxItems(6)
                            ->defaultItems(0),
                    ]),

                Forms\Components\Section::make('Cifras')
                    ->description('Opcional. Ej.: "+2,000" clientes, "48 h" de envío. Si no hay cifras, la sección no aparece.')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Repeater::make('stats')
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\TextInput::make('value')
                                    ->label('Cifra')
                                    ->placeholder('+2,000')
                                    ->required()
                                    ->maxLength(12),
                                Forms\Components\TextInput::make('label')
                                    ->label('Descripción')
                                    ->placeholder('clientes felices')
                                    ->required()
                                    ->maxLength(40),
                            ])
                            ->columns(2)
                            ->reorderableWithButtons()
                            ->addActionLabel('Agregar cifra')
                            ->maxItems(6)
                            ->defaultItems(0),
                    ]),

                Forms\Components\Section::make('Nuestra historia')
                    ->description('Opcional. Momentos importantes en orden. Si no hay, la sección no aparece.')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Repeater::make('milestones')
                            ->hiddenLabel()
                            ->schema([
                                Forms\Components\TextInput::make('year')
                                    ->label('Año o fecha')
                                    ->placeholder('2024')
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('title')
                                    ->label('Título')
                                    ->required()
                                    ->maxLength(80),
                                Forms\Components\Textarea::make('text')
                                    ->label('Detalle')
                                    ->rows(2)
                                    ->maxLength(300)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => trim(($state['year'] ?? '').' '.($state['title'] ?? '')) ?: null)
                            ->addActionLabel('Agregar momento')
                            ->maxItems(12)
                            ->defaultItems(0),
                    ]),

                Forms\Components\Section::make('Cierre')
                    ->description('Invitación al final de la página, con botón al catálogo.')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('closing_title')
                            ->label('Título')
                            ->maxLength(100),
                        Forms\Components\TextInput::make('closing_text')
                            ->label('Texto')
                            ->maxLength(250),
                    ]),
            ]);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('Guardar cambios')->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Los repeaters guardan con claves aleatorias: se guardan como lista.
        foreach (['values', 'process', 'stats', 'milestones'] as $list) {
            $data[$list] = array_values($data[$list] ?? []);
        }

        AboutPage::query()->firstOrNew()->fill($data)->save();

        $this->form->fill($data);

        Notification::make()->title('Página "Nosotros" guardada')->success()->send();
    }
}

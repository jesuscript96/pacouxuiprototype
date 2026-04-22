<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\BajasColaboradores\Pages;

use App\Filament\Cliente\Resources\BajasColaboradores\BajaColaboradorResource;
use App\Imports\BajasColaboradores\BajasMasivasImport;
use App\Models\Empresa;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportarBajas extends Page
{
    protected static string $resource = BajaColaboradorResource::class;

    protected static ?string $title = 'Baja masiva de colaboradores';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.cliente.resources.bajas-colaboradores.pages.importar-bajas';

    public ?array $data = [];

    /** @var list<array{fila: int, status: string, mensaje: string}> */
    public array $resultados = [];

    public bool $mostrarResultados = false;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('Create:BajaColaborador'), 403);
        $this->form->fill();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Importar archivo')
                    ->description('Suba un archivo Excel (.xlsx). Elimine la fila de ejemplo de la plantilla antes de importar.')
                    ->schema([
                        FileUpload::make('archivo')
                            ->label('Archivo Excel')
                            ->required()
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->maxSize(5120)
                            ->storeFiles(false)
                            ->helperText('Solo .xlsx / .xls. Máximo 5 MB.'),
                    ]),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('importar')
                    ->footer([
                        Actions::make([
                            Action::make('importar')
                                ->label('Procesar bajas')
                                ->submit('importar'),
                        ]),
                    ]),
            ]);
    }

    public function importar(): void
    {
        $tenant = Filament::getTenant();
        if (! $tenant instanceof Empresa) {
            Notification::make()
                ->title('Error')
                ->body('No hay empresa seleccionada.')
                ->danger()
                ->send();

            return;
        }

        $data = $this->form->getState();
        $file = $data['archivo'] ?? null;
        if ($file === null) {
            Notification::make()
                ->title('Error')
                ->body('Debe seleccionar un archivo.')
                ->danger()
                ->send();

            return;
        }

        try {
            $path = $file->getRealPath();
            if ($path === false || ! is_readable($path)) {
                throw new \RuntimeException('No se pudo leer el archivo subido.');
            }

            $import = new BajasMasivasImport($tenant->id);
            Excel::import($import, $path);

            $this->resultados = $import->getResultados();
            $this->mostrarResultados = true;

            $procesadas = $import->getProcesadas();
            $errores = $import->getErrores();

            if ($errores === 0) {
                Notification::make()
                    ->title('Importación completada')
                    ->body("Se procesaron {$procesadas} baja(s) correctamente.")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Importación con errores')
                    ->body("Procesadas: {$procesadas}. Errores: {$errores}.")
                    ->warning()
                    ->send();
            }
        } catch (Throwable $e) {
            Notification::make()
                ->title('Error al procesar archivo')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        $this->form->fill();
    }

    public function limpiarResultados(): void
    {
        $this->resultados = [];
        $this->mostrarResultados = false;
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('descargarPlantilla')
                ->label('Descargar plantilla')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn (): string => route('cliente.plantilla.bajas-colaboradores', [
                    'empresa' => Filament::getTenant(),
                ])),
        ];
    }
}

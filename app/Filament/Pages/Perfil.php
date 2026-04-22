<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class Perfil extends Page
{
    use CanUseDatabaseTransactions;

    protected static ?string $navigationLabel = 'Mi perfil';

    protected static ?string $title = 'Mi perfil';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->fillForm();
    }

    protected function getUsuario(): User
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }

    protected function fillForm(): void
    {
        $user = $this->getUsuario();
        $this->form->fill([
            'nombre' => $user->nombre,
            'apellido_paterno' => $user->apellido_paterno,
            'apellido_materno' => $user->apellido_materno,
            'email' => $user->email,
            'telefono' => $user->telefono,
            'celular' => $user->celular,
            'imagen' => $user->imagen,
            'enable_2fa' => $user->enable_2fa,
            'verified_2fa_at' => $user->verified_2fa_at,
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->model($this->getUsuario())
            ->operation('edit')
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos personales')
                    ->schema([
                        TextInput::make('nombre')->required()->maxLength(255),
                        TextInput::make('apellido_paterno')->label('Apellido paterno')->required()->maxLength(255),
                        TextInput::make('apellido_materno')->label('Apellido materno')->required()->maxLength(255),
                        TextInput::make('email')->email()->disabled()->dehydrated(false),
                        TextInput::make('telefono')->tel()->maxLength(20),
                        TextInput::make('celular')->tel()->maxLength(20),
                        FileUpload::make('imagen')
                            ->label('Foto')
                            ->image()
                            ->maxSize(2048)
                            ->directory('usuarios')
                            ->visibility('public'),
                    ])
                    ->columns(2),
                Section::make('Cambiar contraseña')
                    ->schema([
                        TextInput::make('current_password')
                            ->password()
                            ->label('Contraseña actual')
                            ->dehydrated(false),
                        TextInput::make('new_password')
                            ->password()
                            ->label('Nueva contraseña')
                            ->minLength(8)
                            ->same('new_password_confirmation')
                            ->dehydrated(false),
                        TextInput::make('new_password_confirmation')
                            ->password()
                            ->label('Confirmar nueva contraseña')
                            ->dehydrated(false),
                    ])
                    ->columns(2)
                    ->collapsible(),
                Section::make('Autenticación de dos factores')
                    ->schema([
                        Toggle::make('enable_2fa')->label('Verificación en dos pasos')->disabled()->dehydrated(false),
                        DateTimePicker::make('verified_2fa_at')->label('Verificado el')->disabled()->dehydrated(false),
                    ])
                    ->visible(fn (): bool => filled($this->getUsuario()->google2fa_secret) || $this->getUsuario()->enable_2fa),
            ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Guardar')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = $this->getUsuario();

        if (! empty($data['new_password'])) {
            if (! Hash::check($data['current_password'] ?? '', $user->password)) {
                Notification::make()->danger()->title('La contraseña actual no es correcta.')->send();

                return;
            }
            $user->password = $data['new_password'];
        }

        $user->nombre = $data['nombre'];
        $user->apellido_paterno = $data['apellido_paterno'];
        $user->apellido_materno = $data['apellido_materno'];
        $user->telefono = $data['telefono'] ?? null;
        $user->celular = $data['celular'] ?? null;
        $user->imagen = $data['imagen'] ?? $user->imagen;
        $user->save();

        Notification::make()->success()->title('Perfil actualizado correctamente.')->send();
    }

    public static function getSlug(?Panel $panel = null): string
    {
        return 'perfil';
    }
}

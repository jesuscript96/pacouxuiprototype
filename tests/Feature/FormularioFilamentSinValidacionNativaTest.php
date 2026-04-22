<?php

declare(strict_types=1);

use Filament\Schemas\Components\Form;

it('aplica novalidate a los Form de esquema Filament', function (): void {
    $form = Form::make([]);

    expect($form->getExtraAttributeBag()->getAttributes())->toHaveKey('novalidate');
});

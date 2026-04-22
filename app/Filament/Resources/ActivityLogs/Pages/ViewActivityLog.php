<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogs\Pages;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use Filament\Resources\Pages\ViewRecord;
use Spatie\Activitylog\Models\Activity;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var Activity $record */
        $record = $this->getRecord();
        $record->loadMissing('causer');
        $causer = $record->causer;
        if ($causer !== null) {
            $name = $causer->name ?? '';
            $email = $causer->email ?? '';
            $data['causer_display'] = trim($name) !== ''
                ? (trim((string) $email) !== '' ? "{$name} ({$email})" : $name)
                : (trim((string) $email) !== '' ? $email : '—');
        } else {
            $data['causer_display'] = '—';
        }

        return $data;
    }
}

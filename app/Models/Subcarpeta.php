<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Subcarpeta extends Model
{
    use HasFactory;

    protected $table = 'subcarpetas';

    protected $fillable = [
        'carpeta_id',
        'nombre',
        'url',
        'tipo',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function carpeta(): BelongsTo
    {
        return $this->belongsTo(Carpeta::class, 'carpeta_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (Subcarpeta $subcarpeta): void {
            $disk = Storage::disk('uploads');
            if ($subcarpeta->url !== '' && $disk->exists($subcarpeta->url)) {
                $disk->deleteDirectory($subcarpeta->url);
            }
        });
    }
}

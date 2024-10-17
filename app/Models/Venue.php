<?php

namespace App\Models;

use App\Enums\Region;
use Spatie\MediaLibrary\HasMedia;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Venue extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $casts = [
        'id' => 'integer',
        'region' => Region::class,
    ];

    public function conferences(): HasMany
    {
        return $this->hasMany(Conference::class);
    }

    public static function getForm(): array
    {
        return [
            TextInput::make('name')
                ->required()
                ->maxLength(255),
            TextInput::make('city')
                ->required()
                ->maxLength(255),
            TextInput::make('country')
                ->required()
                ->maxLength(255),
            TextInput::make('postal_code')
                ->required()
                ->maxLength(255),
            Select::make('region')
                ->enum(Region::class)
                ->options(Region::class),
            SpatieMediaLibraryFileUpload::make('images')
                ->collection('venue-images')
                ->imageEditor()
                ->multiple()
                ->image()
        ];
    }
}

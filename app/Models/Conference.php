<?php

namespace App\Models;

use Filament\Forms;
use App\Models\Talk;
use App\Enums\Region;
use App\Models\Venue;
use App\Models\Speaker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conference extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'region' => Region::class,
        'venue_id' => 'integer',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class);
    }

    public function talks(): BelongsToMany
    {
        return $this->belongsToMany(Talk::class);
    }

    public static function getForm(): array
    {
        return [
            Section::make('Conference Details')
            // ->aside()
            // ->collapsible()
                ->description('Provide some basic informationabout the conference.')
                ->icon('heroicon-o-information-circle')
                ->columns(2)
                // ->columns(['md' => 2, 'lg' => 3]) // controlling breakpoints
            ->schema([
                TextInput::make('name')
                    ->columnSpanFull()
                    ->label('Conference Name')
                    ->required()
                    // ->hint('Here is the hint')
                    // ->hintIcon('heroicon-o-rectangle-stack')
                    ->default('My Conference')
                    // ->helperText('The name of the conference.')
                    ->maxLength(60),
                MarkdownEditor::make('description')
                    ->columnSpanFull()
                    ->required(),
                DateTimePicker::make('start_date')
                    ->required(),
                DateTimePicker::make('end_date')
                    ->required(),
                Fieldset::make('Status')
                ->columns(1)
                ->schema([
                    Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                            'archived' => 'Archived',
                        ])
                        ->required(),
                    Toggle::make('is_published')
                        ->default(true),
                ])
            ]),

            Section::make('Location')
            ->columns(2)
            ->schema([
                Select::make('region')
                    ->live()
                    ->enum(Region::class)
                    ->options(Region::class),
                Select::make('venue_id')
                    ->searchable()
                    ->preload()
                    ->createOptionForm(Venue::getForm())
                    ->editOptionForm(Venue::getForm())
                    ->relationship('venue', 'name', modifyQueryUsing: function (Builder $query, Forms\Get $get) {
                        return $query->where('region', $get('region'));
                    }),
            ]),



            // CheckboxList::make('speakers')
            //     ->relationship('speakers', 'name')
            //     ->options(
            //        Speaker::all()->pluck('name', 'id')
            //     )
            //     ->required(),

        ];
    }
}

<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Talk;
use Filament\Tables;
use Filament\Forms\Form;
use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Resources\TalkResource\Pages;
use Filament\Tables\Actions\RestoreBulkAction;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TalkResource\RelationManagers;

class TalkResource extends Resource
{
    protected static ?string $model = Talk::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Second Group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema(Talk::getForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->persistFiltersInSession()
            ->filtersTriggerAction(function ($action) {
                return $action->button()->label('Filters');
            })
            ->columns([
                // Tables\Columns\TextInputColumn::make('title')
                //     ->sortable()
                //     ->rules(['required', 'max:255'])
                //     ->searchable(),
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    ->searchable()
                    ->description(function (Talk $record) {
                        return Str::of($record->abstract)->limit(40);
                    }),
                Tables\Columns\ImageColumn::make('speaker.avatar')
                    ->label('Speaker Avatar')
                    ->circular()
                    ->defaultImageUrl(function ($record) {
                        return 'https://ui-avatars.com/api/?background=0D8ABC&color=fff&name=' . urlencode($record->speaker->name);
                    }),
                // Tables\Columns\TextColumn::make('abstract')
                //     ->wrap(),
                Tables\Columns\TextColumn::make('speaker.name')
                    ->sortable()
                    ->searchable(),
                // Tables\Columns\IconColumn::make('new_talk')->boolean(),
                Tables\Columns\ToggleColumn::make('new_talk'),
                TextColumn::make('status')->badge()
                    ->badge()
                    ->sortable()
                    ->color(function ($state) {
                        return $state->getColor();
                    }),
                IconColumn::make('length')
                    ->icon(function ($state) {
                        return match ($state) {
                            TalkLength::NORMAL  => 'heroicon-o-megaphone',
                            TalkLength::LIGHTNING => 'heroicon-o-bolt',
                            TalkLength::KEYNOTE => 'heroicon-o-key',
                        };
                    }),

            ])
            ->filters([
                TernaryFilter::make('new_talk'),
                Tables\Filters\SelectFilter::make('speaker')
                    ->relationship('speaker', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Tables\Filters\Filter::make('has_avatar')
                    ->label('Show Only Speakers With Avatars')
                    ->toggle()
                    ->query(function ($query) {
                        return $query->whereHas('speaker', function (Builder $query) {
                            $query->whereNotNull('avatar');
                        });
                    }),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver(),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('approve')
                        ->visible(function ($record) {
                            return $record->status === (TalkStatus::SUBMITTED);
                        })
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(function (Talk $record) {
                            $record->approve();
                        })->after(function () {
                            Notification::make()->success()->title('This talk was approved')
                                ->duration(1000)
                                ->body('The speaker has been notified and the talk has bneem added top the schedule.')
                                ->send();
                        }),
                    Tables\Actions\Action::make('reject')
                        ->visible(function ($record) {
                            return $record->status === (TalkStatus::SUBMITTED);
                        })
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Talk $record) {
                            $record->reject();
                        })->after(function () {
                            Notification::make()->danger()->title('This talk was rejected')
                                ->duration(1000)
                                ->body('The speaker has been notified.')
                                ->send();
                        }),
                ]),


            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('apprive')
                    ->action(function (Collection $records) {
                        $records->each->approve();
                    }),


                    Tables\Actions\DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->tooltip('thism will export all filtered records.')
                    ->action(function ($livewire) {
                        dd($livewire->getFilteredTableQuery()->count());
                    })

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTalks::route('/'),
            'create' => Pages\CreateTalk::route('/create'),
            // commenting out turns into modal
            // 'edit' => Pages\EditTalk::route('/{record}/edit'),
        ];
    }
}

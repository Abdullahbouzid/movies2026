<?php

namespace App\Filament\Resources\Tournaments\RelationManagers;

use App\Models\TournamentParticipant;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MatchesRelationManager extends RelationManager
{
    protected static string $relationship = 'matches';

    protected static ?string $title = 'هيكلية المنافسة (القرعة)';

    protected static ?string $modelLabel = 'مباراة';

    protected static ?string $pluralModelLabel = 'المباريات';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('round')
                    ->label('الدور (رقم)')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                TextInput::make('position')
                    ->label('الترتيب داخل الدور')
                    ->numeric()
                    ->minValue(1)
                    ->required(),
                Select::make('player_one_id')
                    ->label('اللاعب (١)')
                    ->options(fn () => $this->participantOptions())
                    ->searchable(),
                Select::make('player_two_id')
                    ->label('اللاعب (٢)')
                    ->options(fn () => $this->participantOptions())
                    ->searchable(),
                Select::make('winner_id')
                    ->label('الفائز')
                    ->options(fn () => $this->participantOptions())
                    ->searchable(),
                TextInput::make('score_one')
                    ->label('نتيجة (١)')
                    ->numeric()
                    ->minValue(0),
                TextInput::make('score_two')
                    ->label('نتيجة (٢)')
                    ->numeric()
                    ->minValue(0),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('round')
                    ->label('الدور')
                    ->badge()
                    ->sortable(),
                TextColumn::make('round_label')
                    ->label('المرحلة')
                    ->placeholder('—'),
                TextColumn::make('playerOne.name')
                    ->label('اللاعب (١)')
                    ->placeholder('—'),
                TextColumn::make('playerTwo.name')
                    ->label('اللاعب (٢)')
                    ->placeholder('—'),
                TextColumn::make('score')
                    ->label('النتيجة')
                    ->state(fn ($record) => ($record->score_one ?? '—') . ' : ' . ($record->score_two ?? '—'))
                    ->alignCenter(),
                TextColumn::make('winner.name')
                    ->label('الفائز')
                    ->badge()
                    ->color('success')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('round')
                    ->label('الدور')
                    ->options(fn () => $this->ownerRecord->matches
                        ->pluck('round_label', 'round')
                        ->filter()
                        ->unique()
                        ->all()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('round');
    }

    /**
     * @return array<int, string>
     */
    private function participantOptions(): array
    {
        return $this->ownerRecord->participants
            ->mapWithKeys(fn (TournamentParticipant $p) => [$p->id => $p->name])
            ->all();
    }
}

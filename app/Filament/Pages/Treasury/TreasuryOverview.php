<?php

namespace App\Filament\Pages\Treasury;

use App\Models\TreasuryEntry;
use App\Support\Treasury;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * The consolidated cash desk: every activity's collected money in one table,
 * plus the pending balance that has not been chased yet.
 */
class TreasuryOverview extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'الخزينة والتقارير';

    protected static ?string $navigationLabel = 'الخزينة الرئيسية';

    protected static ?int $navigationSort = 9;

    protected static ?string $slug = 'treasury';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected string $view = 'filament.pages.treasury.overview';

    public ?string $from = null;

    public ?string $to = null;

    public function mount(): void
    {
        $this->from ??= today()->startOfMonth()->toDateString();
        $this->to ??= today()->toDateString();
    }

    public function getTitle(): string
    {
        return 'الخزينة الرئيسية';
    }

    public function getSubheading(): ?string
    {
        return 'إجمالي التحصيل لكل خدمة، والمبالغ المعلّقة التي لم تُحصَّل بعد.';
    }

    /**
     * @return array{rows: array<string, array<string, mixed>>, totals: array<string, float|int>}
     */
    public function getSummary(): array
    {
        return Treasury::summary($this->from, $this->to);
    }

    public function updatedFrom(): void
    {
        $this->resetTable();
    }

    public function updatedTo(): void
    {
        $this->resetTable();
    }

    /**
     * Union of the four booking tables, expressed as one queryable set.
     *
     * A SQL union keeps pagination, search and filters working across all
     * activities without loading every row into PHP. Each row carries a
     * synthetic key because the underlying tables share id values.
     */
    private function unionQuery(): Builder
    {
        $base = null;

        foreach (Treasury::activities() as $key => $meta) {
            /** @var class-string<Model> $model */
            $model = $meta['model'];

            $sub = $model::query()
                ->select([
                    'booking_ref',
                    'full_name',
                    'branch_id',
                    'price',
                    'payment_status',
                    'paid_at',
                    'created_at',
                ])
                ->selectRaw("'{$key}' as activity")
                ->selectRaw($this->rowKeyExpression($key).' as row_id');

            $base = $base === null ? $sub : $base->unionAll($sub);
        }

        return TreasuryEntry::query()
            ->fromSub($base->toBase(), 'treasury_rows')
            ->select('treasury_rows.*');
    }

    /**
     * Row keys are built inside each union branch so the derived table exposes a
     * real column; concatenation syntax differs between MySQL and SQLite.
     */
    private function rowKeyExpression(string $activity): string
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)
            ? "concat('{$activity}', '-', id)"
            : "'{$activity}' || '-' || id";
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->unionQuery())
            ->columns([
                TextColumn::make('activity')
                    ->label('الخدمة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Treasury::label((string) $state))
                    ->color(fn ($state) => Treasury::activities()[$state]['color'] ?? 'gray'),
                TextColumn::make('booking_ref')
                    ->label('رقم الحجز')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('full_name')
                    ->label('الاسم')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('المبلغ')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->label('الدفع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === 'paid' ? 'مدفوع' : 'قيد الانتظار')
                    ->color(fn ($state) => $state === 'paid' ? 'success' : 'warning'),
                TextColumn::make('paid_at')
                    ->label('تاريخ التحصيل')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('window')
                    ->label('الفترة')
                    ->form([
                        DatePicker::make('from')->label('من')->native(false),
                        DatePicker::make('to')->label('إلى')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $d) => $q->whereDate('paid_at', '>=', $d))
                        ->when($data['to'] ?? null, fn (Builder $q, $d) => $q->whereDate('paid_at', '<=', $d))),
                SelectFilter::make('activity')
                    ->label('الخدمة')
                    ->options(collect(Treasury::activities())->map(fn ($meta) => $meta['label'])->all()),
                SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options(['paid' => 'مدفوع', 'pending' => 'قيد الانتظار']),
            ])
            ->defaultSort('paid_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الفترة')
                ->columns(2)
                ->schema([
                    DatePicker::make('from')->label('من تاريخ')->native(false)->live(),
                    DatePicker::make('to')->label('إلى تاريخ')->native(false)->live(),
                ]),
            EmbeddedTable::make(),
        ]);
    }
}

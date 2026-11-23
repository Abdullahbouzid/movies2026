<?php

namespace App\Filament\Pages\Treasury;

use App\Support\Treasury;
use Filament\Forms\Components\DatePicker;
use Filament\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * Shared treasury screen for one activity.
 *
 * Each concrete page only declares its activity key, title and navigation slot;
 * the table, the date window and the totals are identical by design so the
 * cinema, games, club and event treasuries always report money the same way.
 */
abstract class ActivityTreasury extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|\UnitEnum|null $navigationGroup = 'الخزينة والتقارير';

    protected static ?int $navigationSort = 10;

    protected static string $activity = '';

    protected string $view = 'filament.pages.treasury.activity';

    /** Date window applied to the totals cards. */
    public ?string $from = null;

    public ?string $to = null;

    public function mount(): void
    {
        $this->from ??= today()->startOfMonth()->toDateString();
        $this->to ??= today()->toDateString();
    }

    public function getTitle(): string
    {
        return 'خزينة '.Treasury::label(static::$activity);
    }

    public function getSubheading(): ?string
    {
        return 'المبالغ المحصّلة والمعلّقة لهذه الخدمة خلال الفترة المحددة.';
    }

    /**
     * @return array{label: string, model: class-string, icon: string, color: string, service: string}
     */
    public function getActivityMeta(): array
    {
        return Treasury::activities()[static::$activity];
    }

    /**
     * @return array{paid_count: int, paid_total: float, pending_count: int, pending_total: float, bookings: int}
     */
    public function getFigures(): array
    {
        return Treasury::forActivity(static::$activity, $this->from, $this->to);
    }

    public function updatedFrom(): void
    {
        $this->resetTable();
    }

    public function updatedTo(): void
    {
        $this->resetTable();
    }

    public function table(Table $table): Table
    {
        $model = Treasury::model(static::$activity);

        return $table
            ->query(fn (): Builder => $model::query())
            ->columns([
                TextColumn::make('booking_ref')
                    ->label('رقم الحجز')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('full_name')
                    ->label('الاسم')
                    ->searchable(),
                TextColumn::make('branch.name')
                    ->label('الفرع')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),
                TextColumn::make('price')
                    ->label('المبلغ')
                    ->formatStateUsing(fn ($state) => \App\Support\Money::format($state))
                    ->sortable()
                    ->summarize(Sum::make()->label('الإجمالي')->formatStateUsing(fn ($state) => \App\Support\Money::format($state))),
                TextColumn::make('payment_status')
                    ->label('الدفع')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state === $model::PAYMENT_PAID ? 'مدفوع' : 'قيد الانتظار')
                    ->color(fn ($state) => $state === $model::PAYMENT_PAID ? 'success' : 'warning'),
                TextColumn::make('paid_at')
                    ->label('تاريخ التحصيل')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('تاريخ الحجز')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('paid_window')
                    ->label('تاريخ التحصيل')
                    ->form([
                        DatePicker::make('from')->label('من')->native(false),
                        DatePicker::make('to')->label('إلى')->native(false),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $d) => $q->whereDate('paid_at', '>=', $d))
                        ->when($data['to'] ?? null, fn (Builder $q, $d) => $q->whereDate('paid_at', '<=', $d))),
                SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        $model::PAYMENT_PAID => 'مدفوع',
                        $model::PAYMENT_PENDING => 'قيد الانتظار',
                    ]),
                SelectFilter::make('branch_id')
                    ->label('الفرع')
                    ->relationship('branch', 'name'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الفترة')
                ->columns(2)
                ->schema([
                    DatePicker::make('from')
                        ->label('من تاريخ')
                        ->native(false)
                        ->live(),
                    DatePicker::make('to')
                        ->label('إلى تاريخ')
                        ->native(false)
                        ->live(),
                ]),
            EmbeddedTable::make(),
        ]);
    }
}

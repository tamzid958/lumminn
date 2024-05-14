<?php

namespace App\Filament\Exports;

use App\Models\Order;
use Carbon\CarbonInterface;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('name'),
            ExportColumn::make('phone_number'),
            ExportColumn::make('address'),

            ExportColumn::make('total_amount'),
            ExportColumn::make('additional_amount'),
            ExportColumn::make('shipping_amount'),
            ExportColumn::make('discount_amount'),
            ExportColumn::make('pay_amount'),

            ExportColumn::make('paymentProvider.name'),
            ExportColumn::make('payment_id'),

            ExportColumn::make('shippingProvider.name'),
            ExportColumn::make('shipping_id'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your order export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }

    public function getJobRetryUntil(): ?CarbonInterface
    {
        return now()->addMinutes(30);
    }

    public function getFileDisk(): string
    {
        return 'public';
    }

    public function getJobBatchName(): ?string
    {
        return 'order-export';
    }
}

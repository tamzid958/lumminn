<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Enum\ShippingStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ShippingStatusCount extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'shippingStatusCount';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Shipping';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */

    protected static bool $deferLoading = true;

    protected function getOptions(): array
    {
        if (!$this->readyToLoad) {
            return [];
        }

        // Get all enum values as an array
        $enumValues = array_column(ShippingStatus::cases(), 'value');

        // Initialize the final counts array with all enum values set to zero
        $statusCounts = array_fill_keys($enumValues, 0);

        // Get counts of each status from the database
        $dbStatusCounts = Order::select('shipping_status as status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Update the status counts with actual values from the database
        foreach ($dbStatusCounts as $status => $count) {
            if (isset($statusCounts[$status])) {
                $statusCounts[$status] = $count;
            }
        }

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
            ],
            'colors' => ['#6e767d', '#5c80bc', '#d88432', '#3fa372', '#d84c4c', '#85329c'],
            'series' => array_values($statusCounts),
            'labels' => array_keys($statusCounts),
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
        ];
    }
}

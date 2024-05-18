<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Enum\PayStatus;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\DB;
use App\Models\Order;

class PayStatusCount extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'payStatusCount';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Pay Status';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Get all enum values as an array
        $enumValues = array_column(PayStatus::cases(), 'value');

        // Initialize the final counts array with all enum values set to zero
        $statusCounts = array_fill_keys($enumValues, 0);

        // Get counts of each status from the database
        $dbStatusCounts = Order::select('pay_status as status', DB::raw('count(*) as total'))
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
                'height' => 275,
            ],
            'series' => array_values($statusCounts),
            'labels' => array_keys($statusCounts),
            'colors' => ['#808080', '#008000', '#FF0000', '#0000FF'],
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
        ];
    }
}

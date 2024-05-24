<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Enum\ShippingClass;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ShippingClassRatio extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'shippingClassRatio';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Shipping Zone';

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

        $enumValues = array_column(ShippingClass::cases(), 'value');

        // Initialize the final counts array with all enum values set to zero
        $classCounts = array_fill_keys($enumValues, 0);

        // Get counts of each status from the database
        $dbClassCounts = Order::select('shipping_class as class', DB::raw('count(*) as total'))
            ->groupBy('class')
            ->pluck('total', 'class')
            ->toArray();

        // Update the status counts with actual values from the database
        foreach ($dbClassCounts as $class => $count) {
            if (isset($classCounts[$class])) {
                $classCounts[$class] = $count;
            }
        }


        return [
            'chart' => [
                'type' => 'treemap',
                'height' => 300,
            ],
            'series' => [
                [
                    'data' => array_map(function($class, $count) {
                        return ['x' => $class, 'y' => $count];
                    }, array_keys($classCounts), $classCounts),
                ],
            ],
            'colors' => [ '#FF5733'],
            'legend' => [
                'show' => true,
            ],
        ];
    }
}

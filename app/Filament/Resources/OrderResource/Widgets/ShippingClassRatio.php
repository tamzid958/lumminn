<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Enum\ShippingClass;
use App\Models\Enum\ShippingStatus;
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
    protected static ?string $heading = 'Zonal Area';

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
        $statusValues = array_column(ShippingStatus::cases(), 'value');

        // Initialize the final counts array with all enum values set to zero
        $classStatusCounts = [];
        foreach ($enumValues as $class) {
            foreach ($statusValues as $status) {
                $classStatusCounts[$class . " | " . $status] = 0;
            }
        }

        // Get counts of each status from the database
        $dbClassStatusCounts = Order::select('shipping_class as class', 'shipping_status as status', DB::raw('count(*) as total'))
            ->groupBy('class', 'status')
            ->get()
            ->toArray();

        // Update the status counts with actual values from the database
        foreach ($dbClassStatusCounts as $row) {
            $classStatus = $row['class'] . " | " . $row['status'];
            if (isset($classStatusCounts[$classStatus])) {
                $classStatusCounts[$classStatus] = $row['total'];
            }
        }
        
        $data = array_map(function($class, $count) {
            return ['x' => $class, 'y' => $count];
        }, array_keys($classStatusCounts), $classStatusCounts);

       $data = array_values(array_filter($data, fn($data) => $data['y'] > 0));

        return [
            'chart' => [
                'type' => 'treemap',
                'height' => 300,
                'toolbar' => [
                    'tools' => [
                        'download' => false
                    ]
                ],
            ],
            'series' => [
                [
                    'data' => $data,
                ],
            ],
            'colors' => [  
                '#FF5733', '#33FF57', '#3357FF', '#FF33A1', '#FF8C33', '#33FFF5',
                '#F5A623', '#50E3C2', '#B8E986', '#4A90E2', '#BD10E0', '#FF0000', 
            ],
            'legend' => [
                'show' => true,
            ],
            'plotOptions' => [
                'treemap' => [
                    'distributed' => true,
                    'enableShades' => false
                ]
            ],
        ];
    }
}

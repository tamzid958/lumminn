<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Enum\ShippingStatus;
use App\Models\Order;
use Filament\Forms\Components\Select;
use Illuminate\Support\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class TotalOrderCount extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'totalOrderCount';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Orders';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    
     protected static ?string $pollingInterval = null;

     protected static bool $deferLoading = true;

    
    protected function getFormSchema(): array
    {
        return [
            DateRangePicker::make('date-range')
            ->startDate(Carbon::now()->subDays(7))
            ->endDate(Carbon::now()->addDays(1))
            ->autoApply(), // End date: today),
        ];
    }

    protected function getOptions(): array
    {
        if (!$this->readyToLoad) {
            return [];
        }

        $dateRange = $this->filterFormData['date-range'];
        // Split the date range by the dash and trim any extra spaces
        list($startDateStr, $endDateStr) = array_map('trim', explode('-', $dateRange));

        // Convert the date strings to Unix timestamps
        $startDate = strtotime(str_replace('/', '-', $startDateStr));
        $endDate = strtotime(str_replace('/', '-', $endDateStr));

        $numDays = floor(($endDate - $startDate) / (60 * 60 * 24)) + 1; // Add 1 to include the end date
    
        // Initialize arrays to hold the total order counts and order counts per shipping status for each day
        $totalOrderCounts = array_fill(0, $numDays, 0);
        $orderCounts = [];

        // Create an array of day numbers for the x-axis
        $days = [];
        $currentDate = $startDate;
        for ($i = 0; $i < $numDays; $i++) {
            $days[] = date('Y-m-d', $currentDate);
            $currentDate += 86400; // 86400 seconds = 1 day
        }

        // Get enum values for shipping status
        $enumValues = array_column(ShippingStatus::cases(), 'value');

        // Loop through each day of the date range and gather order counts
        foreach ($days as $index => $day) {
            // Query the total order count for the current day
            $totalOrderCount = Order::whereDate('created_at', $day)->count();
            $totalOrderCounts[$index] = $totalOrderCount;

            // Query orders created on the current day, grouped by shipping status
            $dailyOrderCounts = Order::whereDate('created_at', $day)
                ->selectRaw('shipping_status, COUNT(*) as count')
                ->groupBy('shipping_status')
                ->pluck('count', 'shipping_status')
                ->toArray();

            // Store the counts in the orderCounts array
            foreach ($enumValues as $status) {
                $orderCounts[$status][$index] = $dailyOrderCounts[$status] ?? 0;
            }
        }

        // Initialize an empty array to hold the dates
        $dateArray = [];

        // Iterate through the date range
        for ($currentDate = $startDate; $currentDate <= $endDate; $currentDate = strtotime("+1 day", $currentDate)) {
            // Format the current date as "Y-m-d" and add to the array
            $dateArray[] = date("Y-m-d", $currentDate);
        }
                
        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
            ],
            'series' => [
                [
                    'tickAmount' => 'dataPoints',
                    'name' => 'Total Orders',
                    'data' => array_values($totalOrderCounts),
                ],
                ...array_map(function ($status) use ($orderCounts) {
                    return [
                        'tickAmount' => 'dataPoints',
                        'name' => $status,
                        'data' => array_values($orderCounts[$status]),
                    ];
                }, $enumValues)
            ],
            'xaxis' => [
                'type'=> 'datetime',
                'categories' => $dateArray,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Number of Orders'
                ],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#ffffff', '#6e767d', '#5c80bc', '#d88432', '#3fa372', '#d84c4c', '#85329c'],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
        ];
    }
}

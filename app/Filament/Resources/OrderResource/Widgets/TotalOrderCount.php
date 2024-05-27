<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Enum\ShippingStatus;
use App\Models\Order;
use Filament\Forms\Components\Select;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

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
        $currentMonth = date('m');
        $currentYear = date('Y');
        $previousYears = [];

        for ($i = 0; $i < 10; $i++) {
            $previousYears[] = $currentYear - $i;
        }

        $allMonths = [];
        for ($month = 1; $month <= 12; $month++) {
            $monthName = date('M', mktime(0, 0, 0, $month, 1)); // Format the month number as a month name
            $allMonths[$month] = $monthName; // Use the month name as both the key and value
        }

        return [
            Select::make('year')
                ->options($previousYears)
                ->default($currentYear),
            Select::make('month')
                ->options($allMonths)
                ->default($currentMonth),
        ];
    }

    protected function getOptions(): array
    {
        if (!$this->readyToLoad) {
            return [];
        }

        $currentYear = $this->filterFormData['year'];
        $currentMonth = $this->filterFormData['month'];

        $numDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
        $enumValues = array_column(ShippingStatus::cases(), 'value');

        $totalOrderCounts = array_fill(1, $numDaysInMonth, 0);

        // Initialize an array to hold the order counts for each shipping status and day
        $orderCounts = array_fill_keys($enumValues, array_fill(1, $numDaysInMonth, 0));

        // Loop through each day of the current month and gather order counts
        for ($day = 1; $day <= $numDaysInMonth; $day++) {
            // Format the date for the current day
            $currentDate = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
            $startDateTime = "$currentDate 00:00:00";
            $endDateTime = "$currentDate 23:59:59";

            $totalOrderCount = Order::query()
                            ->whereBetween('created_at', [$startDateTime, $endDateTime])
                            ->count();
            $totalOrderCounts[$day] = $totalOrderCount;

            // Query orders created on the current day, grouped by shipping status
            $dailyOrderCounts = Order::query()
                ->whereBetween('created_at', [$startDateTime, $endDateTime])
                ->selectRaw('shipping_status, COUNT(*) as count')
                ->groupBy('shipping_status')
                ->pluck('count', 'shipping_status')
                ->toArray();

            // Store the counts in the orderCounts array
            foreach ($dailyOrderCounts as $status => $count) {
                $orderCounts[$status][$day] = $count;
            }
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
                'type'=> 'date',
                'categories' => range(1, $numDaysInMonth),
                'labels' => [
                    'show'=> false,
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

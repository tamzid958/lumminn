<?php

namespace App\Filament\Resources\OrderResource\Widgets;

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
            $allMonths[$monthName] = $monthName; // Use the month name as both the key and value
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

        // Loop through each day of the current month
        for ($day = 1; $day <= $numDaysInMonth; $day++) {
            // Format the date for the current day
            $currentDate = date('Y-m-d', mktime(0, 0, 0, $currentMonth, $day, $currentYear));

            // Get the start and end datetime for the current day
            $startDateTime = $currentDate . ' 00:00:00';
            $endDateTime = $currentDate . ' 23:59:59';

            // Get the order count for the current day
            $orderCount = Order::query()
                ->whereBetween('created_at', [$startDateTime, $endDateTime])
                ->count();

            // Store the order count in the array with the day as the key
            $orderCounts[date('d', mktime(0, 0, 0, $currentMonth, $day, $currentYear))] = $orderCount;
        }


        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
            ],
            'series' => [
                [
                    'tickAmount' => "dataPoints",
                    'name' => 'Order Count',
                    'data' => array_values($orderCounts),
                ],
            ],
            'xaxis' => [
                'categories' => array_keys($orderCounts),
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
            'colors' => ['#059669'],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
        ];
    }
}

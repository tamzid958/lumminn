<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Forms\Components\Select;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class TotalSaleBasedOnMonth extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'totalSaleBasedOnMonth';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Total sale';

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
        $currentYear = date('Y');
        $previousYears = [];

        for ($i = 0; $i < 10; $i++) {
            $previousYears[] = $currentYear - $i;
        }

        return [
            Select::make('year')
                ->options($previousYears)
                ->default($currentYear),

        ];
    }

    protected function getOptions(): array
    {
        if (!$this->readyToLoad) {
            return [];
        }

        $currentYear = $this->filterFormData['year'];
        $orderCounts = [];

// Loop through each month of the current year
        for ($month = 1; $month <= 12; $month++) {
            // Get the number of days in the current month
            $numDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $currentYear);

            // Get the start and end date of the current month
            $startDate = date('Y-m-01', mktime(0, 0, 0, $month, 1, $currentYear));
            $endDate = date('Y-m-t', mktime(0, 0, 0, $month, $numDaysInMonth, $currentYear)); // t returns the last day of the month

            // Get the order count for the current month
            $orderCount = Order::query()

                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();

            // Store the order count in the array
            $orderCounts[date('M', mktime(0, 0, 0, $month, 1, $currentYear))] = $orderCount;
        }


        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
            ],
            'series' => [
                [
                    'tickAmount' => "dataPoints",
                    'name' => 'Sale Count',
                    'data' => array_values($orderCounts),
                ],
            ],
            'xaxis' => [

                'categories' => array_keys($orderCounts),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Number of Sales'
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

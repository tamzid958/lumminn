<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Expense;
use Filament\Forms\Components\Select;
use Illuminate\Support\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class ExpenseByMonth extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'expenseByMonth';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Expenses';

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
            ->startDate(Carbon::now()->subMonths(1))
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
        
        // Convert the date strings to the desired format directly
        $startDate = date('Y-m-d', strtotime(str_replace('/', '-', $startDateStr)));
        $endDate = date('Y-m-d', strtotime(str_replace('/', '-', $endDateStr)));
        
        $expensesByDay = Expense::query()->selectRaw('DATE(expense_date) as day, SUM(amount) as total')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->pluck('total', 'day')
            ->toArray();
        
        // Initialize all days within the range to 0
        $expensesArray = [];
        $currentDate = strtotime($startDate);
        $endDateTimestamp = strtotime($endDate);
        
        while ($currentDate <= $endDateTimestamp) {
            $dateStr = date('Y-m-d', $currentDate);
            $expensesArray[$dateStr] = $expensesByDay[$dateStr] ?? 0;
            $currentDate = strtotime('+1 day', $currentDate);
        }
        
        // Now $expensesArray contains the daily expenses with missing days set to 0

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Expense (BDT)',
                    'data' => array_values($expensesArray),
                ],
            ],
            'xaxis' => [
                'type' => 'datetime',
                'categories' => array_keys($expensesArray),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Amount (BDT)'
                ],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#e11d48'],
            'stroke' => [
                'curve' => 'smooth',
            ],
            'dataLabels' => [
                'enabled' => false,
            ],
        ];
    }
}

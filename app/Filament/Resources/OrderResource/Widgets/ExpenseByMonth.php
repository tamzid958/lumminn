<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Expense;
use Filament\Forms\Components\Select;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

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

        $expensesByMonth = Expense::query()->selectRaw('EXTRACT(MONTH FROM expense_date) as month, SUM(amount) as total')
            ->whereYear('expense_date', $this->filterFormData['year'])
            ->groupBy('month')
            ->get();

// Initialize an array to store expenses by month
        $expensesArray = [];

// Map the results to create the array with month names and totals
        foreach ($expensesByMonth as $expense) {
            $monthName = date('M', mktime(0, 0, 0, $expense->month, 1));
            $expensesArray[$monthName] = $expense->total;
        }

// If any month has no expense, set its value to 0
        $allMonths = array_map(function ($month) {
            return date('M', mktime(0, 0, 0, $month, 1));
        }, range(1, 12));

        $expensesArray = array_merge(array_fill_keys($allMonths, 0), $expensesArray);

// Sort array by month name

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

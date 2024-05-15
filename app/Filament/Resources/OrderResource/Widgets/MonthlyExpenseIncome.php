<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Expense;
use App\Models\Order;
use Filament\Forms\Components\Select;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class MonthlyExpenseIncome extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'monthlyExpenseIncome';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Income Expense Ratio';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
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

        $incomesArray = $this->getIncomes();

        $expensesArray = $this->getExpenses();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
                'stacked' => true
            ],
            'series' => [
                [
                    'name' => 'Expense',
                    'data' => array_map(function ($value) {
                        return -$value;
                    }, array_values($expensesArray)),
                ],
                [
                    'name' => 'Income',
                    'data' => array_values($incomesArray),
                ],
            ],
            'xaxis' => [
                'title' => [
                    'text' => 'Amount (BDT)'
                ],
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],

                ],
            ],
            'colors' => ['#008FFB', '#FF4560'],
            'plotOptions' => [
                'bar' => [
                    'borderRadiusApplication' => 'end',
                    'borderRadiusWhenStacked' => 'all',
                    'borderRadius' => 3,
                    'horizontal' => true,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getIncomes(): array
    {
        $currentYear = $this->filterFormData['year'];
        $orderRevenues = [];

// Loop through each month of the current year
        for ($month = 1; $month <= 12; $month++) {
            // Get the number of days in the current month
            $numDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $currentYear);

            // Get the start and end date of the current day
            $startDate = date('Y-m-d', mktime(0, 0, 0, $month, 1, $currentYear));
            $endDate = date('Y-m-d', mktime(23, 59, 59, $month, $numDaysInMonth, $currentYear));

            // Get the order revenue for the current day
            $orderRevenue = Order::query()
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->selectRaw('SUM(orders.total_amount + orders.additional_amount - orders.discount_amount) AS total_revenue')
                ->selectRaw('SUM(production_cost) AS total_production_cost')
                ->selectRaw('(SUM(orders.total_amount + orders.additional_amount - orders.discount_amount) - SUM(production_cost)) AS net_revenue')
                ->where('orders.pay_status', '=', 'Paid')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->first();
            // Accumulate the revenue for the month

            // Store the monthly revenue in the array
            $monthName = date('M', mktime(0, 0, 0, $month, 1));
            $orderRevenues[$monthName] = $orderRevenue['net_revenue'];
        }

        return $orderRevenues;
    }

    /**
     * @return array
     */
    private function getExpenses(): array
    {
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
        return $expensesArray;
    }
}

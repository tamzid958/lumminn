<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Expense;
use App\Models\Order;
use Filament\Forms\Components\Select;
use Illuminate\Support\Carbon;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

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
    protected static ?string $pollingInterval = null;

    protected static bool $deferLoading = true;

    protected function getFormSchema(): array
    {
        return [
            DateRangePicker::make('date-range')
            ->startDate(Carbon::now()->subDays(15))
            ->endDate(Carbon::now()->addDays(1))
            ->autoApply(), // End date: today),
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
                'type' => 'datetime',
                'categories' => $this->generateDateRange(),
                'labels' => [
                    'rotate'=> -90,
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
            'colors' => ['#FF4560', '#008FFB'],
            'plotOptions' => [
                'bar' => [
                    'borderRadiusApplication' => 'end',
                    'borderRadiusWhenStacked' => 'all',
                    'borderRadius' => 3,
                    'horizontal' => false,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function getIncomes(): array
    {
    $dateRange = $this->filterFormData['date-range'];

    // Split the date range by the dash and trim any extra spaces
    list($startDateStr, $endDateStr) = array_map('trim', explode('-', $dateRange));
    
    // Convert the date strings to the desired format directly
    $startDate = date('Y-m-d', strtotime(str_replace('/', '-', $startDateStr)));
    $endDate = date('Y-m-d', strtotime(str_replace('/', '-', $endDateStr)));

    $orderRevenues = [];
    
    // Initialize currentDate to startDate and convert endDate to timestamp
    $currentDate = strtotime($startDate);
    $endDateTimestamp = strtotime($endDate);

    // Loop through each day within the specified date range
    while ($currentDate <= $endDateTimestamp) {
            // Get the start and end time for the current day
            $startDateTime = date('Y-m-d 00:00:00', $currentDate);
            $endDateTime = date('Y-m-d 23:59:59', $currentDate);

            // Get the order revenue for the current day
            $orderRevenue = Order::query()
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->selectRaw('SUM(orders.total_amount + orders.additional_amount - orders.discount_amount) AS total_revenue')
                ->selectRaw('SUM(production_cost) AS total_production_cost')
                ->selectRaw('(SUM(orders.total_amount + orders.additional_amount - orders.discount_amount) - SUM(production_cost)) AS net_revenue')
                ->where('orders.pay_status', '=', 'Paid')
                ->whereBetween('orders.created_at', [$startDateTime, $endDateTime])
                ->first();
            
            // Store the daily revenue in the array
            $dateStr = date('Y-m-d', $currentDate);
            $orderRevenues[$dateStr] = $orderRevenue->net_revenue ?? 0;

            // Move to the next day
            $currentDate = strtotime('+1 day', $currentDate);
        }

        return $orderRevenues;
    }


    /**
     * @return array
     */
    private function getExpenses(): array
    {
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
    
        // Initialize an array to store expenses by day
        $expensesArray = [];
        
        // Initialize currentDate to startDate and convert endDate to timestamp
        $currentDate = strtotime($startDate);
        $endDateTimestamp = strtotime($endDate);
    
        // Loop through each day within the specified date range
        while ($currentDate <= $endDateTimestamp) {
            $dateStr = date('Y-m-d', $currentDate);
            $expensesArray[$dateStr] = $expensesByDay[$dateStr] ?? 0;
            $currentDate = strtotime('+1 day', $currentDate);
        }
    
        return $expensesArray;
    }    

    function generateDateRange()
    {
        $dateRange = $this->filterFormData['date-range'];
    
        // Split the date range by the dash and trim any extra spaces
        list($startDateStr, $endDateStr) = array_map('trim', explode('-', $dateRange));
        
        // Convert the date strings to the desired format directly
        $startDate = date('Y-m-d', strtotime(str_replace('/', '-', $startDateStr)));
        $endDate = date('Y-m-d', strtotime(str_replace('/', '-', $endDateStr)));

        $dates = [];
        $currentDate = strtotime($startDate);
        $endDateTimestamp = strtotime($endDate);

        while ($currentDate <= $endDateTimestamp) {
            $dates[] = date('Y-m-d', $currentDate);
            $currentDate = strtotime('+1 day', $currentDate);
        }

        return $dates;
    }

}

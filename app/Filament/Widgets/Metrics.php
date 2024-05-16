<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Expense;
use App\Utils\NumberUtil;
use App\Models\Investment;
use App\Models\Order;

class Metrics extends BaseWidget
{
    protected function getStats(): array
    {
        $expenses = Expense::selectRaw('EXTRACT(YEAR FROM expense_date) as year, EXTRACT(MONTH FROM expense_date) as month, SUM(amount) as total')
        ->groupBy('year', 'month')
        ->get();

        $expenseArray = $expenses->pluck('total')->toArray();
        $totalExpense = array_sum($expenseArray);

        $investments = Investment::selectRaw('EXTRACT(YEAR FROM investment_date) as year, EXTRACT(MONTH FROM investment_date) as month, SUM(amount) as total')
        ->groupBy('year', 'month')
        ->get();

        $invesmentArray = $investments->pluck('total')->toArray();
        $totalInvestment = array_sum($invesmentArray);

        $revenues = Order::selectRaw('EXTRACT(YEAR FROM created_at) as year, EXTRACT(MONTH FROM created_at) as month, SUM(total_amount + additional_amount - discount_amount) as total')
        ->where('pay_status', '=', 'Paid')
        ->groupBy('year', 'month')
        ->get();

        $revenueArray = $revenues->pluck('total')->toArray();
        $totalRevenue = array_sum($revenueArray);

        return [
            Stat::make('Total Investment', NumberUtil::number_shorten($totalInvestment))
                ->chart($invesmentArray)
                ->description('Increment of Investment')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->extraAttributes(['title' => '৳'. $totalInvestment])
                ->color('info'),

            Stat::make('Total Expense', NumberUtil::number_shorten($totalExpense))
                ->chart($expenseArray)
                ->description('Increment of Expense')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->extraAttributes(['title' => '৳'. $totalExpense])
                ->color('danger'),

            Stat::make('Total Sell', NumberUtil::number_shorten($totalRevenue))
                ->chart($revenueArray)
                ->description('Increment of Sell')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->extraAttributes(['title' => '৳'. $totalRevenue])
                ->color('success'),
        ];
    }
}

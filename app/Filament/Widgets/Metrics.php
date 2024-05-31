<?php

namespace App\Filament\Widgets;

use App\Models\Expense;
use App\Models\Investment;
use App\Models\Order;
use App\Utils\NumberUtil;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

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

        $orderRevenue = Order::query()
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->selectRaw('EXTRACT(YEAR FROM orders.created_at) as year, EXTRACT(MONTH FROM orders.created_at) as month')
                ->selectRaw('SUM(orders.total_amount + orders.additional_amount - orders.discount_amount) AS total_revenue')
                ->selectRaw('SUM(production_cost) AS total_production_cost')
                ->selectRaw('(SUM(orders.total_amount + orders.additional_amount - orders.discount_amount) - SUM(production_cost)) AS net_revenue')
                ->where('orders.pay_status', '=', 'Paid')
                ->groupBy('year', 'month')
                ->first();

        $totalSaleArray = $orderRevenue->pluck('total_revenue')->toArray();
        $totalSale = array_sum($totalSaleArray);

        $totalGrossProfitArray = $orderRevenue->pluck('net_revenue')->toArray();
        $totalGrossProfit = array_sum($totalGrossProfitArray);

        return [
            Stat::make('Total Investment', NumberUtil::number_shorten($totalInvestment))
                ->chart($invesmentArray)
                ->description('Increment of Investment')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->extraAttributes(['title' => '৳' . $totalInvestment])
                ->color('info'),

            Stat::make('Total Expense', NumberUtil::number_shorten($totalExpense))
                ->chart($expenseArray)
                ->description('Increment of Expense')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->extraAttributes(['title' => '৳' . $totalExpense])
                ->color('danger'),

            Stat::make('Total Sale', NumberUtil::number_shorten($totalSale))
                ->chart($totalSaleArray)
                ->description('Increment of Sale')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->extraAttributes(['title' => '৳' . $totalSale])
                ->color('success'),

            Stat::make('Gross Profit', NumberUtil::number_shorten($totalGrossProfit))
                ->chart($totalGrossProfitArray)
                ->description('Increment of Gross Profit')
                ->descriptionIcon('heroicon-o-arrow-trending-up')
                ->extraAttributes(['title' => '৳' . $totalGrossProfit])
                ->color('success'),
        ];
    }
}

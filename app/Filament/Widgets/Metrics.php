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
                    ->selectRaw("
                        SUM(orders.total_amount + orders.additional_amount - orders.discount_amount) AS total_revenue,
                        SUM(order_items.production_cost) AS total_production_cost,
                        SUM(
                            (orders.total_amount + orders.additional_amount - orders.discount_amount)
                            - order_items.production_cost
                            - CASE WHEN orders.shipping_status IN ('Cancelled', 'Returned') THEN orders.shipping_amount ELSE 0 END
                        ) AS net_revenue
                    ")
                    ->where('orders.pay_status', '=', 'Paid')
                    ->groupBy('orders.id')
                    ->get();

        $totalSale = $orderRevenue->pluck('total_revenue')->sum();
        $grossProfit = $orderRevenue->pluck('net_revenue')->sum();

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
                ->extraAttributes(['title' => '৳' . $totalSale])
                ->color('success'),

            Stat::make('Gross Profit', NumberUtil::number_shorten($grossProfit))
                ->extraAttributes(['title' => '৳' . ($grossProfit)])
                ->color('success'),
        ];
    }
}

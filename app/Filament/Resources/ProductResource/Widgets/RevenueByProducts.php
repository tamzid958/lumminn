<?php

namespace App\Filament\Resources\ProductResource\Widgets;

use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class RevenueByProducts extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'revenueByProducts';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Revenue by products';

    protected function getOptions(): array
    {
        if (!$this->readyToLoad) {
            return [];
        }

        $products = DB::table('products')
            ->selectRaw('slug, sale_price, production_cost, (sale_price - production_cost) AS revenue')
            ->orderBy('revenue', 'desc')
            ->limit(5)
            ->get()
            ->toArray();


        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Production Cost',
                    'data' => $this->getSeries($products, "production_cost"),
                ],
                [
                    'name' => 'Revenue',
                    'data' => $this->getSeries($products, "revenue"),
                ],
                [
                    'name' => 'Sale Price',
                    'data' => $this->getSeries($products, "sale_price"),
                ],
            ],
            'xaxis' => [
                'categories' => $this->getSeries($products, "slug"),
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'title' => [
                    'text' => 'Price (BDT)'
                ],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],

                ],
            ],
            'colors' => ['#0891b2', '#e11d48', '#059669'],
            'stroke' => [
                'show' => true,
                'width' => 2,
                'colors' => ['transparent']
            ],
            'plotOptions' => [
                'bar' => [
                    'horizontal' => false,
                    'columnWidth' => '55%',
                    'endingShape' => 'rounded',
                    'borderRadiusApplication' => 'end',
                    'borderRadiusWhenStacked' => 'all',
                    'borderRadius' => 3,
                ]
            ]
        ];
    }

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */

    private function getSeries($items, $key): array
    {
        return array_map(function ($item) use ($key) {
            return $item->$key;
        }, $items);
    }
}

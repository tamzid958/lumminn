<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\PaymentProvider;
use App\Models\ShippingProvider;
use Exception;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;

class GenerateInvoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $orderIds;
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(array $orderIds, $user)
    {
        $this->orderIds = $orderIds;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $orders = Order::query()->whereIn('id', $this->orderIds)->get()->toArray();

        try {
            $packingReceipts = collect($orders)->map(function ($record) {
                return [
                    'id' => $record['id'],
                    'name' => $record['name'],
                    'phone_number' => $record['phone_number'],
                    'address' => $record['address'],
                    'shipping_id' => $record['shipping_id'],
                    'shipping_provider_name' => ShippingProvider::query()->find($record['shipping_provider_id'])->name,
                    'due_amount' => PaymentProvider::query()->find($record['payment_provider_id'])->slug === 'cash-on-delivery' ? $record['pay_amount'] : 0,
                ];
            });

            $filename = time() . "-invoice.pdf";

            LaravelMpdf::loadView('components.download-invoice',
                ['packingReceipts' => $packingReceipts])
                ->save(public_path('storage') . '/' . $filename);

            Invoice::query()->create([
                "name" => $filename,
                "file" => $filename
            ]);

            Notification::make()
                ->title('Invoice generated')
                ->icon('heroicon-o-document-text')
                ->body("Download " . $filename . " and print it for packaging")
                ->actions([
                    Action::make('Download')
                        ->button()
                        ->url(asset('storage/' . $filename), shouldOpenInNewTab: true)
                ])
                ->sendToDatabase($this->user);
        } catch (Exception $e) {
            Notification::make()
                ->title('Invoice generation failed')
                ->icon('heroicon-o-document-text')
                ->body("Check failed jobs table to see error logs")
                ->sendToDatabase($this->user);
        }
    }
}

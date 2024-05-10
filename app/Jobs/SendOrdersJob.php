<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\ShippingProvider;
use App\Providers\ShippingServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use App\Models\PaymentProvider;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\Enums\Format;

class SendOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $orders;
    protected $user;

    /**
     * Create a new job instance.
     */
    public function __construct(array $orders, $user)
    {
        $this->orders = $orders;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->orders as $order) {
            $dbOrder = Order::query()->find($order['id'])->toArray();
            $shipping_provider = ShippingProvider::query()->find($order['shipping_provider_id']);

            ShippingServiceProvider::register($shipping_provider)->create()->send($dbOrder);
        }

        $pdfName = time() . "-invoice.pdf";

        $packingReceipts =  collect($this->orders)->map(function ($record) {
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

        Pdf::view('components.download-invoice', ['packingReceipts' => $packingReceipts])
            ->format(Format::A4)
            ->disk('public')
            ->save($pdfName);

        Notification::make()
            ->title('Invoice generated')
            ->icon('heroicon-o-document-text')
            ->body("Download invoice and print it for packaging")
            ->actions([
                Action::make('Download')
                    ->button()
                    ->url(asset('storage/' . $pdfName), shouldOpenInNewTab: true)
            ])
            ->sendToDatabase($this->user);

        //
    }
}

<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\PaymentProvider;
use App\Models\ShippingProvider;
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
       
        $packingReceipts = collect($this->orders)->map(function ($record) {
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

        $filename =  time() . "-invoice.pdf";
        
        LaravelMpdf::loadView('components.download-invoice', 
        ['packingReceipts' => $packingReceipts])
        ->save(public_path('storage') .'/'. $filename);

        Invoice::query()->create([
            "name" => array_first($packingReceipts->toArray())['id'] . "-" . array_last($packingReceipts->toArray())['id'],
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
    }
}

<?php

namespace App\Jobs;

use App\Domain\Payments\Actions\HandleWebhookAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPaymentWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $provider,
        public array $payload,
        public array $headers = []
    ) {
    }

    public function handle(HandleWebhookAction $action): void
    {
        $request = Request::create('/', 'POST', $this->payload);
        if ($this->headers) {
            $request->headers->add($this->headers);
        }

        $action->execute($this->provider, $request);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentGatewayRequest;
use App\Models\PaymentGatewaySetting;
use Illuminate\Http\RedirectResponse;

class PaymentSettingsController extends Controller
{
    public function update(UpdatePaymentGatewayRequest $request, string $provider): RedirectResponse
    {
        $provider = strtolower($provider);
        $supported = array_keys(config('payments.providers', []));
        if (! in_array('applepay', $supported, true)) {
            $supported[] = 'applepay';
        }

        if (! in_array($provider, $supported, true)) {
            abort(404);
        }

        $credentials = array_filter(
            $request->input('credentials', []),
            fn ($value) => $value !== null && $value !== ''
        );

        PaymentGatewaySetting::updateOrCreate(
            ['provider' => $provider],
            [
                'display_name' => $request->input('display_name'),
                'enabled' => $request->boolean('enabled'),
                'sandbox' => $request->boolean('sandbox'),
                'sort_order' => (int) $request->input('sort_order', 0),
                'credentials' => $credentials ?: null,
                'webhook_secret' => $request->input('webhook_secret'),
            ]
        );

        return back()->with('status', __('Payment settings saved.'));
    }
}

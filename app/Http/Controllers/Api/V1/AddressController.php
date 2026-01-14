<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\AddressStoreRequest;
use App\Http\Requests\AddressUpdateRequest;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends ApiController
{
    public function index(Request $request)
    {
        $addresses = $request->user()
            ->addresses()
            ->orderByDesc('is_default')
            ->get();

        return $this->success($addresses);
    }

    public function store(AddressStoreRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;

        if (!empty($data['is_default'])) {
            Address::query()
                ->where('user_id', $request->user()->id)
                ->update(['is_default' => false]);
        }

        $address = Address::create($data);

        return $this->success($address, 'Address created.', 201);
    }

    public function update(AddressUpdateRequest $request, Address $address)
    {
        $this->authorize('update', $address);

        $data = $request->validated();

        if (array_key_exists('is_default', $data) && $data['is_default']) {
            Address::query()
                ->where('user_id', $request->user()->id)
                ->update(['is_default' => false]);
        }

        $address->update($data);

        return $this->success($address, 'Address updated.');
    }

    public function destroy(Request $request, Address $address)
    {
        $this->authorize('delete', $address);

        $address->delete();

        return $this->success(null, 'Address removed.');
    }
}

<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethodStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrackPaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_method' => ['required', Rule::enum(PaymentMethodStatus::class)],
        ];
    }

    public function paymentMethod(): PaymentMethodStatus
    {
        return $this->enum('payment_method', PaymentMethodStatus::class);
    }
}

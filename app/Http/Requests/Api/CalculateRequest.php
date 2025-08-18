<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\ValidateMessagesType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class CalculateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // ! true - пользователь может быть не авторизован, после разработки вернуть в false
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from' => 'required|integer',
            'to' => 'required|integer',
            'places' => 'required|array',
            'places.*.weight' => 'required|decimal:0,1',
            'places.*.length' => 'required|integer',
            'places.*.width' => 'required|integer',
            'places.*.height' => 'required|integer',
            'companies' => 'required|array',
            'delivery_type' => 'array|nullable',
            'shipment_date' => 'date',
            'insurance' => 'integer',
            'cash_on_delivery' => 'lte:insurance|integer',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'data' => $validator->errors()
        ]));
    }

    public function  messages()
    {
        return [
            'from.required' => ValidateMessagesType::Required->value,
            'to.required' => ValidateMessagesType::Required->value,
            'companies.required' => ValidateMessagesType::Required->value,
            'places.required' => ValidateMessagesType::Required->value,
            'places.*.weight.required' => ValidateMessagesType::Required->value,
            'places.*.length.required' => ValidateMessagesType::Required->value,
            'places.*.width.required' => ValidateMessagesType::Required->value,
            'places.*.height.required' => ValidateMessagesType::Required->value,
            'shipment_date.required' => ValidateMessagesType::Required->value,

            'companies.array' => ValidateMessagesType::ToBeArray->value,
            'places.array' => ValidateMessagesType::ToBeArray->value,
            'delivery_type.array' => ValidateMessagesType::ToBeArray->value,

            'shipment_date.date' => ValidateMessagesType::ToBeDate->value,

            'from.integer' => ValidateMessagesType::ToBeInteger->value,
            'to.integer' => ValidateMessagesType::ToBeInteger->value,
            'places.*.length.integer' => ValidateMessagesType::ToBeInteger->value,
            'places.*.width.integer' => ValidateMessagesType::ToBeInteger->value,
            'places.*.height.integer' => ValidateMessagesType::ToBeInteger->value,
            'insurance.integer' => ValidateMessagesType::ToBeInteger->value,
            'cash_on_delivery.integer' => ValidateMessagesType::ToBeInteger->value,

            'places.*.weight.decimal' => ValidateMessagesType::ToBeDecimal->value,

            'cash_on_delivery.lte' => ValidateMessagesType::ToBeNoMore->value,
        ];
    }
}

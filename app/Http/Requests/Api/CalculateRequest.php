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
        return true; // ! true - полльзователь может быть не авторизован
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
            'companies' => 'required|array',
            'places' => 'required|array',
            'places.*.weight' => 'required|decimal:0,1',
            'places.*.length' => 'required|integer',
            'places.*.width' => 'required|integer',
            'places.*.height' => 'required|integer',
            'regimes' => 'array',
            'shipment_date' => 'required|date',
            'sumoc' => 'integer',
            'sumnp' => 'lte:sumoc|integer',
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
            // обязательно
            'from.required' => ValidateMessagesType::Required->value,
            'to.required' => ValidateMessagesType::Required->value,
            'companies.required' => ValidateMessagesType::Required->value,
            'places.required' => ValidateMessagesType::Required->value,
            'places.*.weight.required' => ValidateMessagesType::Required->value,
            'places.*.length.required' => ValidateMessagesType::Required->value,
            'places.*.width.required' => ValidateMessagesType::Required->value,
            'places.*.height.required' => ValidateMessagesType::Required->value,
            'shipment_date.required' => ValidateMessagesType::Required->value,
            // массивом
            'companies.array' => ValidateMessagesType::ToBeArray->value,
            'places.array' => ValidateMessagesType::ToBeArray->value,
            'regimes.array' => ValidateMessagesType::ToBeArray->value,
            // датой
            'shipment_date.date' => ValidateMessagesType::ToBeDate->value,
            // числом
            'from.integer' => ValidateMessagesType::ToBeInteger->value,
            'to.integer' => ValidateMessagesType::ToBeInteger->value,
            'places.*.length.integer' => ValidateMessagesType::ToBeInteger->value,
            'places.*.width.integer' => ValidateMessagesType::ToBeInteger->value,
            'places.*.height.integer' => ValidateMessagesType::ToBeInteger->value,
            'sumoc.integer' => ValidateMessagesType::ToBeInteger->value,
            'sumnp.integer' => ValidateMessagesType::ToBeInteger->value,
            // с точкой или без
            'places.*.weight.decimal' => ValidateMessagesType::ToBeDecimal->value,
            // не более sumoc
            'sumnp.lte' => ValidateMessagesType::ToBeNoMore->value,
        ];
    }
}

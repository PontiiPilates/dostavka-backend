<?php

namespace App\Http\Requests\Api;

use App\Enums\ValidateMessagesType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class AutocompleteRequest extends FormRequest
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
        return ['name' => 'required|alpha_dash|min:2|max:200'];
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
            // обязательно для заполнения
            'name.required' => ValidateMessagesType::Required->value,
            'name.alpha_dash' => ValidateMessagesType::InvalidCharacters->value,
            'name.min' => ValidateMessagesType::ToBeMore->value,
            'name.max' => ValidateMessagesType::ToBeLess->value,
        ];
    }
}

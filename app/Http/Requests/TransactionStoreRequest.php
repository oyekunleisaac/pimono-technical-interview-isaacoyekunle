<?php


namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;


class TransactionStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }


    public function rules()
    {
        return [
        'receiver_id' => ['required', 'integer', 'exists:users,id', 'not_in:' . auth()->id()],
        'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RebanhoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'especie' => [
                'required',
                'string',
                Rule::in(['Bovino', 'Suíno', 'Ovino', 'Caprino', 'Aves', 'Equino', 'Bubalino', 'Outros']),
            ],
            'quantidade' => ['required', 'integer', 'min:1'],
            'finalidade' => ['nullable', 'string', 'max:500'],
            'data_atualizacao' => ['required', 'date'],
            'propriedade_id' => ['required', 'exists:propriedades,id'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'especie' => 'espécie',
            'quantidade' => 'quantidade',
            'finalidade' => 'finalidade',
            'data_atualizacao' => 'data de atualização',
            'propriedade_id' => 'propriedade',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'especie.in' => 'A espécie deve ser: Bovino, Suíno, Ovino, Caprino, Aves, Equino, Bubalino ou Outros.',
            'quantidade.min' => 'A quantidade deve ser no mínimo 1 animal.',
            'propriedade_id.exists' => 'A propriedade selecionada não existe.',
        ];
    }
}

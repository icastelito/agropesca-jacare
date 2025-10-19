<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropriedadeRequest extends FormRequest
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
            'nome' => ['required', 'string', 'max:255'],
            'municipio' => ['required', 'string', 'max:255'],
            'uf' => ['required', 'string', 'size:2', 'uppercase'],
            'inscricao_estadual' => ['nullable', 'string', 'max:50'],
            'area_total' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'produtor_id' => ['required', 'exists:produtores_rurais,id'],
            'data_cadastro' => ['nullable', 'date'],
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
            'nome' => 'nome da propriedade',
            'municipio' => 'município',
            'uf' => 'UF',
            'inscricao_estadual' => 'inscrição estadual',
            'area_total' => 'área total',
            'produtor_id' => 'produtor rural',
            'data_cadastro' => 'data de cadastro',
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
            'uf.size' => 'A UF deve conter exatamente 2 caracteres.',
            'uf.uppercase' => 'A UF deve estar em letras maiúsculas.',
            'produtor_id.exists' => 'O produtor rural selecionado não existe.',
            'area_total.min' => 'A área total deve ser maior ou igual a zero.',
        ];
    }
}

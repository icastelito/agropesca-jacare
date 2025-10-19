<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProdutorRuralRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Permitir para todos (adicionar autenticação depois se necessário)
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Buscar ID do produtor da rota (agora usando 'produtorRural' em vez de 'produtor_rural')
        $produtorId = $this->route('produtorRural') ? $this->route('produtorRural')->id : null;

        return [
            'nome' => ['required', 'string', 'max:255'],
            'cpf_cnpj' => [
                'required',
                'string',
                'max:18',
                Rule::unique('produtores_rurais', 'cpf_cnpj')->ignore($produtorId),
            ],
            'telefone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'endereco' => ['required', 'string'],
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
            'nome' => 'nome',
            'cpf_cnpj' => 'CPF/CNPJ',
            'telefone' => 'telefone',
            'email' => 'e-mail',
            'endereco' => 'endereço',
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
            'cpf_cnpj.unique' => 'Este CPF/CNPJ já está cadastrado no sistema.',
            'email.email' => 'O e-mail deve ser um endereço válido.',
        ];
    }
}

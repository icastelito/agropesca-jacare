<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DocumentoRequest extends FormRequest
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
            'arquivo' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120', // 5MB em kilobytes
            ],
            'categoria' => [
                'nullable',
                'string',
                'in:CPF,CNPJ,RG,Escritura,Foto,Outros',
            ],
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
            'arquivo.required' => 'O arquivo é obrigatório.',
            'arquivo.file' => 'O arquivo enviado não é válido.',
            'arquivo.mimes' => 'O arquivo deve ser do tipo: PDF, JPG, JPEG ou PNG.',
            'arquivo.max' => 'O arquivo não pode ser maior que 5MB.',
            'categoria.in' => 'A categoria deve ser uma das opções: CPF, CNPJ, RG, Escritura, Foto ou Outros.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'arquivo' => 'arquivo',
            'categoria' => 'categoria',
        ];
    }
}

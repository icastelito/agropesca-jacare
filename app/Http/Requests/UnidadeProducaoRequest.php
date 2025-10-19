<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnidadeProducaoRequest extends FormRequest
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
            'nome_cultura' => [
                'required',
                'string',
                Rule::in([
                    'Laranja Pera',
                    'Melancia Crimson Sweet',
                    'Goiaba Paluma',
                    'Manga',
                    'Banana',
                    'Coco',
                    'Abacaxi',
                    'Maracujá',
                    'Limão',
                    'Acerola',
                    'Caju',
                    'Mamão',
                    'Milho',
                    'Feijão',
                    'Mandioca',
                    'Batata Doce',
                    'Cana-de-açúcar',
                    'Arroz',
                    'Hortaliças',
                    'Outras Culturas',
                ]),
            ],
            'area_total_ha' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'coordenadas_geograficas' => ['nullable', 'string', 'max:255'],
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
            'nome_cultura' => 'cultura',
            'area_total_ha' => 'área total em hectares',
            'coordenadas_geograficas' => 'coordenadas geográficas',
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
            'nome_cultura.in' => 'Selecione uma cultura válida da lista disponível.',
            'propriedade_id.exists' => 'A propriedade selecionada não existe.',
            'area_total_ha.min' => 'A área total deve ser maior ou igual a zero.',
        ];
    }
}

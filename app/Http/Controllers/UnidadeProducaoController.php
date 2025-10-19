<?php

namespace App\Http\Controllers;

use App\Models\UnidadeProducao;
use App\Models\Propriedade;
use App\Http\Requests\UnidadeProducaoRequest;
use App\Http\Resources\UnidadeProducaoResource;
use App\Helpers\SearchHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UnidadeProducaoController extends Controller
{
    /**
     * Display a listing of the resource.
     * Com paginação e filtros avançados
     */
    public function index(Request $request)
    {
        $query = UnidadeProducao::query();

        // Filtro por nome da cultura (busca flexível: case-insensitive, accent-insensitive, partial match)
        if ($request->filled('nome_cultura')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->nome_cultura);
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $normalizedTerm = SearchHelper::normalize($term);
                    $q->whereRaw('unaccent(LOWER(nome_cultura)) LIKE unaccent(?)', ['%' . $normalizedTerm . '%']);
                }
            });
        }

        // Filtros por área (área mínima e máxima)
        if ($request->filled('area_total_ha_min')) {
            $query->where('area_total_ha', '>=', $request->area_total_ha_min);
        }

        if ($request->filled('area_total_ha_max')) {
            $query->where('area_total_ha', '<=', $request->area_total_ha_max);
        }

        // Filtro por coordenadas geográficas (busca parcial)
        if ($request->filled('coordenadas_geograficas')) {
            $query->where('coordenadas_geograficas', 'like', '%' . $request->coordenadas_geograficas . '%');
        }

        // Filtro por ID da propriedade (busca exata por UUID)
        if ($request->filled('propriedade_id')) {
            $query->where('propriedade_id', $request->propriedade_id);
        }

        // Filtro por nome da propriedade (busca flexível via join)
        if ($request->filled('propriedade_nome')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->propriedade_nome);
            $query->whereHas('propriedade', function ($q) use ($searchTerms) {
                $q->where(function ($subQ) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $normalizedTerm = SearchHelper::normalize($term);
                        $subQ->whereRaw('unaccent(LOWER(nome)) LIKE unaccent(?)', ['%' . $normalizedTerm . '%']);
                    }
                });
            });
        }

        // Filtro por ID do produtor (busca exata por UUID via join duplo)
        if ($request->filled('produtor_id')) {
            $query->whereHas('propriedade', function ($q) use ($request) {
                $q->where('produtor_id', $request->produtor_id);
            });
        }

        // Filtro por nome do produtor (busca flexível via join duplo)
        if ($request->filled('produtor_nome')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->produtor_nome);
            $query->whereHas('propriedade.produtorRural', function ($q) use ($searchTerms) {
                $q->where(function ($subQ) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $normalizedTerm = SearchHelper::normalize($term);
                        $subQ->whereRaw('unaccent(LOWER(nome)) LIKE unaccent(?)', ['%' . $normalizedTerm . '%']);
                    }
                });
            });
        }

        // Filtro por município da propriedade (busca flexível via join)
        if ($request->filled('municipio')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->municipio);
            $query->whereHas('propriedade', function ($q) use ($searchTerms) {
                $q->where(function ($subQ) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $normalizedTerm = SearchHelper::normalize($term);
                        $subQ->whereRaw('unaccent(LOWER(municipio)) LIKE unaccent(?)', ['%' . $normalizedTerm . '%']);
                    }
                });
            });
        }

        // Filtro por UF da propriedade (busca exata via join)
        if ($request->filled('uf')) {
            $query->whereHas('propriedade', function ($q) use ($request) {
                $q->where('uf', strtoupper($request->uf));
            });
        }

        // Filtro por período de data de criação
        if ($request->filled('created_at_inicio')) {
            $query->whereDate('created_at', '>=', $request->created_at_inicio);
        }

        if ($request->filled('created_at_fim')) {
            $query->whereDate('created_at', '<=', $request->created_at_fim);
        }

        // Ordenação
        $sortFieldMapping = [
            'propriedade.nome' => 'propriedade_nome',
            'propriedade.municipio' => 'propriedade_municipio',
            'propriedade.uf' => 'propriedade_uf',
            'propriedade.produtor_rural.nome' => 'produtor_nome',
        ];

        $requestedSortField = $request->get('sort_field', 'created_at');

        // Mapear campo do PrimeVue para nome aceito pelo backend
        $sortField = $sortFieldMapping[$requestedSortField] ?? $requestedSortField;

        $sortDirection = $request->get('sort_direction', 'desc');

        // Validar campo de ordenação para evitar SQL injection
        $allowedSortFields = ['id', 'nome_cultura', 'area_total_ha', 'created_at', 'propriedade_nome', 'propriedade_municipio', 'propriedade_uf', 'produtor_nome'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        // Aplicar ordenação (mapear campos relacionados para joins/colunas reais)
        if ($sortField === 'propriedade_nome') {
            $query->leftJoin('propriedades', 'unidades_producao.propriedade_id', '=', 'propriedades.id')
                ->orderBy('propriedades.nome', $sortDirection)
                ->select('unidades_producao.*');
        } elseif ($sortField === 'propriedade_municipio') {
            $query->leftJoin('propriedades', 'unidades_producao.propriedade_id', '=', 'propriedades.id')
                ->orderBy('propriedades.municipio', $sortDirection)
                ->select('unidades_producao.*');
        } elseif ($sortField === 'propriedade_uf') {
            $query->leftJoin('propriedades', 'unidades_producao.propriedade_id', '=', 'propriedades.id')
                ->orderBy('propriedades.uf', $sortDirection)
                ->select('unidades_producao.*');
        } elseif ($sortField === 'produtor_nome') {
            $query->leftJoin('propriedades', 'unidades_producao.propriedade_id', '=', 'propriedades.id')
                ->leftJoin('produtor_rurais', 'propriedades.produtor_id', '=', 'produtor_rurais.id')
                ->orderBy('produtor_rurais.nome', $sortDirection)
                ->select('unidades_producao.*');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        // Quantidade por página (validar para evitar valores inválidos)
        $perPage = $request->get('per_page', 15);
        $allowedPerPage = [15, 25, 50, 100];

        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }

        // Paginação com relacionamentos (usar eager loading para evitar N+1)
        $unidades = $query
            ->with('propriedade.produtorRural')
            ->paginate($perPage)
            ->appends($request->query());

        return Inertia::render('UnidadesProducao/Index', [
            'unidades' => $unidades,
            'filters' => $request->only([
                'nome_cultura',
                'area_total_ha_min',
                'area_total_ha_max',
                'coordenadas_geograficas',
                'propriedade_id',
                'propriedade_nome',
                'produtor_id',
                'produtor_nome',
                'municipio',
                'uf',
                'created_at_inicio',
                'created_at_fim',
            ]),
            'pagination' => [
                'per_page' => $perPage,
                'options' => $allowedPerPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('UnidadesProducao/Create', [
            'propriedades' => Propriedade::with('produtorRural')->select('id', 'nome', 'produtor_id')->get(),
            'culturas' => [
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
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UnidadeProducaoRequest $request)
    {
        $unidade = UnidadeProducao::create($request->validated());

        return redirect()
            ->route('unidades-producao.index')
            ->with('success', 'Unidade de produção cadastrada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(UnidadeProducao $unidadeProducao)
    {
        $unidadeProducao->load('propriedade.produtorRural');

        return Inertia::render('UnidadesProducao/Show', [
            'unidade' => new UnidadeProducaoResource($unidadeProducao),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnidadeProducao $unidadeProducao)
    {
        // Carregar relacionamento para exibir no formulário
        $unidadeProducao->load('propriedade.produtorRural');

        return Inertia::render('UnidadesProducao/Edit', [
            'unidade' => [
                'id' => $unidadeProducao->id,
                'nome_cultura' => $unidadeProducao->nome_cultura,
                'area_total_ha' => (float) $unidadeProducao->area_total_ha,
                'coordenadas_geograficas' => $unidadeProducao->coordenadas_geograficas,
                'propriedade_id' => $unidadeProducao->propriedade_id,
            ],
            'propriedades' => Propriedade::with('produtorRural')->select('id', 'nome', 'produtor_id')->get(),
            'culturas' => [
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
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UnidadeProducaoRequest $request, UnidadeProducao $unidadeProducao)
    {
        $unidadeProducao->update($request->validated());

        return redirect()
            ->route('unidades-producao.index')
            ->with('success', 'Unidade de produção atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnidadeProducao $unidadeProducao)
    {
        $unidadeProducao->delete();

        return redirect()
            ->route('unidades-producao.index')
            ->with('success', 'Unidade de produção excluída com sucesso!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Rebanho;
use App\Models\Propriedade;
use App\Http\Requests\RebanhoRequest;
use App\Http\Resources\RebanhoResource;
use App\Helpers\SearchHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RebanhoController extends Controller
{
    /**
     * Display a listing of the resource.
     * Sistema de filtros avançados com busca flexível
     */
    public function index(Request $request)
    {
        $query = Rebanho::query();

        // Filtro: Espécie (aceita múltiplas seleções ou busca flexível)
        if ($request->filled('especie')) {
            $especieFilter = $request->especie;

            // Se vier como array (múltiplas espécies selecionadas)
            if (is_array($especieFilter)) {
                $query->whereIn('especie', $especieFilter);
            } else {
                // Busca flexível com SearchHelper (para compatibilidade)
                $searchTerms = SearchHelper::prepareSearchTerms($especieFilter);
                $query->where(function ($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $normalizedTerm = SearchHelper::normalize($term);
                        $q->orWhereRaw("unaccent(LOWER(especie)) LIKE ?", ["%{$normalizedTerm}%"]);
                    }
                });
            }
        }

        // Filtro: Finalidade (busca flexível com SearchHelper)
        if ($request->filled('finalidade')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->finalidade);
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $normalizedTerm = SearchHelper::normalize($term);
                    $q->orWhereRaw("unaccent(LOWER(finalidade)) LIKE ?", ["%{$normalizedTerm}%"]);
                }
            });
        }

        // Filtro: Quantidade mínima
        if ($request->filled('quantidade_min')) {
            $query->where('quantidade', '>=', $request->quantidade_min);
        }

        // Filtro: Quantidade máxima
        if ($request->filled('quantidade_max')) {
            $query->where('quantidade', '<=', $request->quantidade_max);
        }

        // Filtro: Propriedade por ID (UUID)
        if ($request->filled('propriedade_id')) {
            $query->where('propriedade_id', $request->propriedade_id);
        }

        // Filtro: Propriedade por nome (busca flexível via join)
        if ($request->filled('propriedade_nome')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->propriedade_nome);
            $query->whereHas('propriedade', function ($q) use ($searchTerms) {
                $q->where(function ($subQ) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $normalizedTerm = SearchHelper::normalize($term);
                        $subQ->orWhereRaw("unaccent(LOWER(nome)) LIKE ?", ["%{$normalizedTerm}%"]);
                    }
                });
            });
        }

        // Filtro: Produtor por ID (UUID - via propriedade)
        if ($request->filled('produtor_id')) {
            $query->whereHas('propriedade', function ($q) use ($request) {
                $q->where('produtor_id', $request->produtor_id);
            });
        }

        // Filtro: Produtor por nome (busca flexível via join duplo)
        if ($request->filled('produtor_nome')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->produtor_nome);
            $query->whereHas('propriedade.produtorRural', function ($q) use ($searchTerms) {
                $q->where(function ($subQ) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $normalizedTerm = SearchHelper::normalize($term);
                        $subQ->orWhereRaw("unaccent(LOWER(nome)) LIKE ?", ["%{$normalizedTerm}%"]);
                    }
                });
            });
        }

        // Filtro: Município (via propriedade)
        if ($request->filled('municipio')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->municipio);
            $query->whereHas('propriedade', function ($q) use ($searchTerms) {
                $q->where(function ($subQ) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $normalizedTerm = SearchHelper::normalize($term);
                        $subQ->orWhereRaw("unaccent(LOWER(municipio)) LIKE ?", ["%{$normalizedTerm}%"]);
                    }
                });
            });
        }

        // Filtro: UF (via propriedade)
        if ($request->filled('uf')) {
            $query->whereHas('propriedade', function ($q) use ($request) {
                $q->where('uf', strtoupper($request->uf));
            });
        }

        // Filtro: Data de Atualização (range)
        if ($request->filled('data_atualizacao_inicio')) {
            $query->whereDate('data_atualizacao', '>=', $request->data_atualizacao_inicio);
        }

        if ($request->filled('data_atualizacao_fim')) {
            $query->whereDate('data_atualizacao', '<=', $request->data_atualizacao_fim);
        }

        // Filtro: Data de Criação (range)
        if ($request->filled('created_at_inicio')) {
            $query->whereDate('created_at', '>=', $request->created_at_inicio);
        }

        if ($request->filled('created_at_fim')) {
            $query->whereDate('created_at', '<=', $request->created_at_fim);
        }

        // Validação e Ordenação (suporta campos relacionados)
        $sortFieldMapping = [
            'propriedade.nome' => 'propriedade_nome',
            'propriedade.produtor_rural.nome' => 'produtor_nome',
        ];

        $requestedSortField = $request->get('sort_field', 'created_at');

        // Mapear campo do PrimeVue para nome aceito pelo backend
        $sortField = $sortFieldMapping[$requestedSortField] ?? $requestedSortField;

        $allowedSortFields = ['id', 'especie', 'quantidade', 'finalidade', 'data_atualizacao', 'created_at', 'propriedade_nome', 'produtor_nome'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        $sortDirection = in_array($request->get('sort_direction'), ['asc', 'desc'])
            ? $request->get('sort_direction')
            : 'desc';

        // Aplicar ordenação (mapear campos relacionados para joins/colunas reais)
        if ($sortField === 'propriedade_nome') {
            // ordenar pela coluna propriedades.nome
            $query->leftJoin('propriedades', 'rebanhos.propriedade_id', '=', 'propriedades.id')
                ->orderBy('propriedades.nome', $sortDirection)
                ->select('rebanhos.*');
        } elseif ($sortField === 'produtor_nome') {
            // ordenar pelo nome do produtor (produtor_rurais.nome) via join
            $query->leftJoin('propriedades', 'rebanhos.propriedade_id', '=', 'propriedades.id')
                ->leftJoin('produtor_rurais', 'propriedades.produtor_id', '=', 'produtor_rurais.id')
                ->orderBy('produtor_rurais.nome', $sortDirection)
                ->select('rebanhos.*');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        // Validação de paginação
        $allowedPerPage = [15, 25, 50, 100];
        $perPage = in_array($request->get('per_page'), $allowedPerPage)
            ? (int) $request->get('per_page')
            : 15;

        // Eager loading para evitar N+1
        $rebanhos = $query->with('propriedade.produtorRural')
            ->paginate($perPage)
            ->appends($request->query());

        // Calcular total de animais nos rebanhos filtrados
        $totalAnimais = Rebanho::query();

        // Reaplicar os mesmos filtros para o cálculo
        if ($request->filled('especie')) {
            $especieFilter = $request->especie;

            if (is_array($especieFilter)) {
                $totalAnimais->whereIn('especie', $especieFilter);
            } else {
                $searchTerms = SearchHelper::prepareSearchTerms($especieFilter);
                $totalAnimais->where(function ($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $normalizedTerm = SearchHelper::normalize($term);
                        $q->orWhereRaw("unaccent(LOWER(especie)) LIKE ?", ["%{$normalizedTerm}%"]);
                    }
                });
            }
        }

        if ($request->filled('finalidade')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->finalidade);
            $totalAnimais->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $normalizedTerm = SearchHelper::normalize($term);
                    $q->orWhereRaw("unaccent(LOWER(finalidade)) LIKE ?", ["%{$normalizedTerm}%"]);
                }
            });
        }

        if ($request->filled('quantidade_min')) {
            $totalAnimais->where('quantidade', '>=', $request->quantidade_min);
        }

        if ($request->filled('quantidade_max')) {
            $totalAnimais->where('quantidade', '<=', $request->quantidade_max);
        }

        if ($request->filled('propriedade_id')) {
            $totalAnimais->where('propriedade_id', $request->propriedade_id);
        }

        if ($request->filled('propriedade_nome')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->propriedade_nome);
            $totalAnimais->whereHas('propriedade', function ($q) use ($searchTerms) {
                $q->where(function ($subQ) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $normalizedTerm = SearchHelper::normalize($term);
                        $subQ->orWhereRaw("unaccent(LOWER(nome)) LIKE ?", ["%{$normalizedTerm}%"]);
                    }
                });
            });
        }

        if ($request->filled('produtor_id')) {
            $totalAnimais->whereHas('propriedade', function ($q) use ($request) {
                $q->where('produtor_id', $request->produtor_id);
            });
        }

        if ($request->filled('produtor_nome')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->produtor_nome);
            $totalAnimais->whereHas('propriedade.produtorRural', function ($q) use ($searchTerms) {
                $q->where(function ($subQ) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $normalizedTerm = SearchHelper::normalize($term);
                        $subQ->orWhereRaw("unaccent(LOWER(nome)) LIKE ?", ["%{$normalizedTerm}%"]);
                    }
                });
            });
        }

        if ($request->filled('municipio')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->municipio);
            $totalAnimais->whereHas('propriedade', function ($q) use ($searchTerms) {
                $q->where(function ($subQ) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $normalizedTerm = SearchHelper::normalize($term);
                        $subQ->orWhereRaw("unaccent(LOWER(municipio)) LIKE ?", ["%{$normalizedTerm}%"]);
                    }
                });
            });
        }

        if ($request->filled('uf')) {
            $totalAnimais->whereHas('propriedade', function ($q) use ($request) {
                $q->where('uf', strtoupper($request->uf));
            });
        }

        if ($request->filled('data_atualizacao_inicio')) {
            $totalAnimais->whereDate('data_atualizacao', '>=', $request->data_atualizacao_inicio);
        }

        if ($request->filled('data_atualizacao_fim')) {
            $totalAnimais->whereDate('data_atualizacao', '<=', $request->data_atualizacao_fim);
        }

        if ($request->filled('created_at_inicio')) {
            $totalAnimais->whereDate('created_at', '>=', $request->created_at_inicio);
        }

        if ($request->filled('created_at_fim')) {
            $totalAnimais->whereDate('created_at', '<=', $request->created_at_fim);
        }

        $totalAnimaisCount = $totalAnimais->sum('quantidade');

        return Inertia::render('Rebanhos/Index', [
            'rebanhos' => $rebanhos,
            'totalAnimais' => $totalAnimaisCount,
            'especies' => ['Bovino', 'Suíno', 'Ovino', 'Caprino', 'Aves', 'Equino', 'Bubalino', 'Outros'],
            'filters' => $request->only([
                'especie',
                'finalidade',
                'quantidade_min',
                'quantidade_max',
                'propriedade_id',
                'propriedade_nome',
                'produtor_id',
                'produtor_nome',
                'municipio',
                'uf',
                'data_atualizacao_inicio',
                'data_atualizacao_fim',
                'created_at_inicio',
                'created_at_fim',
                'sort_field',
                'sort_direction',
                'per_page',
            ]),
            'perPageOptions' => $allowedPerPage,
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Rebanhos/Create', [
            'propriedades' => Propriedade::with('produtorRural')->select('id', 'nome', 'produtor_id')->get(),
            'especies' => ['Bovino', 'Suíno', 'Ovino', 'Caprino', 'Aves', 'Equino', 'Bubalino', 'Outros'],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RebanhoRequest $request)
    {
        $rebanho = Rebanho::create($request->validated());

        return redirect()
            ->route('rebanhos.index')
            ->with('success', 'Rebanho cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Rebanho $rebanho)
    {
        $rebanho->load('propriedade.produtorRural');

        return Inertia::render('Rebanhos/Show', [
            'rebanho' => new RebanhoResource($rebanho),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rebanho $rebanho)
    {
        // Carregar relacionamento para exibir no formulário
        $rebanho->load('propriedade.produtorRural');

        return Inertia::render('Rebanhos/Edit', [
            'rebanho' => [
                'id' => $rebanho->id,
                'especie' => $rebanho->especie,
                'quantidade' => $rebanho->quantidade,
                'finalidade' => $rebanho->finalidade,
                'data_atualizacao' => $rebanho->data_atualizacao?->toISOString(),
                'propriedade_id' => $rebanho->propriedade_id,
            ],
            'propriedades' => Propriedade::with('produtorRural')->select('id', 'nome', 'produtor_id')->get(),
            'especies' => ['Bovino', 'Suíno', 'Ovino', 'Caprino', 'Aves', 'Equino', 'Bubalino', 'Outros'],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RebanhoRequest $request, Rebanho $rebanho)
    {
        $rebanho->update($request->validated());

        return redirect()
            ->route('rebanhos.index')
            ->with('success', 'Rebanho atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rebanho $rebanho)
    {
        $rebanho->delete();

        return redirect()
            ->route('rebanhos.index')
            ->with('success', 'Rebanho excluído com sucesso!');
    }
}

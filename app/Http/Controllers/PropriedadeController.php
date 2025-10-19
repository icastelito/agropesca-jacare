<?php

namespace App\Http\Controllers;

use App\Models\Propriedade;
use App\Models\ProdutorRural;
use App\Http\Requests\PropriedadeRequest;
use App\Http\Resources\PropriedadeResource;
use App\Helpers\SearchHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class PropriedadeController extends Controller
{
    /**
     * Display a listing of the resource.
     * Com paginação e filtros avançados
     */
    public function index(Request $request)
    {
        $query = Propriedade::query();

        // Filtro por nome (busca flexível: case-insensitive, accent-insensitive, partial match)
        if ($request->filled('nome')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->nome);
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $normalizedTerm = SearchHelper::normalize($term);
                    // PostgreSQL: usa unaccent() para remover acentos em ambos os lados
                    $q->whereRaw('unaccent(LOWER(nome)) LIKE unaccent(?)', ['%' . $normalizedTerm . '%']);
                }
            });
        }

        // Filtro por município (busca flexível: case-insensitive, accent-insensitive, partial match)
        if ($request->filled('municipio')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->municipio);
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $normalizedTerm = SearchHelper::normalize($term);
                    // PostgreSQL: usa unaccent() para remover acentos
                    $q->whereRaw('unaccent(LOWER(municipio)) LIKE unaccent(?)', ['%' . $normalizedTerm . '%']);
                }
            });
        }

        // Filtro por UF (busca exata, case-insensitive)
        if ($request->filled('uf')) {
            $query->whereRaw('UPPER(uf) = ?', [strtoupper($request->uf)]);
        }

        // Filtro por inscrição estadual (busca por início)
        if ($request->filled('inscricao_estadual')) {
            $inscricao = preg_replace('/[^0-9]/', '', $request->inscricao_estadual);
            $query->where('inscricao_estadual', 'like', $inscricao . '%');
        }

        // Filtro por área total (range)
        if ($request->filled('area_total_min')) {
            $query->where('area_total', '>=', $request->area_total_min);
        }

        if ($request->filled('area_total_max')) {
            $query->where('area_total', '<=', $request->area_total_max);
        }

        // Filtro por produtor (ID exato)
        if ($request->filled('produtor_id')) {
            $query->where('produtor_id', $request->produtor_id);
        }

        // Filtro por nome do produtor (busca flexível com join)
        if ($request->filled('produtor_nome')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->produtor_nome);
            $query->whereHas('produtorRural', function ($q) use ($searchTerms) {
                $q->where(function ($q2) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $normalizedTerm = SearchHelper::normalize($term);
                        $q2->whereRaw('unaccent(LOWER(nome)) LIKE unaccent(?)', ['%' . $normalizedTerm . '%']);
                    }
                });
            });
        }

        // Filtro por período de criação
        if ($request->filled('created_at_inicio')) {
            $query->whereDate('created_at', '>=', $request->created_at_inicio);
        }

        if ($request->filled('created_at_fim')) {
            $query->whereDate('created_at', '<=', $request->created_at_fim);
        }

        // Ordenação
        $sortField = $request->get('sort_field', 'nome');
        $sortDirection = $request->get('sort_direction', 'asc');

        // Validar campo de ordenação para evitar SQL injection
        $allowedSortFields = ['nome', 'municipio', 'uf', 'inscricao_estadual', 'area_total', 'created_at'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'nome';
        }

        // Aplicar ordenação
        $query->orderBy($sortField, $sortDirection);

        // Quantidade por página (validar para evitar valores inválidos)
        $perPage = $request->get('per_page', 15);
        $allowedPerPage = [15, 25, 50, 100];

        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }

        // Paginação com contagens e eager loading
        $propriedades = $query
            ->with('produtorRural:id,nome')
            ->withCount(['unidadesProducao', 'rebanhos'])
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Propriedades/Index', [
            'propriedades' => [
                'data' => $propriedades->map(function ($propriedade) {
                    return [
                        'id' => $propriedade->id,
                        'nome' => $propriedade->nome,
                        'municipio' => $propriedade->municipio,
                        'uf' => $propriedade->uf,
                        'inscricao_estadual' => $propriedade->inscricao_estadual,
                        'area_total' => $propriedade->area_total,
                        'area_total_formatada' => number_format($propriedade->area_total, 2, ',', '.') . ' ha',
                        'produtor_nome' => $propriedade->produtorRural?->nome,
                        'produtor_id' => $propriedade->produtorRural?->id,
                        'total_unidades' => $propriedade->unidades_producao_count,
                        'total_rebanhos' => $propriedade->rebanhos_count,
                        'created_at' => $propriedade->created_at?->format('Y-m-d'),
                        'created_at_formatada' => $propriedade->created_at?->format('d/m/Y'),
                    ];
                }),
                'current_page' => $propriedades->currentPage(),
                'last_page' => $propriedades->lastPage(),
                'per_page' => $propriedades->perPage(),
                'total' => $propriedades->total(),
                'from' => $propriedades->firstItem(),
                'to' => $propriedades->lastItem(),
                'links' => $propriedades->linkCollection()->toArray(),
            ],
            'filters' => $request->only([
                'nome',
                'municipio',
                'uf',
                'inscricao_estadual',
                'area_total_min',
                'area_total_max',
                'produtor_id',
                'produtor_nome',
                'created_at_inicio',
                'created_at_fim'
            ]),
            'per_page' => $perPage,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Propriedades/Create', [
            'produtores' => ProdutorRural::select('id', 'nome')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PropriedadeRequest $request)
    {
        $propriedade = Propriedade::create($request->validated());

        return redirect()
            ->route('propriedades.index')
            ->with('success', 'Propriedade cadastrada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Propriedade $propriedade)
    {
        $propriedade->load(['produtorRural', 'unidadesProducao', 'rebanhos']);

        return Inertia::render('Propriedades/Show', [
            'propriedade' => new PropriedadeResource($propriedade),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Propriedade $propriedade)
    {
        // Carregar relacionamento para exibir no formulário
        $propriedade->load(['produtorRural', 'documentos']);

        // Debug: Verificar dados antes de enviar
        Log::info('Propriedade edit - ID: ' . $propriedade->id);
        Log::info('Propriedade edit - Dados: ', $propriedade->toArray());

        return Inertia::render('Propriedades/Edit', [
            'propriedade' => [
                'id' => $propriedade->id,
                'nome' => $propriedade->nome,
                'municipio' => $propriedade->municipio,
                'uf' => $propriedade->uf,
                'inscricao_estadual' => $propriedade->inscricao_estadual,
                'area_total' => (float) $propriedade->area_total,
                'produtor_id' => $propriedade->produtor_id,
                'data_cadastro' => $propriedade->data_cadastro?->format('d/m/Y'),
                'data_cadastro_iso' => $propriedade->data_cadastro?->format('Y-m-d'),
                'documentos' => $propriedade->documentos->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'nome_original' => $doc->nome_original,
                        'nome_arquivo' => $doc->nome_arquivo,
                        'tipo' => $doc->tipo,
                        'tamanho' => $doc->tamanho,
                        'categoria' => $doc->categoria,
                        'url' => $doc->url,
                        'tamanho_formatado' => $doc->tamanho_formatado,
                        'is_imagem' => $doc->is_imagem,
                        'is_pdf' => $doc->is_pdf,
                        'created_at' => $doc->created_at?->format('d/m/Y H:i'),
                    ];
                })->values()->toArray(),
            ],
            'produtores' => ProdutorRural::select('id', 'nome')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PropriedadeRequest $request, Propriedade $propriedade)
    {
        $propriedade->update($request->validated());

        return redirect()
            ->route('propriedades.index')
            ->with('success', 'Propriedade atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Propriedade $propriedade)
    {
        $propriedade->delete();

        return redirect()
            ->route('propriedades.index')
            ->with('success', 'Propriedade excluída com sucesso!');
    }
}

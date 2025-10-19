<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\UnidadeProducao;
use App\Models\Propriedade;
use App\Http\Requests\UnidadeProducaoRequest;
use App\Http\Resources\UnidadeProducaoResource;
use App\Helpers\SearchHelper;
use Illuminate\Http\Request;

class UnidadeProducaoController extends Controller
{
    public function index(Request $request)
    {
        $query = UnidadeProducao::query();

        if ($request->filled('nome_cultura')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->nome_cultura);
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $normalizedTerm = SearchHelper::normalize($term);
                    $q->whereRaw('unaccent(LOWER(nome_cultura)) LIKE unaccent(?)', ['%' . $normalizedTerm . '%']);
                }
            });
        }

        if ($request->filled('area_total_ha_min')) {
            $query->where('area_total_ha', '>=', $request->area_total_ha_min);
        }

        if ($request->filled('area_total_ha_max')) {
            $query->where('area_total_ha', '<=', $request->area_total_ha_max);
        }

        if ($request->filled('coordenadas_geograficas')) {
            $query->where('coordenadas_geograficas', 'like', '%' . $request->coordenadas_geograficas . '%');
        }

        if ($request->filled('propriedade_id')) {
            $query->where('propriedade_id', $request->propriedade_id);
        }

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

        if ($request->filled('produtor_id')) {
            $query->whereHas('propriedade', function ($q) use ($request) {
                $q->where('produtor_id', $request->produtor_id);
            });
        }

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

        if ($request->filled('uf')) {
            $query->whereHas('propriedade', function ($q) use ($request) {
                $q->where('uf', strtoupper($request->uf));
            });
        }

        if ($request->filled('created_at_inicio')) {
            $query->whereDate('created_at', '>=', $request->created_at_inicio);
        }

        if ($request->filled('created_at_fim')) {
            $query->whereDate('created_at', '<=', $request->created_at_fim);
        }

        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $allowedSortFields = ['id', 'nome_cultura', 'area_total_ha', 'created_at'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        $query->orderBy($sortField, $sortDirection);

        $perPage = $request->get('per_page', 15);
        $allowedPerPage = [15, 25, 50, 100];

        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }

        $unidades = $query->with('propriedade.produtorRural')->paginate($perPage)->appends($request->query());

        return response()->json([
            'success' => true,
            'data' => $unidades->items(),
            'pagination' => [
                'current_page' => $unidades->currentPage(),
                'last_page' => $unidades->lastPage(),
                'per_page' => $unidades->perPage(),
                'total' => $unidades->total(),
                'from' => $unidades->firstItem(),
                'to' => $unidades->lastItem(),
            ],
            'filters' => $request->only(['nome_cultura', 'area_total_ha_min', 'area_total_ha_max', 'coordenadas_geograficas', 'propriedade_id', 'propriedade_nome', 'produtor_id', 'produtor_nome', 'municipio', 'uf', 'created_at_inicio', 'created_at_fim']),
        ]);
    }

    public function store(UnidadeProducaoRequest $request)
    {
        $unidade = UnidadeProducao::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Unidade de produção cadastrada com sucesso!',
            'data' => new UnidadeProducaoResource($unidade)
        ], 201);
    }

    public function show(UnidadeProducao $unidadeProducao)
    {
        $unidadeProducao->load('propriedade.produtorRural');

        return response()->json([
            'success' => true,
            'data' => new UnidadeProducaoResource($unidadeProducao)
        ]);
    }

    public function update(UnidadeProducaoRequest $request, UnidadeProducao $unidadeProducao)
    {
        $unidadeProducao->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Unidade de produção atualizada com sucesso!',
            'data' => new UnidadeProducaoResource($unidadeProducao)
        ]);
    }

    public function destroy(UnidadeProducao $unidadeProducao)
    {
        $unidadeProducao->delete();

        return response()->json([
            'success' => true,
            'message' => 'Unidade de produção excluída com sucesso!'
        ]);
    }
}

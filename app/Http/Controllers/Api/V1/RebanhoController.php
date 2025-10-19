<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Rebanho;
use App\Models\Propriedade;
use App\Http\Requests\RebanhoRequest;
use App\Http\Resources\RebanhoResource;
use App\Helpers\SearchHelper;
use Illuminate\Http\Request;

class RebanhoController extends Controller
{
    public function index(Request $request)
    {
        $query = Rebanho::query();

        if ($request->filled('especie')) {
            $especieFilter = $request->especie;

            if (is_array($especieFilter)) {
                $query->whereIn('especie', $especieFilter);
            } else {
                $searchTerms = SearchHelper::prepareSearchTerms($especieFilter);
                $query->where(function ($q) use ($searchTerms) {
                    foreach ($searchTerms as $term) {
                        $normalizedTerm = SearchHelper::normalize($term);
                        $q->orWhereRaw("unaccent(LOWER(especie)) LIKE ?", ["%{$normalizedTerm}%"]);
                    }
                });
            }
        }

        if ($request->filled('finalidade')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->finalidade);
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $normalizedTerm = SearchHelper::normalize($term);
                    $q->orWhereRaw("unaccent(LOWER(finalidade)) LIKE ?", ["%{$normalizedTerm}%"]);
                }
            });
        }

        if ($request->filled('quantidade_min')) {
            $query->where('quantidade', '>=', $request->quantidade_min);
        }

        if ($request->filled('quantidade_max')) {
            $query->where('quantidade', '<=', $request->quantidade_max);
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
                        $subQ->orWhereRaw("unaccent(LOWER(nome)) LIKE ?", ["%{$normalizedTerm}%"]);
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
                        $subQ->orWhereRaw("unaccent(LOWER(nome)) LIKE ?", ["%{$normalizedTerm}%"]);
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
                        $subQ->orWhereRaw("unaccent(LOWER(municipio)) LIKE ?", ["%{$normalizedTerm}%"]);
                    }
                });
            });
        }

        if ($request->filled('uf')) {
            $query->whereHas('propriedade', function ($q) use ($request) {
                $q->where('uf', strtoupper($request->uf));
            });
        }

        if ($request->filled('data_atualizacao_inicio')) {
            $query->whereDate('data_atualizacao', '>=', $request->data_atualizacao_inicio);
        }

        if ($request->filled('data_atualizacao_fim')) {
            $query->whereDate('data_atualizacao', '<=', $request->data_atualizacao_fim);
        }

        if ($request->filled('created_at_inicio')) {
            $query->whereDate('created_at', '>=', $request->created_at_inicio);
        }

        if ($request->filled('created_at_fim')) {
            $query->whereDate('created_at', '<=', $request->created_at_fim);
        }

        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $allowedSortFields = ['id', 'especie', 'quantidade', 'finalidade', 'data_atualizacao', 'created_at'];

        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        $query->orderBy($sortField, $sortDirection);

        $allowedPerPage = [15, 25, 50, 100];
        $perPage = in_array($request->get('per_page'), $allowedPerPage) ? (int) $request->get('per_page') : 15;

        $rebanhos = $query->with('propriedade.produtorRural')->paginate($perPage)->appends($request->query());

        return response()->json([
            'success' => true,
            'data' => $rebanhos->items(),
            'pagination' => [
                'current_page' => $rebanhos->currentPage(),
                'last_page' => $rebanhos->lastPage(),
                'per_page' => $rebanhos->perPage(),
                'total' => $rebanhos->total(),
                'from' => $rebanhos->firstItem(),
                'to' => $rebanhos->lastItem(),
            ],
            'filters' => $request->only(['especie', 'finalidade', 'quantidade_min', 'quantidade_max', 'propriedade_id', 'propriedade_nome', 'produtor_id', 'produtor_nome', 'municipio', 'uf', 'data_atualizacao_inicio', 'data_atualizacao_fim', 'created_at_inicio', 'created_at_fim']),
        ]);
    }

    public function store(RebanhoRequest $request)
    {
        $rebanho = Rebanho::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Rebanho cadastrado com sucesso!',
            'data' => new RebanhoResource($rebanho)
        ], 201);
    }

    public function show(Rebanho $rebanho)
    {
        $rebanho->load('propriedade.produtorRural');

        return response()->json([
            'success' => true,
            'data' => new RebanhoResource($rebanho)
        ]);
    }

    public function update(RebanhoRequest $request, Rebanho $rebanho)
    {
        $rebanho->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Rebanho atualizado com sucesso!',
            'data' => new RebanhoResource($rebanho)
        ]);
    }

    public function destroy(Rebanho $rebanho)
    {
        $rebanho->delete();

        return response()->json([
            'success' => true,
            'message' => 'Rebanho excluído com sucesso!'
        ]);
    }
}

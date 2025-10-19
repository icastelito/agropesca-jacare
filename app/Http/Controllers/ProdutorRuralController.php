<?php

namespace App\Http\Controllers;

use App\Models\ProdutorRural;
use App\Http\Requests\ProdutorRuralRequest;
use App\Http\Resources\ProdutorRuralResource;
use App\Helpers\SearchHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProdutorRuralController extends Controller
{
    /**
     * Display a listing of the resource.
     * Com paginação e filtros
     */
    public function index(Request $request)
    {
        $query = ProdutorRural::query();

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

        // Filtro por CPF/CNPJ (usa índice único automático)
        if ($request->filled('cpf_cnpj')) {
            // Remove caracteres especiais para busca exata
            $cpfCnpj = preg_replace('/[^0-9]/', '', $request->cpf_cnpj);
            $query->where('cpf_cnpj', 'like', $cpfCnpj . '%');
        }

        // Filtro por endereço (busca flexível: case-insensitive, accent-insensitive, partial match)
        if ($request->filled('endereco')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->endereco);
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $normalizedTerm = SearchHelper::normalize($term);
                    // PostgreSQL: usa unaccent() para remover acentos
                    $q->whereRaw('unaccent(LOWER(endereco)) LIKE unaccent(?)', ['%' . $normalizedTerm . '%']);
                }
            });
        }

        // Filtro por telefone
        if ($request->filled('telefone')) {
            $telefone = preg_replace('/[^0-9]/', '', $request->telefone);
            $query->where('telefone', 'like', '%' . $telefone . '%');
        }

        // Filtro por email
        if ($request->filled('email')) {
            $searchTerms = SearchHelper::prepareSearchTerms($request->email);
            $query->where(function ($q) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $normalizedTerm = SearchHelper::normalize($term);
                    $q->whereRaw('unaccent(LOWER(email)) LIKE unaccent(?)', ['%' . $normalizedTerm . '%']);
                }
            });
        }

        // Filtro por período de data de cadastro
        if ($request->filled('data_cadastro_inicio')) {
            $query->whereDate('data_cadastro', '>=', $request->data_cadastro_inicio);
        }

        if ($request->filled('data_cadastro_fim')) {
            $query->whereDate('data_cadastro', '<=', $request->data_cadastro_fim);
        }

        // Ordenação
        $sortField = $request->get('sort_field', 'nome');
        $sortDirection = $request->get('sort_direction', 'asc');

        // Validar campo de ordenação para evitar SQL injection
        $allowedSortFields = ['nome', 'cpf_cnpj', 'telefone', 'email', 'endereco', 'data_cadastro', 'created_at'];

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

        // Paginação com contagem de propriedades
        $produtores = $query->withCount('propriedades')->paginate($perPage)->withQueryString();

        return Inertia::render('ProdutoresRurais/Index', [
            'produtores' => [
                'data' => $produtores->map(function ($produtor) {
                    return [
                        'id' => $produtor->id,
                        'nome' => $produtor->nome,
                        'cpf_cnpj' => $produtor->cpf_cnpj,
                        'telefone' => $produtor->telefone,
                        'email' => $produtor->email,
                        'endereco' => $produtor->endereco,
                        'data_cadastro' => $produtor->data_cadastro?->format('Y-m-d'),
                        'data_cadastro_formatada' => $produtor->data_cadastro?->format('d/m/Y'),
                        'total_propriedades' => $produtor->propriedades_count,
                    ];
                }),
                'current_page' => $produtores->currentPage(),
                'last_page' => $produtores->lastPage(),
                'per_page' => $produtores->perPage(),
                'total' => $produtores->total(),
                'from' => $produtores->firstItem(),
                'to' => $produtores->lastItem(),
                'links' => $produtores->linkCollection()->toArray(),
            ],
            'filters' => $request->only(['nome', 'cpf_cnpj', 'endereco', 'telefone', 'email', 'data_cadastro_inicio', 'data_cadastro_fim']),
            'per_page' => $perPage,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('ProdutoresRurais/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProdutorRuralRequest $request)
    {
        $produtor = ProdutorRural::create($request->validated());

        return redirect()
            ->route('produtores-rurais.index')
            ->with('success', 'Produtor rural cadastrado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProdutorRural $produtorRural)
    {
        $produtorRural->load(['propriedades.unidadesProducao', 'propriedades.rebanhos']);

        return Inertia::render('ProdutoresRurais/Show', [
            'produtor' => new ProdutorRuralResource($produtorRural),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProdutorRural $produtorRural)
    {
        // Carregar documentos relacionados
        $produtorRural->load('documentos');

        return Inertia::render('ProdutoresRurais/Edit', [
            'produtor' => new ProdutorRuralResource($produtorRural),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProdutorRuralRequest $request, ProdutorRural $produtorRural)
    {
        $produtorRural->update($request->validated());

        return redirect()
            ->route('produtores-rurais.index')
            ->with('success', 'Produtor rural atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProdutorRural $produtorRural)
    {
        $produtorRural->delete();

        return redirect()
            ->route('produtores-rurais.index')
            ->with('success', 'Produtor rural excluído com sucesso!');
    }
}

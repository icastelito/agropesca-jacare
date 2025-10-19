<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentoRequest;
use App\Models\Documento;
use App\Models\ProdutorRural;
use App\Models\Propriedade;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoController extends Controller
{
    public function store(DocumentoRequest $request, string $tipo, string $id): JsonResponse
    {
        try {
            $entidade = $this->getEntidade($tipo, $id);

            if (!$entidade) {
                return response()->json([
                    'success' => false,
                    'message' => 'Entidade não encontrada.'
                ], 404);
            }

            $file = $request->file('arquivo');
            $nomeUnico = Str::uuid() . '.' . $file->extension();
            $path = $file->storeAs('documentos', $nomeUnico, 'public');

            if (!$path) {
                throw new \Exception('Erro ao salvar o arquivo no storage.');
            }

            $documento = $entidade->documentos()->create([
                'nome_original' => $file->getClientOriginalName(),
                'nome_arquivo' => $nomeUnico,
                'tipo' => $file->getMimeType(),
                'tamanho' => $file->getSize(),
                'categoria' => $request->input('categoria'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Documento enviado com sucesso!',
                'data' => $documento
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erro ao fazer upload de documento: ' . $e->getMessage(), [
                'tipo' => $tipo,
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer upload do documento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function download(Documento $documento): StreamedResponse|JsonResponse
    {
        try {
            $filePath = 'documentos/' . $documento->nome_arquivo;

            if (!Storage::disk('public')->exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arquivo não encontrado no servidor.'
                ], 404);
            }

            return response()->streamDownload(function () use ($filePath) {
                echo Storage::disk('public')->get($filePath);
            }, $documento->nome_original, [
                'Content-Type' => $documento->tipo,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao fazer download de documento: ' . $e->getMessage(), [
                'documento_id' => $documento->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao fazer download do documento.'
            ], 500);
        }
    }

    public function destroy(Documento $documento): JsonResponse
    {
        try {
            $filePath = 'documentos/' . $documento->nome_arquivo;

            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            $documento->delete();

            return response()->json([
                'success' => true,
                'message' => 'Documento excluído com sucesso!'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir documento: ' . $e->getMessage(), [
                'documento_id' => $documento->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir documento.'
            ], 500);
        }
    }

    private function getEntidade(string $tipo, string $id): ProdutorRural|Propriedade|null
    {
        return match ($tipo) {
            'produtor' => ProdutorRural::find($id),
            'propriedade' => Propriedade::find($id),
            default => null,
        };
    }
}

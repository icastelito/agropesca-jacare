<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentoRequest;
use App\Models\Documento;
use App\Models\ProdutorRural;
use App\Models\Propriedade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentoController extends Controller
{
    /**
     * Armazena um novo documento
     *
     * @param DocumentoRequest $request
     * @param string $tipo - 'produtor' ou 'propriedade'
     * @param string $id - UUID da entidade
     * @return RedirectResponse
     */
    public function store(DocumentoRequest $request, string $tipo, string $id): RedirectResponse
    {
        try {
            // Validar tipo e obter a entidade
            $entidade = $this->getEntidade($tipo, $id);

            if (!$entidade) {
                return redirect()->back()
                    ->with('error', 'Entidade não encontrada.');
            }

            // Obter o arquivo
            $file = $request->file('arquivo');

            // Gerar nome único usando UUID
            $nomeUnico = Str::uuid() . '.' . $file->extension();

            // Salvar arquivo em storage/app/public/documentos/
            $path = $file->storeAs('documentos', $nomeUnico, 'public');

            if (!$path) {
                throw new \Exception('Erro ao salvar o arquivo no storage.');
            }

            // Criar registro no banco
            $entidade->documentos()->create([
                'nome_original' => $file->getClientOriginalName(),
                'nome_arquivo' => $nomeUnico,
                'tipo' => $file->getMimeType(),
                'tamanho' => $file->getSize(),
                'categoria' => $request->input('categoria'),
            ]);

            return redirect()->back()
                ->with('success', 'Documento enviado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao fazer upload de documento: ' . $e->getMessage(), [
                'tipo' => $tipo,
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao fazer upload do documento: ' . $e->getMessage());
        }
    }

    /**
     * Faz download de um documento
     *
     * @param Documento $documento
     * @return StreamedResponse|RedirectResponse
     */
    public function download(Documento $documento): StreamedResponse|RedirectResponse
    {
        try {
            $filePath = 'documentos/' . $documento->nome_arquivo;

            // Verificar se arquivo existe
            if (!Storage::disk('public')->exists($filePath)) {
                return redirect()->back()
                    ->with('error', 'Arquivo não encontrado no servidor.');
            }

            // Fazer download usando Storage::download
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

            return redirect()->back()
                ->with('error', 'Erro ao fazer download do documento.');
        }
    }

    /**
     * Remove um documento
     *
     * @param Documento $documento
     * @return RedirectResponse
     */
    public function destroy(Documento $documento): RedirectResponse
    {
        try {
            $filePath = 'documentos/' . $documento->nome_arquivo;

            // Excluir arquivo físico se existir
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            // Excluir registro do banco
            $documento->delete();

            return redirect()->back()
                ->with('success', 'Documento excluído com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir documento: ' . $e->getMessage(), [
                'documento_id' => $documento->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()
                ->with('error', 'Erro ao excluir documento.');
        }
    }

    /**
     * Obtém a entidade (Produtor ou Propriedade) baseado no tipo
     *
     * @param string $tipo
     * @param string $id
     * @return ProdutorRural|Propriedade|null
     */
    private function getEntidade(string $tipo, string $id): ProdutorRural|Propriedade|null
    {
        return match ($tipo) {
            'produtor' => ProdutorRural::find($id),
            'propriedade' => Propriedade::find($id),
            default => null,
        };
    }
}

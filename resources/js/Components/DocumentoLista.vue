<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import Card from 'primevue/card';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Image from 'primevue/image';
import Tag from 'primevue/tag';

// Interfaces
interface Documento {
    id: number;
    nome_original: string;
    nome_arquivo: string;
    tipo: string;
    tamanho: number;
    categoria: string | null;
    url: string;
    tamanho_formatado: string;
    is_imagem: boolean;
    is_pdf: boolean;
    created_at: string;
}

interface Props {
    documentos: Documento[];
}

const props = defineProps<Props>();

// Composables
const toast = useToast();
const confirm = useConfirm();

// State
const dialogVisible = ref(false);
const imagemPreview = ref<Documento | null>(null);

// Computed
const documentosOrdenados = computed(() => {
    return [...props.documentos].sort((a, b) => {
        return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
    });
});

// Formatar data
const formatarData = (dateString: string): string => {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return dateString;
    }
};

// Download do documento
const downloadDocumento = (documento: Documento) => {
    window.location.href = `/documentos/${documento.id}/download`;
    
    toast.add({
        severity: 'info',
        summary: 'Download',
        detail: 'Iniciando download do documento...',
        life: 2000,
    });
};

// Preview de imagem
const previewImagem = (documento: Documento) => {
    if (documento.is_imagem) {
        imagemPreview.value = documento;
        dialogVisible.value = true;
    }
};

// Excluir documento
const excluirDocumento = (documento: Documento) => {
    confirm.require({
        message: `Deseja realmente excluir o documento "${documento.nome_original}"?`,
        header: 'Confirmar Exclusão',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Sim, excluir',
        rejectLabel: 'Cancelar',
        acceptClass: 'p-button-danger',
        accept: () => {
            // Debug
            // eslint-disable-next-line no-console
            console.log('[DocumentoLista] solicitando delete para documento id=', documento.id);

            router.delete(`/documentos/${documento.id}`, {
                preserveScroll: true,
                preserveState: false, // Recarregar tudo para atualizar lista
                onSuccess: () => {
                    // eslint-disable-next-line no-console
                    console.log('[DocumentoLista] onSuccess callback called for delete', documento.id);
                },
                onError: () => {
                    // eslint-disable-next-line no-console
                    console.log('[DocumentoLista] onError callback called for delete', documento.id);
                    toast.add({
                        severity: 'error',
                        summary: 'Erro',
                        detail: 'Erro ao excluir documento.',
                        life: 5000,
                    });
                },
            });
        },
    });
};

// Obter severidade do Tag por categoria
const getSeveridadeCategoria = (categoria: string | null): string => {
    if (!categoria) return 'info';
    
    const severidades: Record<string, string> = {
        'CPF': 'success',
        'CNPJ': 'success',
        'RG': 'info',
        'Escritura': 'warning',
        'Foto': 'help',
        'Outros': 'secondary',
    };
    
    return severidades[categoria] || 'info';
};

// Obter ícone por tipo de arquivo
const getIconePorTipo = (documento: Documento): string => {
    if (documento.is_pdf) return 'pi pi-file-pdf text-red-500';
    if (documento.is_imagem) return 'pi pi-image text-blue-500';
    return 'pi pi-file text-gray-500';
};
</script>

<template>
    <Card>
        <template #title>
            <div class="flex align-items-center gap-2">
                <i class="pi pi-folder-open text-blue-500"></i>
                <span>Documentos</span>
                <Tag 
                    v-if="documentos.length > 0" 
                    :value="documentos.length.toString()" 
                    severity="info" 
                    rounded 
                />
            </div>
        </template>

        <template #content>
            <!-- Tabela de Documentos -->
            <DataTable
                v-if="documentos.length > 0"
                :value="documentosOrdenados"
                striped-rows
                responsive-layout="scroll"
                :paginator="documentos.length > 10"
                :rows="10"
                paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport"
                current-page-report-template="Mostrando {first} a {last} de {totalRecords} documentos"
            >
                <!-- Coluna: Tipo (ícone) -->
                <Column header="" style="width: 60px">
                    <template #body="{ data }">
                        <i :class="getIconePorTipo(data)" class="text-2xl"></i>
                    </template>
                </Column>

                <!-- Coluna: Nome -->
                <Column field="nome_original" header="Nome do Arquivo" sortable>
                    <template #body="{ data }">
                        <div class="flex align-items-center gap-2">
                            <span class="font-semibold">{{ data.nome_original }}</span>
                            <Button
                                v-if="data.is_imagem"
                                icon="pi pi-eye"
                                text
                                rounded
                                size="small"
                                severity="secondary"
                                @click="previewImagem(data)"
                                v-tooltip.top="'Visualizar'"
                            />
                        </div>
                    </template>
                </Column>

                <!-- Coluna: Categoria -->
                <Column field="categoria" header="Categoria" sortable style="width: 150px">
                    <template #body="{ data }">
                        <Tag 
                            v-if="data.categoria" 
                            :value="data.categoria" 
                            :severity="getSeveridadeCategoria(data.categoria)"
                        />
                        <span v-else class="text-400 text-sm">Sem categoria</span>
                    </template>
                </Column>

                <!-- Coluna: Tamanho -->
                <Column field="tamanho_formatado" header="Tamanho" sortable style="width: 120px">
                    <template #body="{ data }">
                        <span class="text-sm text-600">{{ data.tamanho_formatado }}</span>
                    </template>
                </Column>

                <!-- Coluna: Data -->
                <Column field="created_at" header="Data de Upload" sortable style="width: 180px">
                    <template #body="{ data }">
                        <span class="text-sm text-600">{{ formatarData(data.created_at) }}</span>
                    </template>
                </Column>

                <!-- Coluna: Ações -->
                <Column header="Ações" style="width: 150px">
                    <template #body="{ data }">
                        <div class="flex gap-2 justify-content-center">
                            <Button
                                icon="pi pi-download"
                                severity="info"
                                text
                                rounded
                                @click="downloadDocumento(data)"
                                v-tooltip.top="'Download'"
                            />
                            <Button
                                icon="pi pi-trash"
                                severity="danger"
                                text
                                rounded
                                @click="excluirDocumento(data)"
                                v-tooltip.top="'Excluir'"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Mensagem quando não há documentos -->
            <div v-else class="text-center py-6">
                <i class="pi pi-folder-open text-6xl text-300 mb-4"></i>
                <p class="text-600 text-lg font-semibold">Nenhum documento enviado</p>
                <p class="text-500 text-sm mt-2">
                    Use a aba "Upload de Documento" para adicionar arquivos
                </p>
            </div>
        </template>
    </Card>

    <!-- Dialog para preview de imagem -->
    <Dialog
        v-model:visible="dialogVisible"
        modal
        :header="imagemPreview?.nome_original"
        :style="{ width: '80vw', maxWidth: '900px' }"
        :dismissable-mask="true"
    >
        <div v-if="imagemPreview" class="flex justify-content-center align-items-center p-4">
            <Image 
                :src="imagemPreview.url" 
                :alt="imagemPreview.nome_original"
                preview
                class="max-w-full h-auto"
            />
        </div>
        
        <template #footer>
            <div class="flex gap-2 justify-content-end">
                <Button
                    label="Download"
                    icon="pi pi-download"
                    severity="info"
                    @click="downloadDocumento(imagemPreview!)"
                />
                <Button
                    label="Fechar"
                    icon="pi pi-times"
                    severity="secondary"
                    @click="dialogVisible = false"
                />
            </div>
        </template>
    </Dialog>
</template>

<style scoped>
/* Estilos do PrimeVue DataTable - sem customizações */
</style>

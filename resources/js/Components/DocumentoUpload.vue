<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from 'primevue/usetoast';
import Card from 'primevue/card';
import FileUpload, { FileUploadUploaderEvent } from 'primevue/fileupload';
import Dropdown from 'primevue/dropdown';
import Button from 'primevue/button';
import ProgressBar from 'primevue/progressbar';
import Message from 'primevue/message';

// Props
interface Props {
    tipo: 'produtor' | 'propriedade';
    id: string | number; // Suporta UUID (string) ou ID numérico
}

const props = defineProps<Props>();

// Composables
const toast = useToast();

// State
const categoriaSelecionada = ref<string | null>(null);
const uploading = ref(false);
const fileUploadRef = ref();

// Opções de categoria
const categorias = [
    { label: 'CPF', value: 'CPF' },
    { label: 'CNPJ', value: 'CNPJ' },
    { label: 'RG', value: 'RG' },
    { label: 'Escritura', value: 'Escritura' },
    { label: 'Foto', value: 'Foto' },
    { label: 'Outros', value: 'Outros' },
];

// Validação client-side
const validarArquivo = (file: File): string | null => {
    // Verificar tipo
    const tiposPermitidos = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
    if (!tiposPermitidos.includes(file.type)) {
        return 'Tipo de arquivo não permitido. Use PDF, JPG, JPEG ou PNG.';
    }

    // Verificar tamanho (5MB)
    const tamanhoMaximo = 5 * 1024 * 1024; // 5MB em bytes
    if (file.size > tamanhoMaximo) {
        return 'Arquivo muito grande. Tamanho máximo: 5MB.';
    }

    return null;
};

// Handler de upload
const uploadHandler = (event: FileUploadUploaderEvent) => {
    const files = event.files;

    // Garantir que files é um array
    const fileArray = Array.isArray(files) ? files : [files];

    if (!fileArray || fileArray.length === 0) {
        toast.add({
            severity: 'warn',
            summary: 'Atenção',
            detail: 'Nenhum arquivo selecionado.',
            life: 3000,
        });
        return;
    }

    const file = fileArray[0];

    // Validar arquivo
    const erroValidacao = validarArquivo(file);
    if (erroValidacao) {
        toast.add({
            severity: 'error',
            summary: 'Erro de Validação',
            detail: erroValidacao,
            life: 5000,
        });
        return;
    }

    // Criar FormData
    const formData = new FormData();
    formData.append('arquivo', file);
    
    if (categoriaSelecionada.value) {
        formData.append('categoria', categoriaSelecionada.value);
    }

    uploading.value = true;

    // Fazer upload usando Inertia
    router.post(`/documentos/${props.tipo}/${props.id}`, formData, {
        forceFormData: true,
        preserveScroll: true,
        preserveState: false, // Recarregar tudo para atualizar lista
        onSuccess: () => {
            // Debug
            // eslint-disable-next-line no-console
            console.log('[DocumentoUpload] onSuccess callback called');
            // Limpar formulário
            categoriaSelecionada.value = null;
            if (fileUploadRef.value) {
                fileUploadRef.value.clear();
            }
        },
        onError: (errors) => {
            // Debug
            // eslint-disable-next-line no-console
            console.log('[DocumentoUpload] onError callback called:', errors);
            const mensagemErro = errors.arquivo || errors.categoria || 'Erro ao fazer upload do documento.';
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: mensagemErro,
                life: 5000,
            });
        },
        onFinish: () => {
            // Debug
            // eslint-disable-next-line no-console
            console.log('[DocumentoUpload] onFinish callback called');
            uploading.value = false;
        },
    });
};

// Formatar tamanho do arquivo
const formatarTamanho = (bytes: number): string => {
    if (bytes < 1024) return bytes + ' bytes';
    if (bytes < 1048576) return (bytes / 1024).toFixed(2) + ' KB';
    return (bytes / 1048576).toFixed(2) + ' MB';
};
</script>

<template>
    <Card class="h-full">
        <template #title>
            <div class="flex align-items-center gap-2">
                <i class="pi pi-upload text-blue-500"></i>
                <span>Upload de Documento</span>
            </div>
        </template>

        <template #content>
            <!-- Mensagem informativa -->
            <Message severity="info" :closable="false" class="mb-4">
                <span class="text-sm">Tipos aceitos: <strong>PDF, JPG, JPEG, PNG</strong> (máximo 5MB)</span>
            </Message>

            <div class="flex flex-column gap-4">
                <!-- Seleção de Categoria -->
                <div class="flex flex-column">
                    <label for="categoria" class="block text-sm font-semibold text-700 mb-2">
                        Categoria (opcional)
                    </label>
                    <Dropdown
                        id="categoria"
                        v-model="categoriaSelecionada"
                        :options="categorias"
                        option-label="label"
                        option-value="value"
                        placeholder="Selecione uma categoria"
                        class="w-full"
                        :disabled="uploading"
                        :auto-focus="false"
                    />
                </div>

                <!-- FileUpload Component -->
                <div class="flex flex-column">
                    <FileUpload
                        ref="fileUploadRef"
                        name="arquivo"
                        :custom-upload="true"
                        @uploader="uploadHandler"
                        :multiple="false"
                        accept="application/pdf,image/jpeg,image/jpg,image/png"
                        :max-file-size="5242880"
                        :disabled="uploading"
                        :show-upload-button="true"
                        :show-cancel-button="false"
                        choose-label="Escolher Arquivo"
                        upload-label="Enviar"
                        cancel-label="Cancelar"
                    >
                        <template #empty>
                            <div class="flex flex-column align-items-center justify-content-center p-5 text-center">
                                <i class="pi pi-cloud-upload text-5xl text-400 mb-3"></i>
                                <p class="text-700 font-medium mb-1">Arraste um arquivo aqui ou clique para selecionar</p>
                                <p class="text-sm text-500">PDF, JPG, JPEG, PNG (máx 5MB)</p>
                            </div>
                        </template>

                        <template #content="{ files, removeFileCallback }">
                            <div v-if="files.length > 0">
                                <div class="flex flex-column gap-3">
                                    <div
                                        v-for="(file, index) of files"
                                        :key="file.name + file.type + file.size"
                                        class="p-3 border-1 surface-border border-round surface-50 flex align-items-center justify-content-between"
                                    >
                                        <div class="flex align-items-center gap-3 flex-1">
                                            <i
                                                :class="{
                                                    'pi pi-file-pdf text-red-500 text-2xl': file.type === 'application/pdf',
                                                    'pi pi-image text-blue-500 text-2xl': file.type.startsWith('image/')
                                                }"
                                            ></i>
                                            <div class="flex-1">
                                                <p class="font-semibold text-900 m-0 mb-1">{{ file.name }}</p>
                                                <p class="text-sm text-600 m-0">{{ formatarTamanho(file.size) }}</p>
                                            </div>
                                        </div>
                                        <Button
                                            icon="pi pi-times"
                                            severity="danger"
                                            text
                                            rounded
                                            @click="removeFileCallback(index)"
                                            :disabled="uploading"
                                        />
                                    </div>
                                </div>
                            </div>
                        </template>
                    </FileUpload>
                </div>

                <!-- Progress Bar durante upload -->
                <div v-if="uploading" class="mt-2">
                    <ProgressBar mode="indeterminate" style="height: 6px" />
                    <p class="text-sm text-600 text-center mt-2">
                        <i class="pi pi-spin pi-spinner mr-2"></i>
                        Enviando documento...
                    </p>
                </div>
            </div>
        </template>
    </Card>
</template>

<style scoped>
/* Estilos do PrimeVue FileUpload - sem customizações */
</style>

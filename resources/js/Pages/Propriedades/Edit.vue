<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, onMounted, watch } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';
import TabView from 'primevue/tabview';
import TabPanel from 'primevue/tabpanel';
import DocumentoUpload from '@/Components/DocumentoUpload.vue';
import DocumentoLista from '@/Components/DocumentoLista.vue';

interface ProdutorRural {
    id: string; // UUID
    nome: string;
}

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

interface Propriedade {
    id: string; // UUID
    nome: string;
    municipio: string;
    uf: string;
    inscricao_estadual: string;
    area_total: number;
    produtor_id: string; // UUID
    data_cadastro?: string;
    data_cadastro_iso?: string;
    documentos?: Documento[];
}

interface Props {
    propriedade: Propriedade;
    produtores: ProdutorRural[];
}

const props = defineProps<Props>();

// State para controlar a aba ativa do TabView
const activeTab = ref(0);

// Chave para salvar a aba ativa no sessionStorage
const STORAGE_KEY = `propriedade-edit-tab-${props.propriedade.id}`;

// Restaurar aba ativa ao montar componente
onMounted(() => {
    const savedTab = sessionStorage.getItem(STORAGE_KEY);
    if (savedTab !== null) {
        activeTab.value = parseInt(savedTab, 10);
        sessionStorage.removeItem(STORAGE_KEY); // Limpar após usar
    }
    
    // Debug: verificar dados recebidos
    console.log('Propriedade recebida:', props.propriedade);
    console.log('Produtores recebidos:', props.produtores);
});

// Salvar aba ativa quando mudar
watch(activeTab, (newTab) => {
    sessionStorage.setItem(STORAGE_KEY, newTab.toString());
});

// Lista de UFs
const ufs = ref([
    { label: 'AC - Acre', value: 'AC' },
    { label: 'AL - Alagoas', value: 'AL' },
    { label: 'AP - Amapá', value: 'AP' },
    { label: 'AM - Amazonas', value: 'AM' },
    { label: 'BA - Bahia', value: 'BA' },
    { label: 'CE - Ceará', value: 'CE' },
    { label: 'DF - Distrito Federal', value: 'DF' },
    { label: 'ES - Espírito Santo', value: 'ES' },
    { label: 'GO - Goiás', value: 'GO' },
    { label: 'MA - Maranhão', value: 'MA' },
    { label: 'MT - Mato Grosso', value: 'MT' },
    { label: 'MS - Mato Grosso do Sul', value: 'MS' },
    { label: 'MG - Minas Gerais', value: 'MG' },
    { label: 'PA - Pará', value: 'PA' },
    { label: 'PB - Paraíba', value: 'PB' },
    { label: 'PR - Paraná', value: 'PR' },
    { label: 'PE - Pernambuco', value: 'PE' },
    { label: 'PI - Piauí', value: 'PI' },
    { label: 'RJ - Rio de Janeiro', value: 'RJ' },
    { label: 'RN - Rio Grande do Norte', value: 'RN' },
    { label: 'RS - Rio Grande do Sul', value: 'RS' },
    { label: 'RO - Rondônia', value: 'RO' },
    { label: 'RR - Roraima', value: 'RR' },
    { label: 'SC - Santa Catarina', value: 'SC' },
    { label: 'SP - São Paulo', value: 'SP' },
    { label: 'SE - Sergipe', value: 'SE' },
    { label: 'TO - Tocantins', value: 'TO' }
]);

// Converter data_cadastro_iso para Date se disponível
const parseDate = (dateString: string | null | undefined): Date | null => {
    console.log('parseDate - recebeu:', dateString);
    
    if (!dateString) {
        console.log('parseDate - data vazia, retornando nova data');
        return new Date();
    }
    
    try {
        const date = new Date(dateString);
        console.log('parseDate - data parseada:', date);
        
        if (isNaN(date.getTime())) {
            console.log('parseDate - data inválida, retornando nova data');
            return new Date();
        }
        
        return date;
    } catch (error) {
        console.log('parseDate - erro ao parsear:', error);
        return new Date();
    }
};

// Formulário usando Inertia useForm
const form = useForm({
    nome: props.propriedade.nome,
    municipio: props.propriedade.municipio,
    uf: props.propriedade.uf,
    inscricao_estadual: props.propriedade.inscricao_estadual || '',
    area_total: props.propriedade.area_total,
    produtor_id: props.propriedade.produtor_id,
    data_cadastro: parseDate(props.propriedade.data_cadastro_iso),
});

// Debug: verificar valores do formulário
console.log('Valores do formulário:', {
    nome: form.nome,
    municipio: form.municipio,
    uf: form.uf,
    inscricao_estadual: form.inscricao_estadual,
    area_total: form.area_total,
    produtor_id: form.produtor_id,
    data_cadastro: form.data_cadastro,
});

// Submeter formulário
// Nota: Toast é gerenciado automaticamente pelo MainLayout via flash messages
const submit = () => {
    form.put(`/propriedades/${props.propriedade.id}`);
};

// Cancelar e voltar
const cancel = () => {
    router.get('/propriedades');
};
</script>

<template>
    <MainLayout>
        <Head title="Editar Propriedade" />

        <div class="mb-4">
            <div>
                <h1 class="text-3xl font-bold text-900">Editar Propriedade</h1>
                <p class="text-sm text-600 mt-2">Atualize os dados da propriedade {{ propriedade.nome }}</p>
            </div>
        </div>

        <TabView v-model:activeIndex="activeTab">
            <!-- Aba de Dados da Propriedade -->
            <TabPanel header="Dados da Propriedade">
                <Card>
                    <template #content>
                        <form @submit.prevent="submit" class="p-fluid">
                            <div class="grid">
                                <!-- Nome -->
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label for="nome">Nome  da Propriedade</label>
                                        <InputText
                                            id="nome"
                                            v-model="form.nome"
                                            :class="{ 'p-invalid': form.errors.nome }"
                                            placeholder="Digite o nome da propriedade"
                                        />
                                        <small v-if="form.errors.nome" class="p-error">
                                            {{ form.errors.nome }}
                                        </small>
                                    </div>
                                </div>

                                <!-- Produtor Rural -->
                                <div class="col-12 md:col-6">
                                    <div class="field">
                                        <label for="produtor_id">Produtor Rural <span class="text-red-500">*</span></label>
                                        <Dropdown
                                            id="produtor_id"
                                            v-model="form.produtor_id"
                                            :options="produtores"
                                            optionLabel="nome"
                                            optionValue="id"
                                            placeholder="Selecione o produtor"
                                            :class="{ 'p-invalid': form.errors.produtor_id }"
                                            :filter="true"
                                            filterPlaceholder="Buscar produtor"
                                            emptyMessage="Nenhum produtor encontrado"
                                        />
                                        <small v-if="form.errors.produtor_id" class="p-error">
                                            {{ form.errors.produtor_id }}
                                        </small>
                                    </div>
                                </div>

                                <!-- Município -->
                                <div class="col-12 md:col-5">
                                    <div class="field">
                                        <label for="municipio">Munic�pio <span class="text-red-500">*</span>Município</label>
                                        <InputText
                                            id="municipio"
                                            v-model="form.municipio"
                                            :class="{ 'p-invalid': form.errors.municipio }"
                                            placeholder="Digite o município"
                                        />
                                        <small v-if="form.errors.municipio" class="p-error">
                                            {{ form.errors.municipio }}
                                        </small>
                                    </div>
                                </div>

                                <!-- UF -->
                                <div class="col-12 md:col-2">
                                    <div class="field">
                                        <label for="uf">UF <span class="text-red-500">*</span></label>
                                        <Dropdown
                                            id="uf"
                                            v-model="form.uf"
                                            :options="ufs"
                                            optionLabel="label"
                                            optionValue="value"
                                            placeholder="Selecione"
                                            :class="{ 'p-invalid': form.errors.uf }"
                                            :filter="true"
                                        />
                                        <small v-if="form.errors.uf" class="p-error">
                                            {{ form.errors.uf }}
                                        </small>
                                    </div>
                                </div>

                                <!-- Inscrição Estadual -->
                                <div class="col-12 md:col-5">
                                    <div class="field">
                                        <label for="inscricao_estadual">Inscrição Estadual</label>
                                        <InputText
                                            id="inscricao_estadual"
                                            v-model="form.inscricao_estadual"
                                            :class="{ 'p-invalid': form.errors.inscricao_estadual }"
                                            placeholder="Digite a inscrição estadual"
                                        />
                                        <small v-if="form.errors.inscricao_estadual" class="p-error">
                                            {{ form.errors.inscricao_estadual }}
                                        </small>
                                    </div>
                                </div>

                                <!-- Área Total -->
                                <div class="col-12 md:col-3">
                                    <div class="field">
                                        <label for="area_total" >Área Total (hectares)</label>
                                        <InputNumber
                                            id="area_total"
                                            v-model="form.area_total"
                                            :class="{ 'p-invalid': form.errors.area_total }"
                                            placeholder="0.00"
                                            :minFractionDigits="2"
                                            :maxFractionDigits="2"
                                            :min="0"
                                            suffix=" ha"
                                        />
                                        <small v-if="form.errors.area_total" class="p-error">
                                            {{ form.errors.area_total }}
                                        </small>
                                    </div>
                                </div>

                                <!-- Data de Cadastro -->
                                <div class="col-12 md:col-4">
                                    <div class="field">
                                        <label for="data_cadastro">Data de Cadastro <span class="text-red-500">*</span></label>
                                        <Calendar
                                            id="data_cadastro"
                                            v-model="form.data_cadastro"
                                            :class="{ 'p-invalid': form.errors.data_cadastro }"
                                            dateFormat="dd/mm/yy"
                                            :showIcon="true"
                                            :maxDate="new Date()"
                                        />
                                        <small v-if="form.errors.data_cadastro" class="p-error">
                                            {{ form.errors.data_cadastro }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <!-- Ações -->
                            <div class="flex justify-content-end gap-2 mt-4">
                                <Button
                                    label="Cancelar"
                                    icon="pi pi-times"
                                    severity="secondary"
                                    type="button"
                                    @click="cancel"
                                    :disabled="form.processing"
                                />
                                <Button
                                    label="Atualizar"
                                    icon="pi pi-check"
                                    type="submit"
                                    :loading="form.processing"
                                    :disabled="form.processing"
                                />
                            </div>
                        </form>
                    </template>
                </Card>
            </TabPanel>

            <!-- Aba de Documentos -->
            <TabPanel header="Documentos">
                <div class="documentos-container">
                    <div class="documentos-grid">
                        <!-- Upload de novo documento -->
                        <div class="upload-section">
                            <DocumentoUpload tipo="propriedade" :id="propriedade.id" />
                        </div>
                        
                        <!-- Lista de documentos existentes -->
                        <div class="lista-section">
                            <DocumentoLista :documentos="propriedade.documentos || []" />
                        </div>
                    </div>
                </div>
            </TabPanel>
        </TabView>
    </MainLayout>
</template>

<style scoped>
/*  100% PrimeVue/PrimeFlex - Todos os estilos personalizados foram removidos */
</style>

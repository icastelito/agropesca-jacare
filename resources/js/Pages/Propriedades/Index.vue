<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Paginator from 'primevue/paginator';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import ConfirmDialog from 'primevue/confirmdialog';

interface Propriedade {
    id: string; // UUID
    nome: string;
    municipio: string;
    uf: string;
    inscricao_estadual: string;
    area_total: number;
    area_total_formatada: string;
    produtor_nome: string;
    produtor_id: string; // UUID
    total_unidades: number;
    total_rebanhos: number;
    created_at: string;
    created_at_formatada: string;
}

interface PaginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    propriedades: {
        data: Propriedade[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: PaginationLinks[];
    };
    filters: {
        nome?: string;
        municipio?: string;
        uf?: string;
        inscricao_estadual?: string;
        area_total_min?: number;
        area_total_max?: number;
        produtor_nome?: string;
        created_at_inicio?: string;
        created_at_fim?: string;
    };
    per_page?: number;
}

const props = defineProps<Props>();
const confirm = useConfirm();

// Lista de UFs
const ufs = ref([
    { label: 'Todos', value: '' },
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

// Estado
const isSearching = ref(false);
const sortField = ref('nome');
const sortOrder = ref(1); // 1 = ASC, -1 = DESC
const perPage = ref(props.per_page || 15);

const perPageOptions = ref([
    { label: '15 por página', value: 15 },
    { label: '25 por página', value: 25 },
    { label: '50 por página', value: 50 },
    { label: '100 por página', value: 100 },
]);

// Filtros
const filterNome = ref(props.filters.nome || '');
const filterMunicipio = ref(props.filters.municipio || '');
const filterUf = ref(props.filters.uf || '');
const filterInscricaoEstadual = ref(props.filters.inscricao_estadual || '');
const filterAreaMin = ref(props.filters.area_total_min || null);
const filterAreaMax = ref(props.filters.area_total_max || null);
const filterProdutorNome = ref(props.filters.produtor_nome || '');
const filterCreatedAtInicio = ref<Date | null>(
    props.filters.created_at_inicio ? new Date(props.filters.created_at_inicio) : null
);
const filterCreatedAtFim = ref<Date | null>(
    props.filters.created_at_fim ? new Date(props.filters.created_at_fim) : null
);

// Debounce
let debounceTimer: number | null = null;

const formatDateToYmd = (date: Date | null): string | undefined => {
    if (!date) return undefined;
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const performSearch = () => {
    isSearching.value = true;
    
    router.get('/propriedades', {
        nome: filterNome.value,
        municipio: filterMunicipio.value,
        uf: filterUf.value,
        inscricao_estadual: filterInscricaoEstadual.value,
        area_total_min: filterAreaMin.value,
        area_total_max: filterAreaMax.value,
        produtor_nome: filterProdutorNome.value,
        created_at_inicio: formatDateToYmd(filterCreatedAtInicio.value),
        created_at_fim: formatDateToYmd(filterCreatedAtFim.value),
        sort_field: sortField.value,
        sort_direction: sortOrder.value === 1 ? 'asc' : 'desc',
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['propriedades'],
        onFinish: () => {
            isSearching.value = false;
        },
    });
};

const debouncedSearch = () => {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    
    debounceTimer = window.setTimeout(() => {
        performSearch();
    }, 500);
};

// Watchers
watch(filterNome, debouncedSearch);
watch(filterMunicipio, debouncedSearch);
watch(filterUf, debouncedSearch);
watch(filterInscricaoEstadual, debouncedSearch);
watch(filterAreaMin, debouncedSearch);
watch(filterAreaMax, debouncedSearch);
watch(filterProdutorNome, debouncedSearch);
watch(filterCreatedAtInicio, debouncedSearch);
watch(filterCreatedAtFim, debouncedSearch);

const applyFilters = () => {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    performSearch();
};

const clearFilters = () => {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    
    filterNome.value = '';
    filterMunicipio.value = '';
    filterUf.value = '';
    filterInscricaoEstadual.value = '';
    filterAreaMin.value = null;
    filterAreaMax.value = null;
    filterProdutorNome.value = '';
    filterCreatedAtInicio.value = null;
    filterCreatedAtFim.value = null;
    
    router.get('/propriedades', {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['propriedades'],
        onFinish: () => {
            isSearching.value = false;
        },
    });
};

const onPageChange = (event: any) => {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    
    router.get('/propriedades', {
        page: event.page + 1,
        nome: filterNome.value,
        municipio: filterMunicipio.value,
        uf: filterUf.value,
        inscricao_estadual: filterInscricaoEstadual.value,
        area_total_min: filterAreaMin.value,
        area_total_max: filterAreaMax.value,
        produtor_nome: filterProdutorNome.value,
        created_at_inicio: formatDateToYmd(filterCreatedAtInicio.value),
        created_at_fim: formatDateToYmd(filterCreatedAtFim.value),
        sort_field: sortField.value,
        sort_direction: sortOrder.value === 1 ? 'asc' : 'desc',
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['propriedades'],
    });
};

watch(perPage, () => {
    performSearch();
});

const onSort = (event: any) => {
    sortField.value = event.sortField;
    sortOrder.value = event.sortOrder;
    performSearch();
};

const deletePropriedade = (id: number, nome: string) => {
    confirm.require({
        message: `Tem certeza que deseja excluir a propriedade "${nome}"?`,
        header: 'Confirmar Exclusão',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Sim, excluir',
        rejectLabel: 'Cancelar',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(`/propriedades/${id}`);
        },
    });
};

const hasActiveFilters = computed(() => {
    return filterNome.value || filterMunicipio.value || filterUf.value || 
           filterInscricaoEstadual.value || filterAreaMin.value || 
           filterAreaMax.value || filterProdutorNome.value;
});
</script>

<template>
    <MainLayout>
        <Head title="Propriedades" />

        <ConfirmDialog />

        <div class="mb-4">
            <div>
                <h1 class="text-3xl font-bold text-900">Propriedades</h1>
                <p class="text-sm text-600 mt-2">Gestão de propriedades rurais cadastradas</p>
            </div>
            <Link href="/propriedades/create">
                <Button label="Cadastrar Propriedade" icon="pi pi-plus" />
            </Link>
        </div>

        <!-- Card de Filtros -->
        <Card class="mb-4">
            <template #title>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-filter text-primary"></i>
                    <span>Filtros de Busca</span>
                    <Tag v-if="hasActiveFilters" :value="'Filtros ativos'" severity="info" />
                    <i v-if="isSearching" class="pi pi-spin pi-spinner text-primary ml-2"></i>
                    <span v-if="isSearching" class="text-sm text-primary">Buscando...</span>
                </div>
            </template>
            <template #content>
                <div class="grid">
                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterNome">Nome da Propriedade</label>
                        <InputText
                            id="filterNome"
                            v-model="filterNome"
                            placeholder="Buscar por nome..."
                            @keyup.enter="applyFilters"
                            :disabled="isSearching"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterMunicipio">Município</label>
                        <InputText
                            id="filterMunicipio"
                            v-model="filterMunicipio"
                            placeholder="Buscar por município..."
                            @keyup.enter="applyFilters"
                            :disabled="isSearching"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterUf">UF</label>
                        <Dropdown
                            id="filterUf"
                            v-model="filterUf"
                            :options="ufs"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Selecione o estado"
                            :showClear="true"
                            :disabled="isSearching"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterInscricao">Inscrição Estadual</label>
                        <InputText
                            id="filterInscricao"
                            v-model="filterInscricaoEstadual"
                            placeholder="Buscar por inscrição..."
                            @keyup.enter="applyFilters"
                            :disabled="isSearching"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterAreaMin">Área Mínima (ha)</label>
                        <InputNumber
                            id="filterAreaMin"
                            v-model="filterAreaMin"
                            placeholder="0.00"
                            :min="0"
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            suffix=" ha"
                            :disabled="isSearching"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterAreaMax">Área Máxima (ha)</label>
                        <InputNumber
                            id="filterAreaMax"
                            v-model="filterAreaMax"
                            placeholder="0.00"
                            :min="0"
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            suffix=" ha"
                            :disabled="isSearching"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterProdutor">Nome do Produtor</label>
                        <InputText
                            id="filterProdutor"
                            v-model="filterProdutorNome"
                            placeholder="Buscar por produtor..."
                            @keyup.enter="applyFilters"
                            :disabled="isSearching"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <!-- Filtros de Data -->
                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterCreatedInicio">Data Cadastro (De)</label>
                        <Calendar
                            id="filterCreatedInicio"
                            v-model="filterCreatedAtInicio"
                            dateFormat="dd/mm/yy"
                            placeholder="dd/mm/aaaa"
                            :disabled="isSearching"
                            :showIcon="true"
                            :showButtonBar="true"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterCreatedFim">Data Cadastro (Até)</label>
                        <Calendar
                            id="filterCreatedFim"
                            v-model="filterCreatedAtFim"
                            dateFormat="dd/mm/yy"
                            placeholder="dd/mm/aaaa"
                            :disabled="isSearching"
                            :showIcon="true"
                            :showButtonBar="true"
                            class="w-full"
                        />
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-3">
                    <Button
                        label="Buscar Agora"
                        icon="pi pi-search"
                        @click="applyFilters"
                        :loading="isSearching"
                    />
                    <Button
                        label="Limpar Filtros"
                        icon="pi pi-times"
                        severity="secondary"
                        outlined
                        @click="clearFilters"
                        :disabled="!hasActiveFilters"
                    />
                </div>
            </template>
        </Card>

        <!-- DataTable -->
        <Card class="mb-5">
            <template #title>
                <div class="flex justify-content-between align-items-center">
                    <span>Lista de Propriedades</span>
                    <span class="text-sm text-600">
                        {{ propriedades.total }} {{ propriedades.total === 1 ? 'registro' : 'registros' }} encontrado{{ propriedades.total === 1 ? '' : 's' }}
                    </span>
                </div>
            </template>
            <template #content>
                <DataTable
                    :value="propriedades.data"
                    stripedRows
                    responsiveLayout="scroll"
                    :loading="isSearching"
                    showGridlines
                    :sortField="sortField"
                    :sortOrder="sortOrder"
                    @sort="onSort"
                    tableStyle="min-width: 75rem"
                >
                    <Column field="nome" header="Nome" sortable />
                    <Column field="municipio" header="Município" sortable />
                    <Column field="uf" header="UF" style="width: 100px;" sortable />
                    <Column field="inscricao_estadual" header="Inscrição Estadual" style="width: 180px;" sortable />
                    <Column field="area_total_formatada" header="Área Total" style="width: 150px;" sortable />
                    <Column field="produtor_nome" header="Produtor Rural" sortable />
                    <Column field="total_unidades" header="Unidades" style="width: 120px;" bodyClass="text-center" sortable>
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.total_unidades.toString()" severity="info" />
                        </template>
                    </Column>
                    <Column field="total_rebanhos" header="Rebanhos" style="width: 120px;" bodyClass="text-center" sortable>
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.total_rebanhos.toString()" severity="success" />
                        </template>
                    </Column>
                    <Column field="created_at_formatada" header="Data Cadastro" style="width: 140px;" sortable />
                    <Column header="Ações" style="width: 180px;">
                        <template #body="slotProps">
                            <div class="flex gap-2 justify-content-center">
                                <Link :href="`/propriedades/${slotProps.data.id}`">
                                    <Button
                                        icon="pi pi-eye"
                                        severity="info"
                                        size="small"
                                        v-tooltip.top="'Visualizar'"
                                        text
                                        rounded
                                    />
                                </Link>
                                <Link :href="`/propriedades/${slotProps.data.id}/edit`">
                                    <Button
                                        icon="pi pi-pencil"
                                        severity="warning"
                                        size="small"
                                        v-tooltip.top="'Editar'"
                                        text
                                        rounded
                                    />
                                </Link>
                                <Button
                                    icon="pi pi-trash"
                                    severity="danger"
                                    size="small"
                                    v-tooltip.top="'Excluir'"
                                    text
                                    rounded
                                    @click="deletePropriedade(slotProps.data.id, slotProps.data.nome)"
                                />
                            </div>
                        </template>
                    </Column>
                    <template #empty>
                        <div class="text-center py-6">
                            <i class="pi pi-map text-6xl text-300 mb-4"></i>
                            <p>Nenhuma propriedade encontrada</p>
                        </div>
                    </template>
                </DataTable>

                <!-- Informações de paginação -->
                <div class="mt-3">
                    <span class="text-sm text-600">
                        Exibindo <strong>{{ propriedades.from }}</strong> a <strong>{{ propriedades.to }}</strong> 
                        de <strong>{{ propriedades.total }}</strong> propriedades
                        <span v-if="propriedades.last_page > 1" class="text-sm text-600">
                            (Página {{ propriedades.current_page }} de {{ propriedades.last_page }})
                        </span>
                    </span>
                </div>

                <div class="flex justify-content-between align-items-center mt-3">
                    <div class="flex align-items-center gap-2">
                        <label for="perPage" class="mr-2">Exibir:</label>
                        <Dropdown
                            id="perPage"
                            v-model="perPage"
                            :options="perPageOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Itens por página"
                        />
                    </div>

                    <Paginator
                        v-if="propriedades.last_page > 1"
                        :rows="propriedades.per_page"
                        :totalRecords="propriedades.total"
                        :first="(propriedades.current_page - 1) * propriedades.per_page"
                        @page="onPageChange"
                        template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
                    />
                </div>
            </template>
        </Card>
    </MainLayout>
</template>

<style scoped>
/* Estilos do PrimeVue - sem customizações */
</style>

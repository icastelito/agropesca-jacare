<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import InputNumber from 'primevue/inputnumber';
import Calendar from 'primevue/calendar';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import Paginator from 'primevue/paginator';
import { useConfirm } from 'primevue/useconfirm';
import ConfirmDialog from 'primevue/confirmdialog';

interface UnidadeProducao {
    id: string;
    nome_cultura: string;
    area_total_ha: number | string;
    coordenadas_geograficas: string | null;
    created_at: string;
    propriedade: {
        id: string;
        nome: string;
        municipio: string;
        uf: string;
        produtor_rural: {
            id: string;
            nome: string;
        };
    };
}

interface PaginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    unidades: {
        data: UnidadeProducao[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: PaginationLinks[];
    };
    filters: {
        nome_cultura?: string;
        area_total_ha_min?: number;
        area_total_ha_max?: number;
        coordenadas_geograficas?: string;
        propriedade_id?: string;
        propriedade_nome?: string;
        produtor_id?: string;
        produtor_nome?: string;
        municipio?: string;
        uf?: string;
        created_at_inicio?: string;
        created_at_fim?: string;
    };
    pagination: {
        per_page: number;
        options: number[];
    };
}

const props = defineProps<Props>();
const confirm = useConfirm();

// Estado dos filtros
const filterNomeCultura = ref(props.filters.nome_cultura || '');
const filterAreaMin = ref(props.filters.area_total_ha_min || null);
const filterAreaMax = ref(props.filters.area_total_ha_max || null);
const filterCoordenadas = ref(props.filters.coordenadas_geograficas || '');
const filterPropriedadeNome = ref(props.filters.propriedade_nome || '');
const filterProdutorNome = ref(props.filters.produtor_nome || '');
const filterMunicipio = ref(props.filters.municipio || '');
const filterUf = ref(props.filters.uf || '');
const filterDataInicio = ref(props.filters.created_at_inicio ? new Date(props.filters.created_at_inicio) : null);
const filterDataFim = ref(props.filters.created_at_fim ? new Date(props.filters.created_at_fim) : null);

// Controles
const loading = ref(false);
const perPage = ref(props.pagination.per_page);
const sortField = ref('created_at');
const sortDirection = ref<'asc' | 'desc'>('desc');
const sortOrder = computed(() => sortDirection.value === 'asc' ? 1 : -1);

// Opções de itens por página
const perPageOptions = [
    { label: '15 por página', value: 15 },
    { label: '25 por página', value: 25 },
    { label: '50 por página', value: 50 },
    { label: '100 por página', value: 100 }
];

// Timer para debounce
let searchTimer: ReturnType<typeof setTimeout> | null = null;

// Lista de UFs
const ufs = [
    { label: 'Acre', value: 'AC' },
    { label: 'Alagoas', value: 'AL' },
    { label: 'Amapá', value: 'AP' },
    { label: 'Amazonas', value: 'AM' },
    { label: 'Bahia', value: 'BA' },
    { label: 'Ceará', value: 'CE' },
    { label: 'Distrito Federal', value: 'DF' },
    { label: 'Espírito Santo', value: 'ES' },
    { label: 'Goiás', value: 'GO' },
    { label: 'Maranhão', value: 'MA' },
    { label: 'Mato Grosso', value: 'MT' },
    { label: 'Mato Grosso do Sul', value: 'MS' },
    { label: 'Minas Gerais', value: 'MG' },
    { label: 'Pará', value: 'PA' },
    { label: 'Paraíba', value: 'PB' },
    { label: 'Paraná', value: 'PR' },
    { label: 'Pernambuco', value: 'PE' },
    { label: 'Piauí', value: 'PI' },
    { label: 'Rio de Janeiro', value: 'RJ' },
    { label: 'Rio Grande do Norte', value: 'RN' },
    { label: 'Rio Grande do Sul', value: 'RS' },
    { label: 'Rondônia', value: 'RO' },
    { label: 'Roraima', value: 'RR' },
    { label: 'Santa Catarina', value: 'SC' },
    { label: 'São Paulo', value: 'SP' },
    { label: 'Sergipe', value: 'SE' },
    { label: 'Tocantins', value: 'TO' }
];

// Função de busca com parâmetros
const performSearch = (additionalParams = {}) => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    loading.value = true;

    router.get('/unidades-producao', {
        nome_cultura: filterNomeCultura.value || undefined,
        area_total_ha_min: filterAreaMin.value || undefined,
        area_total_ha_max: filterAreaMax.value || undefined,
        coordenadas_geograficas: filterCoordenadas.value || undefined,
        propriedade_nome: filterPropriedadeNome.value || undefined,
        produtor_nome: filterProdutorNome.value || undefined,
        municipio: filterMunicipio.value || undefined,
        uf: filterUf.value || undefined,
        created_at_inicio: filterDataInicio.value ? formatDateToSQL(filterDataInicio.value) : undefined,
        created_at_fim: filterDataFim.value ? formatDateToSQL(filterDataFim.value) : undefined,
        per_page: perPage.value,
        sort_field: sortField.value,
        sort_direction: sortDirection.value,
        ...additionalParams,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['unidades', 'filters'],
        onFinish: () => {
            loading.value = false;
        },
    });
};

// Busca com debounce
const debouncedSearch = () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    searchTimer = setTimeout(() => {
        performSearch();
    }, 500);
};

// Watchers para busca automática
watch(filterNomeCultura, () => debouncedSearch());
watch(filterAreaMin, () => debouncedSearch());
watch(filterAreaMax, () => debouncedSearch());
watch(filterCoordenadas, () => debouncedSearch());
watch(filterPropriedadeNome, () => debouncedSearch());
watch(filterProdutorNome, () => debouncedSearch());
watch(filterMunicipio, () => debouncedSearch());
watch(filterUf, () => debouncedSearch());
watch(filterDataInicio, () => debouncedSearch());
watch(filterDataFim, () => debouncedSearch());
watch(perPage, () => performSearch({ page: 1 }));

// Aplicar filtros
const applyFilters = () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }
    performSearch();
};

// Limpar filtros
const clearFilters = () => {
    if (searchTimer) {
        clearTimeout(searchTimer);
    }

    filterNomeCultura.value = '';
    filterAreaMin.value = null;
    filterAreaMax.value = null;
    filterCoordenadas.value = '';
    filterPropriedadeNome.value = '';
    filterProdutorNome.value = '';
    filterMunicipio.value = '';
    filterUf.value = '';
    filterDataInicio.value = null;
    filterDataFim.value = null;

    performSearch();
};

// Ordenação
const onSort = (event: any) => {
    sortField.value = event.sortField;
    sortDirection.value = event.sortOrder === 1 ? 'asc' : 'desc';
    performSearch();
};

// Paginação
const onPageChange = (event: any) => {
    performSearch({ page: event.page + 1 });
};

// Deletar unidade
const deleteUnidade = (id: string, cultura: string) => {
    confirm.require({
        message: `Tem certeza que deseja excluir a unidade de produção "${cultura}"?`,
        header: 'Confirmar Exclusão',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Sim, excluir',
        rejectLabel: 'Cancelar',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(`/unidades-producao/${id}`, {
                preserveScroll: true,
            });
        },
    });
};

// Verificar se há filtros ativos
const hasActiveFilters = computed(() => {
    return !!(
        filterNomeCultura.value ||
        filterAreaMin.value ||
        filterAreaMax.value ||
        filterCoordenadas.value ||
        filterPropriedadeNome.value ||
        filterProdutorNome.value ||
        filterMunicipio.value ||
        filterUf.value ||
        filterDataInicio.value ||
        filterDataFim.value
    );
});

// Formatação de data
const formatDateToSQL = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

const formatDate = (dateString: string): string => {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        });
    } catch {
        return dateString;
    }
};

// Truncar coordenadas
const truncateCoords = (coords: string | null): string => {
    if (!coords) return '-';
    return coords.length > 30 ? coords.substring(0, 30) + '...' : coords;
};
</script>

<template>
    <MainLayout>
        <Head title="Unidades de Produção" />

        <ConfirmDialog />

        <div class="mb-4">
            <div>
                <h1 class="text-3xl font-bold text-900">Unidades de Produção</h1>
                <p class="text-sm text-600 mt-2">Gestão de unidades de produção agrícola</p>
            </div>
            <Link href="/unidades-producao/create">
                <Button label="Cadastrar Unidade" icon="pi pi-plus" />
            </Link>
        </div>

        <Card class="mb-4">
            <template #title>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-filter text-primary"></i>
                    <span>Filtros de Busca</span>
                    <Tag v-if="hasActiveFilters" :value="'Filtros ativos'" severity="info" />
                    <i v-if="loading" class="pi pi-spin pi-spinner text-primary ml-2"></i>
                    <span v-if="loading" class="text-sm text-primary">Buscando...</span>
                </div>
            </template>
            <template #content>
                <div class="grid">
                    <!-- Nome da Cultura -->
                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterNomeCultura">Nome da Cultura</label>
                        <InputText
                            id="filterNomeCultura"
                            v-model="filterNomeCultura"
                            placeholder="Ex: Laranja, Melancia..."
                            class="w-full"
                        />
                        </div>
                    </div>

                    <!-- Área Mínima -->
                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterAreaMin">Área Mínima (ha)</label>
                        <InputNumber
                            id="filterAreaMin"
                            v-model="filterAreaMin"
                            placeholder="0.00"
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            :min="0"
                            suffix=" ha"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <!-- Área Máxima -->
                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterAreaMax">Área Máxima (ha)</label>
                        <InputNumber
                            id="filterAreaMax"
                            v-model="filterAreaMax"
                            placeholder="0.00"
                            :minFractionDigits="2"
                            :maxFractionDigits="2"
                            :min="0"
                            suffix=" ha"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <!-- Coordenadas -->
                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterCoordenadas">Coordenadas</label>
                        <InputText
                            id="filterCoordenadas"
                            v-model="filterCoordenadas"
                            placeholder="Buscar por coordenadas..."
                            class="w-full"
                        />
                        </div>
                    </div>

                    <!-- Nome da Propriedade -->
                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterPropriedadeNome">Nome da Propriedade</label>
                        <InputText
                            id="filterPropriedadeNome"
                            v-model="filterPropriedadeNome"
                            placeholder="Buscar por propriedade..."
                            class="w-full"
                        />
                        </div>
                    </div>

                    <!-- Nome do Produtor -->
                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterProdutorNome">Nome do Produtor</label>
                        <InputText
                            id="filterProdutorNome"
                            v-model="filterProdutorNome"
                            placeholder="Buscar por produtor..."
                            class="w-full"
                        />
                        </div>
                    </div>

                    <!-- Município -->
                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterMunicipio">Município</label>
                        <InputText
                            id="filterMunicipio"
                            v-model="filterMunicipio"
                            placeholder="Buscar por município..."
                            class="w-full"
                        />
                        </div>
                    </div>

                    <!-- UF -->
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
                            class="w-full"
                        />
                        </div>
                    </div>

                    <!-- Data Criação Início -->
                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterDataInicio">Data Cadastro (De)</label>
                        <Calendar
                            id="filterDataInicio"
                            v-model="filterDataInicio"
                            dateFormat="dd/mm/yy"
                            placeholder="dd/mm/aaaa"
                            :showIcon="true"
                            :showButtonBar="true"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <!-- Data Criação Fim -->
                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterDataFim">Data Cadastro (Até)</label>
                        <Calendar
                            id="filterDataFim"
                            v-model="filterDataFim"
                            dateFormat="dd/mm/yy"
                            placeholder="dd/mm/aaaa"
                            :showIcon="true"
                            :showButtonBar="true"
                            class="w-full"
                        />
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex gap-3 mt-3">
                    <Button
                        label="Buscar Agora"
                        icon="pi pi-search"
                        @click="applyFilters"
                        :loading="loading"
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
                    <span>Lista de Unidades</span>
                    <span class="text-sm text-600">
                        {{ unidades.total }} {{ unidades.total === 1 ? 'registro' : 'registros' }} encontrado{{ unidades.total === 1 ? '' : 's' }}
                    </span>
                </div>
            </template>
            <template #content>
                <DataTable
                    :value="unidades.data"
                    stripedRows
                    responsiveLayout="scroll"
                    :loading="loading"
                    showGridlines
                    :sortField="sortField"
                    :sortOrder="sortOrder"
                    @sort="onSort"
                    tableStyle="min-width: 75rem"
                >
                    <!-- Cultura -->
                    <Column field="nome_cultura" header="Cultura" sortable />

                    <!-- Área Total -->
                    <Column field="area_total_ha" header="Área Total" sortable style="width: 150px" />

                    <!-- Coordenadas -->
                    <Column field="coordenadas_geograficas" header="Coordenadas" style="width: 200px">
                        <template #body="{ data }">
                            <span class="coordenadas-text" v-tooltip.top="data.coordenadas_geograficas">
                                {{ truncateCoords(data.coordenadas_geograficas) }}
                            </span>
                        </template>
                    </Column>

                    <!-- Propriedade -->
                    <Column field="propriedade.nome" header="Propriedade" sortable>
                        <template #body="{ data }">
                            <Link 
                                v-if="data.propriedade"
                                :href="`/propriedades/${data.propriedade.id}`" 
                                class="table-link"
                            >
                                {{ data.propriedade.nome }}
                            </Link>
                            <span v-else class="text-gray-400">-</span>
                        </template>
                    </Column>

                    <!-- Município -->
                    <Column field="propriedade.municipio" header="Município" sortable>
                        <template #body="{ data }">
                            {{ data.propriedade?.municipio || '-' }}
                        </template>
                    </Column>

                    <!-- UF -->
                    <Column field="propriedade.uf" header="UF" sortable style="width: 80px">
                        <template #body="{ data }">
                            {{ data.propriedade?.uf || '-' }}
                        </template>
                    </Column>

                    <!-- Produtor Rural -->
                    <Column field="propriedade.produtor_rural.nome" header="Produtor Rural" sortable>
                        <template #body="{ data }">
                            <Link 
                                v-if="data.propriedade?.produtor_rural"
                                :href="`/produtores-rurais/${data.propriedade.produtor_rural.id}`" 
                                class="text-primary no-underline hover:underline"
                            >
                                {{ data.propriedade.produtor_rural.nome }}
                            </Link>
                            <span v-else class="text-400">-</span>
                        </template>
                    </Column>

                    <!-- Data Cadastro -->
                    <Column field="created_at" header="Data Cadastro" sortable style="width: 140px">
                        <template #body="{ data }">
                            {{ formatDate(data.created_at) }}
                        </template>
                    </Column>

                    <!-- Ações -->
                    <Column header="Ações" style="width: 180px">
                        <template #body="{ data }">
                            <div class="flex gap-2 justify-content-center">
                                <Link :href="`/unidades-producao/${data.id}`">
                                    <Button
                                        icon="pi pi-eye"
                                        severity="info"
                                        size="small"
                                        v-tooltip.top="'Visualizar'"
                                        text
                                        rounded
                                    />
                                </Link>
                                <Link :href="`/unidades-producao/${data.id}/edit`">
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
                                    @click="deleteUnidade(data.id, data.nome_cultura)"
                                />
                            </div>
                        </template>
                    </Column>

                    <!-- Empty State -->
                    <template #empty>
                        <div class="text-center py-6">
                            <i class="pi pi-th-large text-6xl text-300 mb-4"></i>
                            <p>Nenhuma unidade de produção encontrada</p>
                        </div>
                    </template>
                </DataTable>

                <!-- Informações de paginação -->
                <div class="mt-3">
                    <span class="text-sm text-600">
                        Exibindo <strong>{{ unidades.from }}</strong> a <strong>{{ unidades.to }}</strong> 
                        de <strong>{{ unidades.total }}</strong> unidades de produção
                        <span v-if="unidades.last_page > 1" class="text-sm text-600">
                            (Página {{ unidades.current_page }} de {{ unidades.last_page }})
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
                        v-if="unidades.last_page > 1"
                        :rows="unidades.per_page"
                        :totalRecords="unidades.total"
                        :first="(unidades.current_page - 1) * unidades.per_page"
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
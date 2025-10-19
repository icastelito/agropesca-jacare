<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Dropdown from 'primevue/dropdown';
import MultiSelect from 'primevue/multiselect';
import InputNumber from 'primevue/inputnumber';
import Paginator from 'primevue/paginator';
import Card from 'primevue/card';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import ConfirmDialog from 'primevue/confirmdialog';

interface Rebanho {
    id: string;
    especie: string;
    quantidade: number;
    quantidade_formatada?: string;
    finalidade: string;
    data_atualizacao: string;
    data_atualizacao_formatada?: string;
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
    rebanhos: {
        data: Rebanho[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
        links: PaginationLinks[];
    };
    especies: string[];
    totalAnimais?: number;
    filters: {
        especie?: string | string[];
        finalidade?: string;
        propriedade_nome?: string;
        produtor_nome?: string;
        propriedade_id?: string;
        quantidade_min?: number;
        quantidade_max?: number;
    };
    perPageOptions: number[];
    sortField?: string;
    sortDirection?: string;
    per_page?: number;
}

const props = defineProps<Props>();
const confirm = useConfirm();

// Estado de loading para feedback visual
const isSearching = ref(false);

// Estado de ordenação
const sortField = ref('created_at');
const sortOrder = ref(-1); // 1 = ASC, -1 = DESC

// Itens por página
const perPage = ref(props.per_page || props.rebanhos.per_page);
const perPageOptions = ref([
    { label: '15 por página', value: 15 },
    { label: '25 por página', value: 25 },
    { label: '50 por página', value: 50 },
    { label: '100 por página', value: 100 },
]);

// Filtros
const filterEspecie = ref<string[]>(
    Array.isArray(props.filters.especie) 
        ? props.filters.especie 
        : props.filters.especie 
            ? [props.filters.especie] 
            : []
);
const filterFinalidade = ref(props.filters.finalidade || '');
const filterPropriedadeNome = ref(props.filters.propriedade_nome || '');
const filterProdutorNome = ref(props.filters.produtor_nome || '');
const filterQuantidadeMin = ref(props.filters.quantidade_min || null);
const filterQuantidadeMax = ref(props.filters.quantidade_max || null);

// Opções de espécies para multiselect
const especiesOptions = props.especies.map(especie => ({
    label: especie,
    value: especie,
}));

// Opções de finalidades
const finalidadesOptions = [
    { label: 'Corte', value: 'Corte' },
    { label: 'Leite', value: 'Leite' },
    { label: 'Mista', value: 'Mista' },
    { label: 'Reprodução', value: 'Reprodução' },
    { label: 'Trabalho', value: 'Trabalho' },
    { label: 'Outros', value: 'Outros' },
];

// Timer para debounce
let debounceTimer: number | null = null;

// Função de busca otimizada com debounce
const performSearch = () => {
    isSearching.value = true;
    
    router.get('/rebanhos', {
        especie: filterEspecie.value.length > 0 ? filterEspecie.value : undefined,
        finalidade: filterFinalidade.value || undefined,
        propriedade_nome: filterPropriedadeNome.value || undefined,
        produtor_nome: filterProdutorNome.value || undefined,
        quantidade_min: filterQuantidadeMin.value || undefined,
        quantidade_max: filterQuantidadeMax.value || undefined,
        sort_field: sortField.value,
        sort_direction: sortOrder.value === 1 ? 'asc' : 'desc',
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['rebanhos'],
        onFinish: () => {
            isSearching.value = false;
        },
    });
};

// Debounce search - aguarda 500ms após o usuário parar de digitar
const debouncedSearch = () => {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    
    debounceTimer = window.setTimeout(() => {
        performSearch();
    }, 500);
};

// Watchers para busca em tempo real
watch(filterEspecie, () => {
    debouncedSearch();
});

watch(filterFinalidade, () => {
    debouncedSearch();
});

watch(filterPropriedadeNome, () => {
    debouncedSearch();
});

watch(filterProdutorNome, () => {
    debouncedSearch();
});

watch(filterQuantidadeMin, () => {
    debouncedSearch();
});

watch(filterQuantidadeMax, () => {
    debouncedSearch();
});

// Aplicar filtros manualmente (caso usuário clique no botão)
const applyFilters = () => {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    performSearch();
};

// Limpar filtros
const clearFilters = () => {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    
    filterEspecie.value = [];
    filterFinalidade.value = '';
    filterPropriedadeNome.value = '';
    filterProdutorNome.value = '';
    filterQuantidadeMin.value = null;
    filterQuantidadeMax.value = null;
    
    router.get('/rebanhos', {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['rebanhos'],
        onFinish: () => {
            isSearching.value = false;
        },
    });
};

// Paginação
const onPageChange = (event: any) => {
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    
    router.get('/rebanhos', {
        page: event.page + 1,
        especie: filterEspecie.value.length > 0 ? filterEspecie.value : undefined,
        finalidade: filterFinalidade.value || undefined,
        propriedade_nome: filterPropriedadeNome.value || undefined,
        produtor_nome: filterProdutorNome.value || undefined,
        quantidade_min: filterQuantidadeMin.value || undefined,
        quantidade_max: filterQuantidadeMax.value || undefined,
        sort_field: sortField.value,
        sort_direction: sortOrder.value === 1 ? 'asc' : 'desc',
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['rebanhos'],
    });
};

// Alterar quantidade por página
const onPerPageChange = () => {
    performSearch();
};

// Watch per page changes
watch(perPage, () => {
    onPerPageChange();
});

// Ordenação
const onSort = (event: any) => {
    sortField.value = event.sortField;
    sortOrder.value = event.sortOrder;
    performSearch();
};

// Deletar rebanho
const deleteRebanho = (id: string, especie: string) => {
    confirm.require({
        message: `Tem certeza que deseja excluir o rebanho de "${especie}"?`,
        header: 'Confirmar Exclusão',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Sim, excluir',
        rejectLabel: 'Cancelar',
        acceptClass: 'p-button-danger',
        accept: () => {
            router.delete(`/rebanhos/${id}`);
        },
    });
};

// Verificar se há filtros ativos
const hasActiveFilters = computed(() => {
    return filterEspecie.value.length > 0 || filterFinalidade.value || filterPropriedadeNome.value || 
           filterProdutorNome.value || filterQuantidadeMin.value || filterQuantidadeMax.value;
});

// Severity para Tag de espécie
const getEspecieSeverity = (especie: string) => {
    const severities: Record<string, 'success' | 'info' | 'warning' | 'danger' | 'secondary' | 'contrast'> = {
        'Suínos': 'danger',
        'Suíno': 'danger',
        'Caprinos': 'warning',
        'Caprino': 'warning',
        'Bovinos': 'success',
        'Bovino': 'success',
        'Ovino': 'info',
        'Aves': 'contrast',
        'Equino': 'secondary',
        'Bubalino': 'secondary',
        'Outros': 'secondary',
    };
    return severities[especie] || 'secondary';
};

// Formatação de data/hora
const formatDateTime = (dateString: string) => {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZone: 'America/Sao_Paulo',
    });
};
</script>

<template>
    <MainLayout>
        <Head title="Rebanhos" />

        <ConfirmDialog />

        <div class="mb-4">
            <div>
                <h1 class="text-3xl font-bold text-900 mb-2">Rebanhos</h1>
                <p class="text-600">Gestão de rebanhos cadastrados</p>
            </div>
            <Link href="/rebanhos/create">
                <Button label="Cadastrar Rebanho" icon="pi pi-plus" />
            </Link>
        </div>

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
                        <label for="filterEspecie" class="block mb-2">Espécie(s)</label>
                        <MultiSelect
                            id="filterEspecie"
                            v-model="filterEspecie"
                            :options="especiesOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Selecione uma ou mais espécies"
                            :maxSelectedLabels="2"
                            selectedItemsLabel="{0} espécies selecionadas"
                            :disabled="isSearching"
                            display="chip"
                            class="w-full"
                        />
                        <small class="text-500 block mt-1">Você pode selecionar múltiplas espécies</small>
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <label for="filterFinalidade" class="block mb-2">Finalidade</label>
                        <Dropdown
                            id="filterFinalidade"
                            v-model="filterFinalidade"
                            :options="finalidadesOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Selecione a finalidade"
                            :showClear="true"
                            :disabled="isSearching"
                            class="w-full"
                        />
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <label for="filterPropriedadeNome" class="block mb-2">Nome da Propriedade</label>
                        <InputText
                            id="filterPropriedadeNome"
                            v-model="filterPropriedadeNome"
                            placeholder="Buscar por propriedade..."
                            @keyup.enter="applyFilters"
                            :disabled="isSearching"
                            class="w-full"
                        />
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <label for="filterProdutorNome" class="block mb-2">Nome do Produtor</label>
                        <InputText
                            id="filterProdutorNome"
                            v-model="filterProdutorNome"
                            placeholder="Buscar por produtor..."
                            @keyup.enter="applyFilters"
                            :disabled="isSearching"
                            class="w-full"
                        />
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <label for="filterQuantidadeMin" class="block mb-2">Quantidade Mínima</label>
                        <InputNumber
                            id="filterQuantidadeMin"
                            v-model="filterQuantidadeMin"
                            placeholder="0"
                            :min="0"
                            :disabled="isSearching"
                            class="w-full"
                        />
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <label for="filterQuantidadeMax" class="block mb-2">Quantidade Máxima</label>
                        <InputNumber
                            id="filterQuantidadeMax"
                            v-model="filterQuantidadeMax"
                            placeholder="0"
                            :min="0"
                            :disabled="isSearching"
                            class="w-full"
                        />
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-4">
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

        <Card class="mb-5">
            <template #title>
                <div class="flex justify-content-between align-items-center">
                    <span>Lista de Rebanhos</span>
                    <span class="text-sm text-600">
                        {{ rebanhos.total }} {{ rebanhos.total === 1 ? 'registro' : 'registros' }} encontrado{{ rebanhos.total === 1 ? '' : 's' }}
                    </span>
                </div>
            </template>
            <template #content>
                <DataTable
                    :value="rebanhos.data"
                    stripedRows
                    responsiveLayout="scroll"
                    :loading="isSearching"
                    showGridlines
                    :sortField="sortField"
                    :sortOrder="sortOrder"
                    @sort="onSort"
                    tableStyle="min-width: 75rem"
                >
                    <Column field="especie" header="Espécie" sortable style="width: 150px;">
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.especie" :severity="getEspecieSeverity(slotProps.data.especie)" />
                        </template>
                    </Column>
                    <Column field="propriedade.nome" header="Propriedade" sortable style="width: 200px;">
                        <template #body="slotProps">
                            <Link :href="`/propriedades/${slotProps.data.propriedade.id}`" class="text-primary no-underline hover:underline">
                                {{ slotProps.data.propriedade.nome }}
                            </Link>
                        </template>
                    </Column>
                    <Column field="propriedade.produtor_rural.nome" header="Produtor" sortable style="width: 200px;">
                        <template #body="slotProps">
                            <Link :href="`/produtores-rurais/${slotProps.data.propriedade.produtor_rural.id}`" class="text-primary no-underline hover:underline">
                                {{ slotProps.data.propriedade.produtor_rural.nome }}
                            </Link>
                        </template>
                    </Column>
                    <Column field="quantidade" header="Quantidade" sortable style="width: 120px;">
                        <template #body="slotProps">
                            {{ slotProps.data.quantidade.toLocaleString('pt-BR') }}
                        </template>
                    </Column>
                    <Column field="finalidade" header="Finalidade" sortable style="width: 140px;" />
                    <Column field="data_atualizacao" header="Atualização" sortable style="width: 160px;">
                        <template #body="slotProps">
                            {{ formatDateTime(slotProps.data.data_atualizacao) }}
                        </template>
                    </Column>
                    <Column header="Ações" style="width: 180px;">
                        <template #body="slotProps">
                            <div class="action-buttons">
                                <Link :href="`/rebanhos/${slotProps.data.id}`">
                                    <Button
                                        icon="pi pi-eye"
                                        severity="info"
                                        size="small"
                                        v-tooltip.top="'Visualizar'"
                                        text
                                        rounded
                                    />
                                </Link>
                                <Link :href="`/rebanhos/${slotProps.data.id}/edit`">
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
                                    @click="deleteRebanho(slotProps.data.id, slotProps.data.especie)"
                                />
                            </div>
                        </template>
                    </Column>
                    <template #empty>
                        <div class="text-center py-6">
                            <i class="pi pi-flag text-6xl text-300 mb-4"></i>
                            <p class="text-600">Nenhum rebanho encontrado</p>
                        </div>
                    </template>
                </DataTable>

                <!-- Informações de paginação -->
                <div class="mt-3">
                    <span class="text-sm text-600">
                        Exibindo <strong>{{ rebanhos.from }}</strong> a <strong>{{ rebanhos.to }}</strong> 
                        de <strong>{{ rebanhos.total }}</strong> rebanhos
                        <span v-if="rebanhos.last_page > 1" class="text-500">
                            (Página {{ rebanhos.current_page }} de {{ rebanhos.last_page }})
                        </span>
                    </span>
                </div>

                <div class="flex justify-content-between align-items-center mt-3">
                    <div class="flex align-items-center gap-2">
                        <label for="perPage" class="text-600">Exibir:</label>
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
                        v-if="rebanhos.last_page > 1"
                        :rows="rebanhos.per_page"
                        :totalRecords="rebanhos.total"
                        :first="(rebanhos.current_page - 1) * rebanhos.per_page"
                        @page="onPageChange"
                        template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink"
                    />
                </div>
            </template>
        </Card>
    </MainLayout>
</template>

<style scoped>
/* 🎨 100% PrimeVue/PrimeFlex - Todos os estilos personalizados foram removidos */
</style>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Paginator from 'primevue/paginator';
import Card from 'primevue/card';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Tag from 'primevue/tag';
import { useConfirm } from 'primevue/useconfirm';
import ConfirmDialog from 'primevue/confirmdialog';

interface ProdutorRural {
    id: string; // UUID
    nome: string;
    cpf_cnpj: string;
    telefone: string;
    email: string;
    endereco: string;
    data_cadastro: string;
    data_cadastro_formatada: string;
    total_propriedades: number;
}

interface PaginationLinks {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    produtores: {
        data: ProdutorRural[];
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
        cpf_cnpj?: string;
        endereco?: string;
        telefone?: string;
        email?: string;
        data_cadastro_inicio?: string;
        data_cadastro_fim?: string;
    };
    per_page?: number;
}

const props = defineProps<Props>();
const confirm = useConfirm();

// Estado de loading para feedback visual
const isSearching = ref(false);

// Estado de ordenação
const sortField = ref('nome');
const sortOrder = ref(1); // 1 = ASC, -1 = DESC

// Itens por página
const perPage = ref(props.per_page || props.produtores.per_page);
const perPageOptions = ref([
    { label: '15 por página', value: 15 },
    { label: '25 por página', value: 25 },
    { label: '50 por página', value: 50 },
    { label: '100 por página', value: 100 },
]);

// Filtros
const filterNome = ref(props.filters.nome || '');
const filterCpfCnpj = ref(props.filters.cpf_cnpj || '');
const filterEndereco = ref(props.filters.endereco || '');
const filterTelefone = ref(props.filters.telefone || '');
const filterEmail = ref(props.filters.email || '');
const filterDataCadastroInicio = ref<Date | null>(props.filters.data_cadastro_inicio ? new Date(props.filters.data_cadastro_inicio) : null);
const filterDataCadastroFim = ref<Date | null>(props.filters.data_cadastro_fim ? new Date(props.filters.data_cadastro_fim) : null);

// Timer para debounce
let debounceTimer: number | null = null;

// Função auxiliar para formatar data para Y-m-d
const formatDateToYmd = (date: Date | null): string | undefined => {
    if (!date) return undefined;
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
};

// Função de busca otimizada com debounce
const performSearch = () => {
    isSearching.value = true;
    
    router.get('/produtores-rurais', {
        nome: filterNome.value,
        cpf_cnpj: filterCpfCnpj.value,
        endereco: filterEndereco.value,
        telefone: filterTelefone.value,
        email: filterEmail.value,
        data_cadastro_inicio: formatDateToYmd(filterDataCadastroInicio.value),
        data_cadastro_fim: formatDateToYmd(filterDataCadastroFim.value),
        sort_field: sortField.value,
        sort_direction: sortOrder.value === 1 ? 'asc' : 'desc',
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['produtores'], // Apenas recarrega os dados de produtores
        onFinish: () => {
            isSearching.value = false;
        },
    });
};

// Debounce search - aguarda 500ms após o usuário parar de digitar
const debouncedSearch = () => {
    // Limpa o timer anterior se existir
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    
    // Cria novo timer
    debounceTimer = window.setTimeout(() => {
        performSearch();
    }, 500); // 500ms de delay
};

// Watchers para busca em tempo real
watch(filterNome, () => {
    debouncedSearch();
});

watch(filterCpfCnpj, () => {
    debouncedSearch();
});

watch(filterEndereco, () => {
    debouncedSearch();
});

watch(filterTelefone, () => {
    debouncedSearch();
});

watch(filterEmail, () => {
    debouncedSearch();
});

watch(filterDataCadastroInicio, () => {
    debouncedSearch();
});

watch(filterDataCadastroFim, () => {
    debouncedSearch();
});

// Aplicar filtros manualmente (caso usuário clique no botão)
const applyFilters = () => {
    // Cancela qualquer busca pendente
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    performSearch();
};

// Limpar filtros
const clearFilters = () => {
    // Cancela qualquer busca pendente
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    
    filterNome.value = '';
    filterCpfCnpj.value = '';
    filterEndereco.value = '';
    filterTelefone.value = '';
    filterEmail.value = '';
    filterDataCadastroInicio.value = null;
    filterDataCadastroFim.value = null;
    
    router.get('/produtores-rurais', {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['produtores'],
        onFinish: () => {
            isSearching.value = false;
        },
    });
};

// Paginação
const onPageChange = (event: any) => {
    // Cancela qualquer busca pendente
    if (debounceTimer !== null) {
        clearTimeout(debounceTimer);
    }
    
    router.get('/produtores-rurais', {
        page: event.page + 1,
        nome: filterNome.value,
        cpf_cnpj: filterCpfCnpj.value,
        endereco: filterEndereco.value,
        telefone: filterTelefone.value,
        email: filterEmail.value,
        data_cadastro_inicio: formatDateToYmd(filterDataCadastroInicio.value),
        data_cadastro_fim: formatDateToYmd(filterDataCadastroFim.value),
        sort_field: sortField.value,
        sort_direction: sortOrder.value === 1 ? 'asc' : 'desc',
        per_page: perPage.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['produtores'],
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

// Deletar produtor
const deleteProdutor = (id: number, nome: string) => {
    confirm.require({
        message: `Tem certeza que deseja excluir o produtor "${nome}"?`,
        header: 'Confirmar Exclusão',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Sim, excluir',
        rejectLabel: 'Cancelar',
        acceptClass: 'p-button-danger',
        accept: () => {
            // Toast é gerenciado automaticamente pelo MainLayout via flash messages
            router.delete(`/produtores-rurais/${id}`);
        },
    });
};

// Verificar se há filtros ativos
const hasActiveFilters = computed(() => {
    return filterNome.value || filterCpfCnpj.value || filterEndereco.value || filterTelefone.value || filterEmail.value;
});
</script>

<template>
    <MainLayout>
        <Head title="Produtores Rurais" />

        <ConfirmDialog />

        <div class="mb-4">
            <div>
                <h1 class="text-3xl font-bold text-900">Produtores Rurais</h1>
                <p class="text-sm text-600 mt-2">Gestão de produtores rurais cadastrados</p>
            </div>
            <Link href="/produtores-rurais/create">
                <Button label="Cadastrar Produtor" icon="pi pi-plus" />
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
                        <label for="filterNome">Nome</label>
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
                        <label for="filterCpfCnpj">CPF/CNPJ</label>
                        <InputText
                            id="filterCpfCnpj"
                            v-model="filterCpfCnpj"
                            placeholder="Buscar por CPF/CNPJ..."
                            @keyup.enter="applyFilters"
                            :disabled="isSearching"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterEndereco">Endereço</label>
                        <InputText
                            id="filterEndereco"
                            v-model="filterEndereco"
                            placeholder="Buscar por endereço..."
                            @keyup.enter="applyFilters"
                            :disabled="isSearching"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterTelefone">Telefone</label>
                        <InputText
                            id="filterTelefone"
                            v-model="filterTelefone"
                            placeholder="Buscar por telefone..."
                            @keyup.enter="applyFilters"
                            :disabled="isSearching"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterEmail">E-mail</label>
                        <InputText
                            id="filterEmail"
                            v-model="filterEmail"
                            placeholder="Buscar por e-mail..."
                            @keyup.enter="applyFilters"
                            :disabled="isSearching"
                            class="w-full"
                        />
                        </div>
                    </div>

                    <!-- Filtros de Data -->
                    <div class="col-12 md:col-6 lg:col-4">
                        <div class="field">
                        <label for="filterDataInicio">Data Cadastro (De)</label>
                        <Calendar
                            id="filterDataInicio"
                            v-model="filterDataCadastroInicio"
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
                        <label for="filterDataFim">Data Cadastro (Até)</label>
                        <Calendar
                            id="filterDataFim"
                            v-model="filterDataCadastroFim"
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
                    <span>Lista de Produtores</span>
                    <span class="text-sm text-600">
                        {{ produtores.total }} {{ produtores.total === 1 ? 'registro' : 'registros' }} encontrado{{ produtores.total === 1 ? '' : 's' }}
                    </span>
                </div>
            </template>
            <template #content>
                <DataTable
                    :value="produtores.data"
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
                    <Column field="cpf_cnpj" header="CPF/CNPJ" style="width: 180px;" sortable />
                    <Column field="endereco" header="Endereço" sortable />
                    <Column field="telefone" header="Telefone" style="width: 150px;" sortable />
                    <Column field="email" header="E-mail" sortable />
                    <Column field="data_cadastro_formatada" header="Data Cadastro" style="width: 140px;" sortable />
                    <Column field="total_propriedades" header="Propriedades" style="width: 120px;" bodyClass="text-center" sortable>
                        <template #body="slotProps">
                            <Tag :value="slotProps.data.total_propriedades.toString()" severity="info" />
                        </template>
                    </Column>
                    <Column header="Ações" style="width: 180px;">
                        <template #body="slotProps">
                            <div class="flex gap-2 justify-content-center">
                                <Link :href="`/produtores-rurais/${slotProps.data.id}`">
                                    <Button
                                        icon="pi pi-eye"
                                        severity="info"
                                        size="small"
                                        v-tooltip.top="'Visualizar'"
                                        text
                                        rounded
                                    />
                                </Link>
                                <Link :href="`/produtores-rurais/${slotProps.data.id}/edit`">
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
                                    @click="deleteProdutor(slotProps.data.id, slotProps.data.nome)"
                                />
                            </div>
                        </template>
                    </Column>
                    <template #empty>
                        <div class="text-center py-6">
                            <i class="pi pi-users text-6xl text-300 mb-4"></i>
                            <p>Nenhum produtor encontrado</p>
                        </div>
                    </template>
                </DataTable>

                <!-- Informações de paginação -->
                <div class="mt-3">
                    <span class="text-sm text-600">
                        Exibindo <strong>{{ produtores.from }}</strong> a <strong>{{ produtores.to }}</strong> 
                        de <strong>{{ produtores.total }}</strong> produtores
                        <span v-if="produtores.last_page > 1" class="text-sm text-600">
                            (Página {{ produtores.current_page }} de {{ produtores.last_page }})
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
                        v-if="produtores.last_page > 1"
                        :rows="produtores.per_page"
                        :totalRecords="produtores.total"
                        :first="(produtores.current_page - 1) * produtores.per_page"
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

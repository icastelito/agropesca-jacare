<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dropdown from 'primevue/dropdown';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';

interface PropriedadePorMunicipio {
    municipio: string;
    uf: string;
    total: number;
}

interface AnimaisPorEspecie {
    especie: string;
    total_animais: number;
}

interface HectaresPorCultura {
    nome_cultura: string;
    total_hectares: number;
}

interface ProdutorRural {
    id: number;
    nome: string;
}

interface Props {
    produtores: ProdutorRural[];
    propriedades_municipio?: PropriedadePorMunicipio[];
    animais_especie?: AnimaisPorEspecie[];
    hectares_cultura?: HectaresPorCultura[];
}

const props = defineProps<Props>();
const toast = useToast();

// Dados dos relatórios (inicializados com os dados do controller ou arrays vazios)
const propriedadesPorMunicipio = ref<PropriedadePorMunicipio[]>(props.propriedades_municipio || []);
const animaisPorEspecie = ref<AnimaisPorEspecie[]>(props.animais_especie || []);
const hectaresPorCultura = ref<HectaresPorCultura[]>(props.hectares_cultura || []);
const loading = ref(false);

// Produtor selecionado para PDF
const produtorSelecionado = ref<number | null>(null);

// Carregar dados dos relatórios
const carregarRelatorios = async () => {
    loading.value = true;
    try {
        // Propriedades por município
        const resProp = await axios.get('/relatorios/propriedades-por-municipio');
        propriedadesPorMunicipio.value = resProp.data.data || resProp.data;

        // Animais por espécie
        const resAnimais = await axios.get('/relatorios/animais-por-especie');
        animaisPorEspecie.value = resAnimais.data.data || resAnimais.data;

        // Hectares por cultura
        const resHectares = await axios.get('/relatorios/hectares-por-cultura');
        hectaresPorCultura.value = resHectares.data.data || resHectares.data;

        toast.add({
            severity: 'success',
            summary: 'Sucesso',
            detail: 'Relatórios atualizados',
            life: 3000,
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Erro',
            detail: 'Erro ao carregar relatórios',
            life: 3000,
        });
    } finally {
        loading.value = false;
    }
};

// Exportar propriedades para Excel
const exportarPropriedadesExcel = () => {
    window.location.href = '/exportar/propriedades/excel';
    toast.add({
        severity: 'success',
        summary: 'Exportação',
        detail: 'Download iniciado',
        life: 3000,
    });
};

// Exportar rebanhos para PDF
const exportarRebanhosPdf = () => {
    if (!produtorSelecionado.value) {
        toast.add({
            severity: 'warn',
            summary: 'Atenção',
            detail: 'Selecione um produtor para exportar',
            life: 3000,
        });
        return;
    }
    window.location.href = `/exportar/rebanhos/pdf/${produtorSelecionado.value}`;
    toast.add({
        severity: 'success',
        summary: 'Exportação',
        detail: 'Download iniciado',
        life: 3000,
    });
};



// Calcular totais
const totalPropriedades = () => {
    if (!propriedadesPorMunicipio.value || propriedadesPorMunicipio.value.length === 0) return 0;
    return propriedadesPorMunicipio.value.reduce((acc, item) => acc + (Number(item.total) || 0), 0);
};

const totalAnimais = () => {
    if (!animaisPorEspecie.value || animaisPorEspecie.value.length === 0) return 0;
    return animaisPorEspecie.value.reduce((acc, item) => acc + (Number(item.total_animais) || 0), 0);
};

const totalHectares = () => {
    if (!hectaresPorCultura.value || hectaresPorCultura.value.length === 0) return 0;
    return hectaresPorCultura.value.reduce((acc, item) => acc + (Number(item.total_hectares) || 0), 0);
};

</script>

<template>
    <MainLayout>
        <Head title="Relatórios" />

        <div class="flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="text-3xl font-bold text-900 m-0">Relatórios</h1>
                <p class="text-600 mt-2 mb-0">Visualize estatísticas e exporte dados do sistema</p>
            </div>
            <Button
                label="Atualizar Dados"
                icon="pi pi-refresh"
                @click="carregarRelatorios"
                :loading="loading"
                severity="secondary"
            />
        </div>

        <!-- Cards de Resumo -->
        <div class="grid mb-4">
            <div class="col-12 md:col-6 lg:col-4">
                <Card class="text-center">
                    <template #content>
                        <div class="flex align-items-center justify-content-center border-circle mb-3" style="width: 4rem; height: 4rem; background-color: #dbeafe; margin-left: auto; margin-right: auto;">
                            <i class="pi pi-map-marker text-2xl" style="color: #3b82f6;"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-900 m-0 mb-2">{{ totalPropriedades() }}</h3>
                        <p class="text-600 m-0">Total de Propriedades</p>
                    </template>
                </Card>
            </div>

            <div class="col-12 md:col-6 lg:col-4">
                <Card class="text-center">
                    <template #content>
                        <div class="flex align-items-center justify-content-center border-circle mb-3" style="width: 4rem; height: 4rem; background-color: #fce7f3; margin-left: auto; margin-right: auto;">
                            <i class="pi pi-flag text-2xl" style="color: #ec4899;"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-900 m-0 mb-2">{{ totalAnimais() }}</h3>
                        <p class="text-600 m-0">Total de Animais</p>
                    </template>
                </Card>
            </div>

            <div class="col-12 md:col-6 lg:col-4">
                <Card class="text-center">
                    <template #content>
                        <div class="flex align-items-center justify-content-center border-circle mb-3" style="width: 4rem; height: 4rem; background-color: #d1fae5; margin-left: auto; margin-right: auto;">
                            <i class="pi pi-th-large text-2xl" style="color: #10b981;"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-900 m-0 mb-2">{{ (totalHectares() || 0).toFixed(2) }} ha</h3>
                        <p class="text-600 m-0">Total de Hectares</p>
                    </template>
                </Card>
            </div>
        </div>

        <!-- Relatório: Propriedades por Município -->
        <Card class="mb-4">
            <template #title>
                <div class="flex justify-content-between align-items-center">
                    <span>Propriedades por Município</span>
                </div>
            </template>
            <template #content>
                <DataTable
                    :value="propriedadesPorMunicipio"
                    :loading="loading"
                    stripedRows
                    responsiveLayout="scroll"
                    showGridlines
                >
                    <Column field="municipio" header="Município" />
                    <Column field="uf" header="UF" style="width: 100px;" />
                    <Column field="total" header="Total de Propriedades" style="width: 200px;" bodyClass="text-center">
                        <template #body="slotProps">
                            <span severity="info">{{ slotProps.data.total }}</span>
                        </template>
                    </Column>
                    <template #empty>
                        <div class="text-center py-4">
                            <p>Nenhum dado disponível</p>
                        </div>
                    </template>
                </DataTable>
            </template>
        </Card>

        <!-- Relatório: Animais por Espécie -->
        <Card class="mb-4">
            <template #title>
                <div class="flex justify-content-between align-items-center">
                    <span>Animais por Espécie</span>
                </div>
            </template>
            <template #content>
                <DataTable
                    :value="animaisPorEspecie"
                    :loading="loading"
                    stripedRows
                    responsiveLayout="scroll"
                    showGridlines
                >
                    <Column field="especie" header="Espécie" />
                    <Column field="total_animais" header="Total de Animais" style="width: 200px;" bodyClass="text-center">
                        <template #body="slotProps">
                            <span severity="info">{{ slotProps.data.total_animais }}</span>
                        </template>
                    </Column>
                    <template #empty>
                        <div class="text-center py-4">
                            <p>Nenhum dado disponível</p>
                        </div>
                    </template>
                </DataTable>
            </template>
        </Card>

        <!-- Relatório: Hectares por Cultura -->
        <Card class="mb-4">
            <template #title>
                <div class="flex justify-content-between align-items-center">
                    <span>Hectares por Cultura</span>
                </div>
            </template>
            <template #content>
                <DataTable
                    :value="hectaresPorCultura"
                    :loading="loading"
                    stripedRows
                    responsiveLayout="scroll"
                    showGridlines
                >
                    <Column field="nome_cultura" header="Cultura" />
                    <Column field="total_hectares" header="Total de Hectares" style="width: 200px;" bodyClass="text-right">
                        <template #body="slotProps">
                            {{ Number(slotProps.data.total_hectares || 0).toFixed(2) }} ha
                        </template>
                    </Column>
                    <template #empty>
                        <div class="text-center py-4">
                            <p>Nenhum dado disponível</p>
                        </div>
                    </template>
                </DataTable>
            </template>
        </Card>

        <!-- Exportações -->
        <Card class="mb-4">
            <template #title>
                <span>Exportações</span>
            </template>
            <template #content>
                <div class="flex flex-column gap-4">
                    <div class="flex justify-content-between align-items-start gap-4 p-4 border-1 surface-border border-round surface-50">
                        <div class="flex gap-3 align-items-start flex-1">
                            <i class="pi pi-file-excel" style="font-size: 2rem; color: #10b981;"></i>
                            <div>
                                <h4>Exportar Propriedades</h4>
                                <p>Baixe a lista completa de propriedades em formato Excel</p>
                            </div>
                        </div>
                        <Button
                            label="Baixar Excel"
                            icon="pi pi-download"
                            @click="exportarPropriedadesExcel"
                            severity="success"
                        />
                    </div>

                    <div class="flex justify-content-between align-items-start gap-4 p-4 border-1 surface-border border-round surface-50">
                        <div class="flex gap-3 align-items-start flex-1">
                            <i class="pi pi-file-pdf" style="font-size: 2rem; color: #ef4444;"></i>
                            <div>
                                <h4>Exportar Rebanhos por Produtor</h4>
                                <p>Selecione um produtor e baixe o relatório de rebanhos em PDF</p>
                                <Dropdown
                                    v-model="produtorSelecionado"
                                    :options="produtores"
                                    optionLabel="nome"
                                    optionValue="id"
                                    placeholder="Selecione um produtor"
                                    :filter="true"
                                    filterPlaceholder="Buscar produtor"
                                    class="w-full mt-2"
                                    emptyMessage="Nenhum produtor encontrado"
                                />
                            </div>
                        </div>
                        <div class="flex gap-2">
                         
                            <Button
                                label="Baixar PDF"
                                icon="pi pi-download"
                                @click="exportarRebanhosPdf"
                                severity="danger"
                            />
                        </div>
                    </div>
                </div>
            </template>
        </Card>
    </MainLayout>
</template>

<style scoped>
/*  100% PrimeVue/PrimeFlex - Todos os estilos personalizados foram removidos */
</style>

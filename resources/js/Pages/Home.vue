<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';

interface Stats {
    total_produtores: number;
    total_propriedades: number;
    total_unidades: number;
    total_rebanhos: number;
    total_animais: number;
    total_hectares: number;
}

interface Props {
    stats?: Stats;
}

const props = defineProps<Props>();

// Dados de exemplo caso não venham do backend
const stats = {
    total_produtores: props.stats?.total_produtores || 0,
    total_propriedades: props.stats?.total_propriedades || 0,
    total_unidades: props.stats?.total_unidades || 0,
    total_rebanhos: props.stats?.total_rebanhos || 0,
    total_animais: props.stats?.total_animais || 0,
    total_hectares: Number(props.stats?.total_hectares || 0),
};
</script>

<template>
    <MainLayout>
        <Head title="Início" />

        <div class="text-center mb-5">
            <h1 class="text-5xl font-bold text-900 mb-2">Bem-vindo ao Sistema Agropesca Jacaré</h1>
            <p class="text-xl text-600">Sistema de Gestão Agropecuária de Jacaré dos Homens - AL</p>
        </div>

        <!-- Cards de Estatísticas -->
        <div class="grid">
            <div class="col-12 md:col-6 lg:col-4">
                <Card>
                    <template #content>
                        <div class="flex align-items-center gap-3">
                            <div class="flex align-items-center justify-content-center border-round" style="width: 3.5rem; height: 3.5rem; background-color: #dbeafe;">
                                <i class="pi pi-users text-4xl" style="color: #3b82f6;"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-3xl font-bold text-900">{{ stats.total_produtores }}</div>
                                <div class="text-600 text-sm mt-1">Produtores Rurais</div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="col-12 md:col-6 lg:col-4">
                <Card>
                    <template #content>
                        <div class="flex align-items-center gap-3">
                            <div class="flex align-items-center justify-content-center border-round" style="width: 3.5rem; height: 3.5rem; background-color: #dbeafe;">
                                <i class="pi pi-map-marker text-4xl" style="color: #3b82f6;"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-3xl font-bold text-900">{{ stats.total_propriedades }}</div>
                                <div class="text-600 text-sm mt-1">Propriedades</div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="col-12 md:col-6 lg:col-4">
                <Card>
                    <template #content>
                        <div class="flex align-items-center gap-3">
                            <div class="flex align-items-center justify-content-center border-round" style="width: 3.5rem; height: 3.5rem; background-color: #d1fae5;">
                                <i class="pi pi-th-large text-4xl" style="color: #10b981;"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-3xl font-bold text-900">{{ stats.total_unidades }}</div>
                                <div class="text-600 text-sm mt-1">Unidades de Produção</div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="col-12 md:col-6 lg:col-4">
                <Card>
                    <template #content>
                        <div class="flex align-items-center gap-3">
                            <div class="flex align-items-center justify-content-center border-round" style="width: 3.5rem; height: 3.5rem; background-color: #fce7f3;">
                                <i class="pi pi-flag text-4xl" style="color: #ec4899;"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-3xl font-bold text-900">{{ stats.total_rebanhos }}</div>
                                <div class="text-600 text-sm mt-1">Rebanhos</div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="col-12 md:col-6 lg:col-4">
                <Card>
                    <template #content>
                        <div class="flex align-items-center gap-3">
                            <div class="flex align-items-center justify-content-center border-round" style="width: 3.5rem; height: 3.5rem; background-color: #ffedd5;">
                                <i class="pi pi-chart-bar text-4xl" style="color: #f59e0b;"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-3xl font-bold text-900">{{ stats.total_animais }}</div>
                                <div class="text-600 text-sm mt-1">Total de Animais</div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>

            <div class="col-12 md:col-6 lg:col-4">
                <Card>
                    <template #content>
                        <div class="flex align-items-center gap-3">
                            <div class="flex align-items-center justify-content-center border-round" style="width: 3.5rem; height: 3.5rem; background-color: #d1fae5;">
                                <i class="pi pi-globe text-4xl" style="color: #10b981;"></i>
                            </div>
                            <div class="flex-1">
                                <div class="text-3xl font-bold text-900">{{ Number(stats.total_hectares).toFixed(2) }}</div>
                                <div class="text-600 text-sm mt-1">Hectares Totais</div>
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <!-- Ações Rápidas -->
        <Card class="mt-4">
            <template #title>
                <span>Ações Rápidas</span>
            </template>
            <template #content>
                <div class="grid">
                    <div class="col-12 md:col-6 lg:col-4">
                        <Link href="/produtores-rurais/create">
                            <Button label="Cadastrar Produtor" icon="pi pi-user-plus" class="w-full" severity="info" />
                        </Link>
                    </div>
                    <div class="col-12 md:col-6 lg:col-4">
                        <Link href="/propriedades/create">
                            <Button label="Cadastrar Propriedade" icon="pi pi-map" class="w-full" severity="success" />
                        </Link>
                    </div>
                    <div class="col-12 md:col-6 lg:col-4">
                        <Link href="/unidades-producao/create">
                            <Button label="Cadastrar Unidade" icon="pi pi-plus-circle" class="w-full" severity="help" />
                        </Link>
                    </div>
                    <div class="col-12 md:col-6 lg:col-4">
                        <Link href="/rebanhos/create">
                            <Button label="Cadastrar Rebanho" icon="pi pi-bookmark" class="w-full" severity="warning" />
                        </Link>
                    </div>
                    <div class="col-12 md:col-6 lg:col-4">
                        <Link href="/relatorios">
                            <Button label="Ver Relatórios" icon="pi pi-chart-line" class="w-full" />
                        </Link>
                    </div>
                    <div class="col-12 md:col-6 lg:col-4">
                        <Link href="/produtores-rurais">
                            <Button label="Listar Produtores" icon="pi pi-list" class="w-full" severity="secondary" />
                        </Link>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Informações do Sistema -->
        <Card class="mt-4">
            <template #title>
                <span>Sobre o Sistema</span>
            </template>
            <template #content>
                <p class="text-700 line-height-3 mb-3">
                    O Sistema Agropesca Jacaré foi desenvolvido para gerenciar informações de produtores rurais,
                    propriedades, unidades de produção agrícola e rebanhos do município de Jacaré dos Homens - AL.
                </p>
                <div class="flex flex-column gap-2">
                    <div class="flex align-items-center gap-2">
                        <i class="pi pi-check-circle text-green-500"></i>
                        <span class="text-700">Gestão completa de produtores e propriedades</span>
                    </div>
                    <div class="flex align-items-center gap-2">
                        <i class="pi pi-check-circle text-green-500"></i>
                        <span class="text-700">Controle de unidades de produção (Laranja Pera, Melancia, Goiaba)</span>
                    </div>
                    <div class="flex align-items-center gap-2">
                        <i class="pi pi-check-circle text-green-500"></i>
                        <span class="text-700">Monitoramento de rebanhos (Suínos, Caprinos, Bovinos)</span>
                    </div>
                    <div class="flex align-items-center gap-2">
                        <i class="pi pi-check-circle text-green-500"></i>
                        <span class="text-700">Relatórios detalhados e exportações (Excel e PDF)</span>
                    </div>
                </div>
            </template>
        </Card>
    </MainLayout>
</template>

<style scoped>
/*  100% PrimeVue/PrimeFlex - Todos os estilos personalizados foram removidos */
</style>

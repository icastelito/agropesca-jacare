<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';

interface ProdutorRural {
    id: number;
    nome: string;
    cpf_cnpj: string;
    telefone: string;
}

interface UnidadeProducao {
    id: number;
    nome_cultura: string;
    area_total_ha: number;
    coordenadas_geograficas: string;
}

interface Rebanho {
    id: number;
    especie: string;
    quantidade: number;
    peso_medio_kg: number;
}

interface Propriedade {
    id: number;
    nome: string;
    municipio: string;
    uf: string;
    inscricao_estadual: string;
    area_total: number;
    area_total_formatada: string;
    produtor_rural: ProdutorRural;
    unidades_producao: UnidadeProducao[];
    rebanhos: Rebanho[];
    total_unidades: number;
    total_rebanhos: number;
    created_at: string;
}

interface Props {
    propriedade: {
        data: Propriedade;
    };
}

const props = defineProps<Props>();
const propriedadeData = props.propriedade.data;
</script>

<template>
    <MainLayout>
        <Head :title="`Propriedade: ${propriedadeData.nome}`" />

        <div class="page-header">
            <div>
                <h1 class="page-title">{{ propriedadeData.nome }}</h1>
                <p class="page-subtitle">{{ propriedadeData.municipio }} - {{ propriedadeData.uf }}</p>
            </div>
            <div class="header-actions">
                <Link :href="`/propriedades/${propriedadeData.id}/edit`">
                    <Button label="Editar" icon="pi pi-pencil" severity="warning" />
                </Link>
                <Link href="/propriedades">
                    <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" />
                </Link>
            </div>
        </div>

        <!-- Informações Gerais -->
        <Card class="mb-3">
            <template #title>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-map-marker"></i>
                    <span>Informações da Propriedade</span>
                </div>
            </template>
            <template #content>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Município</label>
                        <p>{{ propriedadeData.municipio }}</p>
                    </div>
                    <div class="info-item">
                        <label>UF</label>
                        <p>{{ propriedadeData.uf }}</p>
                    </div>
                    <div class="info-item">
                        <label>Inscrição Estadual</label>
                        <p>{{ propriedadeData.inscricao_estadual || 'Não informado' }}</p>
                    </div>
                    <div class="info-item">
                        <label>Área Total</label>
                        <p>{{ propriedadeData.area_total_formatada }}</p>
                    </div>
                    <div class="info-item">
                        <label>Data de Cadastro</label>
                        <p>{{ propriedadeData.created_at }}</p>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Informações do Produtor -->
        <Card class="mb-3">
            <template #title>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-user"></i>
                    <span>Produtor Rural</span>
                </div>
            </template>
            <template #content>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Nome</label>
                        <p>
                            <Link :href="`/produtores-rurais/${propriedadeData.produtor_rural.id}`" class="link">
                                {{ propriedadeData.produtor_rural.nome }}
                            </Link>
                        </p>
                    </div>
                    <div class="info-item">
                        <label>CPF/CNPJ</label>
                        <p>{{ propriedadeData.produtor_rural.cpf_cnpj }}</p>
                    </div>
                    <div class="info-item">
                        <label>Telefone</label>
                        <p>{{ propriedadeData.produtor_rural.telefone }}</p>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Unidades de Produção -->
        <Card class="mb-3">
            <template #title>
                <div class="flex justify-content-between align-items-center">
                    <div class="flex align-items-center gap-2">
                        <i class="pi pi-th-large"></i>
                        <span>Unidades de Produção</span>
                    </div>
                    <div class="flex gap-3 align-items-center">
                        <span class="badge-total">
                            {{ propriedadeData.total_unidades || propriedadeData.unidades_producao?.length || 0 }} 
                            {{ ((propriedadeData.total_unidades || propriedadeData.unidades_producao?.length || 0) === 1) ? 'unidade' : 'unidades' }}
                        </span>
                        <Link href="/unidades-producao/create">
                            <Button label="Adicionar" icon="pi pi-plus" size="small" />
                        </Link>
                    </div>
                </div>
            </template>
            <template #content>
                <DataTable
                    v-if="propriedadeData.unidades_producao && propriedadeData.unidades_producao.length > 0"
                    :value="propriedadeData.unidades_producao"
                    stripedRows
                    responsiveLayout="scroll"
                    showGridlines
                >
                    <Column field="nome_cultura" header="Cultura" />
                    <Column field="area_total_ha" header="Área Total" style="width: 150px;">
                        <template #body="slotProps">
                            {{ Number(slotProps.data.area_total_ha || 0).toFixed(2) }} ha
                        </template>
                    </Column>
                    <Column field="coordenadas_geograficas" header="Coordenadas" style="width: 200px;" />
                    <Column header="Ações" style="width: 120px;">
                        <template #body="slotProps">
                            <div class="action-buttons">
                                <Link :href="`/unidades-producao/${slotProps.data.id}`">
                                    <Button
                                        icon="pi pi-eye"
                                        severity="info"
                                        size="small"
                                        v-tooltip.top="'Visualizar'"
                                        text
                                        rounded
                                    />
                                </Link>
                                <Link :href="`/unidades-producao/${slotProps.data.id}/edit`">
                                    <Button
                                        icon="pi pi-pencil"
                                        severity="warning"
                                        size="small"
                                        v-tooltip.top="'Editar'"
                                        text
                                        rounded
                                    />
                                </Link>
                            </div>
                        </template>
                    </Column>
                </DataTable>
                <div v-else class="empty-state">
                    <i class="pi pi-th-large" style="font-size: 3rem; color: #cbd5e1;"></i>
                    <p>Nenhuma unidade de produção cadastrada</p>
                    <Link href="/unidades-producao/create">
                        <Button label="Cadastrar Unidade" icon="pi pi-plus" class="mt-3" />
                    </Link>
                </div>
            </template>
        </Card>

        <!-- Rebanhos -->
        <Card>
            <template #title>
                <div class="flex justify-content-between align-items-center">
                    <div class="flex align-items-center gap-2">
                        <i class="pi pi-flag"></i>
                        <span>Rebanhos</span>
                    </div>
                    <div class="flex gap-3 align-items-center">
                        <span class="badge-total">
                            {{ propriedadeData.total_rebanhos || propriedadeData.rebanhos?.length || 0 }} 
                            {{ ((propriedadeData.total_rebanhos || propriedadeData.rebanhos?.length || 0) === 1) ? 'rebanho' : 'rebanhos' }}
                        </span>
                        <Link href="/rebanhos/create">
                            <Button label="Adicionar" icon="pi pi-plus" size="small" />
                        </Link>
                    </div>
                </div>
            </template>
            <template #content>
                <DataTable
                    v-if="propriedadeData.rebanhos && propriedadeData.rebanhos.length > 0"
                    :value="propriedadeData.rebanhos"
                    stripedRows
                    responsiveLayout="scroll"
                    showGridlines
                >
                    <Column field="especie" header="Espécie" />
                    <Column field="quantidade" header="Quantidade" style="width: 150px;" bodyClass="text-center">
                        <template #body="slotProps">
                            <span class="badge">{{ slotProps.data.quantidade }}</span>
                        </template>
                    </Column>
                    <Column field="peso_medio_kg" header="Peso Médio" style="width: 150px;">
                        <template #body="slotProps">
                            {{ Number(slotProps.data.peso_medio_kg).toFixed(2) }} kg
                        </template>
                    </Column>
                    <Column header="Peso Total" style="width: 150px;">
                        <template #body="slotProps">
                            {{ (Number(slotProps.data.quantidade) * Number(slotProps.data.peso_medio_kg)).toFixed(2) }} kg
                        </template>
                    </Column>
                    <Column header="Ações" style="width: 120px;">
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
                            </div>
                        </template>
                    </Column>
                </DataTable>
                <div v-else class="empty-state">
                    <i class="pi pi-flag" style="font-size: 3rem; color: #cbd5e1;"></i>
                    <p>Nenhum rebanho cadastrado</p>
                    <Link href="/rebanhos/create">
                        <Button label="Cadastrar Rebanho" icon="pi pi-plus" class="mt-3" />
                    </Link>
                </div>
            </template>
        </Card>
    </MainLayout>
</template>

<style scoped>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.page-subtitle {
    color: #6b7280;
    margin: 0.5rem 0 0 0;
}

.header-actions {
    display: flex;
    gap: 0.75rem;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    flex-direction: column;
}

.info-item label {
    font-weight: 600;
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.info-item p {
    color: #1f2937;
    font-size: 1rem;
    margin: 0;
}

.link {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 500;
}

.link:hover {
    text-decoration: underline;
}

.badge-total {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    background-color: #3b82f6;
    color: white;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    padding: 0.25rem 0.75rem;
    background-color: #3b82f6;
    color: white;
    border-radius: 1rem;
    font-size: 0.875rem;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    gap: 0.25rem;
    justify-content: center;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-state p {
    margin-top: 1rem;
    color: #6b7280;
    font-size: 1.125rem;
}

.mb-3 {
    margin-bottom: 1.5rem;
}

.mt-3 {
    margin-top: 1.5rem;
}
</style>

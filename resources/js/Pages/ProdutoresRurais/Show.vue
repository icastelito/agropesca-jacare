<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Divider from 'primevue/divider';

interface Propriedade {
    id: number;
    nome: string;
    municipio: string;
    uf: string;
    area_total_formatada: string;
    total_unidades: number;
    total_rebanhos: number;
}

interface ProdutorRural {
    id: number;
    nome: string;
    cpf_cnpj: string;
    telefone: string;
    email: string;
    endereco: string;
    data_cadastro_formatada: string;
    propriedades: Propriedade[];
    total_propriedades: number;
}

interface Props {
    produtor: {
        data: ProdutorRural;
    };
}

const props = defineProps<Props>();
const produtorData = props.produtor.data;
</script>

<template>
    <MainLayout>
        <Head :title="`Produtor: ${produtorData.nome}`" />

        <div class="page-header">
            <div>
                <h1 class="page-title">{{ produtorData.nome }}</h1>
                <p class="page-subtitle">Detalhes do produtor rural</p>
            </div>
            <div class="header-actions">
                <Link :href="`/produtores-rurais/${produtorData.id}/edit`">
                    <Button label="Editar" icon="pi pi-pencil" severity="warning" />
                </Link>
                <Link href="/produtores-rurais">
                    <Button label="Voltar" icon="pi pi-arrow-left" severity="secondary" />
                </Link>
            </div>
        </div>

        <!-- Informações do Produtor -->
        <Card class="mb-3">
            <template #title>
                <div class="flex align-items-center gap-2">
                    <i class="pi pi-user"></i>
                    <span>Informações Pessoais</span>
                </div>
            </template>
            <template #content>
                <div class="info-grid">
                    <div class="info-item">
                        <label>CPF/CNPJ</label>
                        <p>{{ produtorData.cpf_cnpj }}</p>
                    </div>
                    <div class="info-item">
                        <label>Telefone</label>
                        <p>{{ produtorData.telefone }}</p>
                    </div>
                    <div class="info-item">
                        <label>E-mail</label>
                        <p>{{ produtorData.email }}</p>
                    </div>
                    <div class="info-item">
                        <label>Data de Cadastro</label>
                        <p>{{ produtorData.data_cadastro_formatada }}</p>
                    </div>
                    <div class="info-item col-span-full">
                        <label>Endereço</label>
                        <p>{{ produtorData.endereco }}</p>
                    </div>
                </div>
            </template>
        </Card>

        <!-- Propriedades -->
        <Card>
            <template #title>
                <div class="flex justify-content-between align-items-center">
                    <div class="flex align-items-center gap-2">
                        <i class="pi pi-map-marker"></i>
                        <span>Propriedades</span>
                    </div>
                    <span class="badge-total">
                        {{ produtorData.total_propriedades || produtorData.propriedades.length }} 
                        {{ (produtorData.total_propriedades || produtorData.propriedades.length) === 1 ? 'propriedade' : 'propriedades' }}
                    </span>
                </div>
            </template>
            <template #content>
                <DataTable
                    v-if="produtorData.propriedades && produtorData.propriedades.length > 0"
                    :value="produtorData.propriedades"
                    stripedRows
                    responsiveLayout="scroll"
                    showGridlines
                >
                    <Column field="nome" header="Nome da Propriedade" />
                    <Column field="municipio" header="Município" style="width: 200px;" />
                    <Column field="uf" header="UF" style="width: 80px;" />
                    <Column field="area_total_formatada" header="Área Total" style="width: 150px;" />
                    <Column field="total_unidades" header="Unidades" style="width: 100px;" bodyClass="text-center">
                        <template #body="slotProps">
                            <span class="badge">{{ slotProps.data.total_unidades || 0 }}</span>
                        </template>
                    </Column>
                    <Column field="total_rebanhos" header="Rebanhos" style="width: 100px;" bodyClass="text-center">
                        <template #body="slotProps">
                            <span class="badge">{{ slotProps.data.total_rebanhos || 0 }}</span>
                        </template>
                    </Column>
                    <Column header="Ações" style="width: 120px;">
                        <template #body="slotProps">
                            <div class="action-buttons">
                                <Link :href="`/propriedades/${slotProps.data.id}`">
                                    <Button
                                        icon="pi pi-eye"
                                        severity="info"
                                        size="small"
                                        v-tooltip.top="'Visualizar Propriedade'"
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
                            </div>
                        </template>
                    </Column>
                </DataTable>
                <div v-else class="empty-state">
                    <i class="pi pi-map-marker" style="font-size: 3rem; color: #cbd5e1;"></i>
                    <p>Nenhuma propriedade cadastrada para este produtor</p>
                    <Link href="/propriedades/create">
                        <Button label="Cadastrar Propriedade" icon="pi pi-plus" class="mt-3" />
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

.info-item.col-span-full {
    grid-column: 1 / -1;
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
    min-width: 2rem;
    padding: 0.25rem 0.5rem;
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

<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';

interface ProdutorRural {
    id: string; // UUID
    nome: string;
}

interface Props {
    produtores: ProdutorRural[];
}

defineProps<Props>();

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

// Formulário usando Inertia useForm
const form = useForm({
    nome: '',
    municipio: '',
    uf: '',
    inscricao_estadual: '',
    area_total: null as number | null,
    produtor_id: null as string | null,
    data_cadastro: new Date(),
});

// Submeter formulário
// Nota: Toast é gerenciado automaticamente pelo MainLayout via flash messages
const submit = () => {
    form.post('/propriedades');
};

// Cancelar e voltar
const cancel = () => {
    router.get('/propriedades');
};
</script>

<template>
    <MainLayout>
        <Head title="Cadastrar Propriedade" />

        <div class="mb-4">
            <div>
                <h1 class="text-3xl font-bold text-900">Cadastrar Propriedade</h1>
                <p class="text-sm text-600 mt-2">Preencha os dados para cadastrar uma nova propriedade rural</p>
            </div>
        </div>

        <Card>
            <template #content>
                <form @submit.prevent="submit" class="p-fluid">
                    <div class="grid">
                        <!-- Nome -->
                        <div class="col-12 md:col-6">
                            <div class="field">
                                <label for="nome">Nome da Propriedade <span class="text-red-500">*</span></label>
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
                                <label for="municipio">Município <span class="text-red-500">*</span></label>
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
                        <div class="col-12 md:col-4">
                            <div class="field">
                                <label for="area_total" class="required">Área Total (hectares)</label>
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
                            label="Salvar"
                            icon="pi pi-check"
                            type="submit"
                            :loading="form.processing"
                            :disabled="form.processing"
                        />
                    </div>
                </form>
            </template>
        </Card>
    </MainLayout>
</template>

<style scoped>
/* 🎨 100% PrimeVue/PrimeFlex - Todos os estilos personalizados foram removidos */
</style>

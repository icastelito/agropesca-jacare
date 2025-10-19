<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Card from 'primevue/card';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';

interface Propriedade {
    id: string;
    nome: string;
    produtor_rural: {
        nome: string;
    };
}

interface UnidadeProducao {
    id: string;
    nome_cultura: string;
    area_total_ha: number;
    coordenadas_geograficas: string | null;
    propriedade_id: string;
}

interface Props {
    unidade: UnidadeProducao;
    propriedades: Propriedade[];
    culturas: string[];
}

const props = defineProps<Props>();

// Opções de culturas - APENAS 3 opções permitidas
const culturasOptions = props.culturas.map(cultura => ({
    label: cultura,
    value: cultura,
}));

// Opções de propriedades formatadas
const propriedadesOptions = props.propriedades.map(prop => ({
    id: prop.id,
    label: `${prop.nome} (${prop.produtor_rural.nome})`,
}));

// Formulário usando Inertia useForm
const form = useForm({
    nome_cultura: props.unidade.nome_cultura,
    area_total_ha: props.unidade.area_total_ha,
    coordenadas_geograficas: props.unidade.coordenadas_geograficas || '',
    propriedade_id: props.unidade.propriedade_id,
});

// Submeter formulário
// Nota: Toast é gerenciado automaticamente pelo MainLayout via flash messages
const submit = () => {
    form.put(`/unidades-producao/${props.unidade.id}`);
};

// Cancelar e voltar
const cancel = () => {
    router.get('/unidades-producao');
};
</script>

<template>
    <MainLayout>
        <Head title="Editar Unidade de Produção" />

        <div class="mb-4">
            <div>
                <h1 class="text-3xl font-bold text-900">Editar Unidade de Produção</h1>
                <p class="text-sm text-600 mt-2">Atualize os dados da unidade de {{ unidade.nome_cultura }}</p>
            </div>
        </div>

        <Card>
            <template #content>
                <form @submit.prevent="submit" class="p-fluid">
                    <div class="grid">
                        <!-- Cultura -->
                        <div class="col-12 md:col-6">
                            <div class="field">
                                <label for="nome_cultura" >Cultura</label>
                                <Dropdown
                                    id="nome_cultura"
                                    v-model="form.nome_cultura"
                                    :options="culturasOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Selecione a cultura"
                                    :class="{ 'p-invalid': form.errors.nome_cultura }"
                                    emptyMessage="Nenhuma cultura disponível"
                                />
                                <small v-if="form.errors.nome_cultura" class="p-error">
                                    {{ form.errors.nome_cultura }}
                                </small>
                                <small class="help-text">
                                    Culturas disponíveis: {{ culturas.join(', ') }}
                                </small>
                            </div>
                        </div>

                        <!-- Propriedade -->
                        <div class="col-12 md:col-6">
                            <div class="field">
                                <label for="propriedade_id">Propriedade <span class="text-red-500">*</span></label>
                                <Dropdown
                                    id="propriedade_id"
                                    v-model="form.propriedade_id"
                                    :options="propriedadesOptions"
                                    optionLabel="label"
                                    optionValue="id"
                                    placeholder="Selecione a propriedade"
                                    :class="{ 'p-invalid': form.errors.propriedade_id }"
                                    :filter="true"
                                    filterPlaceholder="Buscar propriedade"
                                    emptyMessage="Nenhuma propriedade encontrada"
                                />
                                <small v-if="form.errors.propriedade_id" class="p-error">
                                    {{ form.errors.propriedade_id }}
                                </small>
                            </div>
                        </div>

                        <!-- Área Total -->
                        <div class="col-12 md:col-6">
                            <div class="field">
                                <label for="area_total_ha" >Área Total (hectares)</label>
                                <InputNumber
                                    id="area_total_ha"
                                    v-model="form.area_total_ha"
                                    :class="{ 'p-invalid': form.errors.area_total_ha }"
                                    placeholder="0.00"
                                    :minFractionDigits="2"
                                    :maxFractionDigits="2"
                                    :min="0"
                                    suffix=" ha"
                                />
                                <small v-if="form.errors.area_total_ha" class="p-error">
                                    {{ form.errors.area_total_ha }}
                                </small>
                            </div>
                        </div>

                        <!-- Coordenadas Geográficas -->
                        <div class="col-12 md:col-6">
                            <div class="field">
                                <label for="coordenadas_geograficas">Coordenadas Geográficas</label>
                                <Textarea
                                    id="coordenadas_geograficas"
                                    v-model="form.coordenadas_geograficas"
                                    :class="{ 'p-invalid': form.errors.coordenadas_geograficas }"
                                    placeholder="Ex: -9.123456, -36.789012"
                                    rows="3"
                                />
                                <small v-if="form.errors.coordenadas_geograficas" class="p-error">
                                    {{ form.errors.coordenadas_geograficas }}
                                </small>
                                <small class="help-text">
                                    Formato: latitude, longitude
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
    </MainLayout>
</template>

<style scoped>
/*  100% PrimeVue/PrimeFlex - Todos os estilos personalizados foram removidos */
</style>

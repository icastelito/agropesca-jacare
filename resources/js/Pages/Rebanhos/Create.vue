<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Card from 'primevue/card';
import InputNumber from 'primevue/inputnumber';
import Dropdown from 'primevue/dropdown';
import Calendar from 'primevue/calendar';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';

interface Propriedade {
    id: number;
    nome: string;
    produtor_rural: {
        nome: string;
    };
}

interface Props {
    propriedades: Propriedade[];
    especies: string[];
}

const props = defineProps<Props>();
const toast = useToast();

// Opções de espécies - APENAS 3 opções permitidas
const especiesOptions = props.especies.map(especie => ({
    label: especie,
    value: especie,
}));

// Opções de propriedades formatadas
const propriedadesOptions = props.propriedades.map(prop => ({
    id: prop.id,
    label: `${prop.nome} (${prop.produtor_rural.nome})`,
}));

// Formulário usando Inertia useForm
const form = useForm({
    especie: '',
    quantidade: null as number | null,
    finalidade: '',
    data_atualizacao: new Date(),
    propriedade_id: null as number | null,
});

// Submeter formulário
const submit = () => {
    form.post('/rebanhos', {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: 'Rebanho cadastrado com sucesso',
                life: 3000,
            });
        },
        onError: () => {
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: 'Verifique os campos e tente novamente',
                life: 3000,
            });
        },
    });
};

// Cancelar e voltar
const cancel = () => {
    router.get('/rebanhos');
};
</script>

<template>
    <MainLayout>
        <Head title="Cadastrar Rebanho" />

        <div class="mb-4">
            <div>
                <h1 class="text-3xl font-bold text-900">Cadastrar Rebanho</h1>
                <p class="text-sm text-600 mt-2">Preencha os dados para cadastrar um novo rebanho</p>
            </div>
        </div>

        <Card>
            <template #content>
                <form @submit.prevent="submit" class="p-fluid">
                    <div class="grid">
                        <!-- Espécie -->
                        <div class="col-12 md:col-6">
                            <div class="field">
                                <label for="especie">Espécie <span class="text-red-500">*</span></label>
                                <Dropdown
                                    id="especie"
                                    v-model="form.especie"
                                    :options="especiesOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    placeholder="Selecione a espécie"
                                    :class="{ 'p-invalid': form.errors.especie }"
                                    emptyMessage="Nenhuma espécie disponível"
                                />
                                <small v-if="form.errors.especie" class="p-error">
                                    {{ form.errors.especie }}
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

                        <!-- Quantidade -->
                        <div class="col-12 md:col-4">
                            <div class="field">
                                <label for="quantidade">Quantidade  de Animais</label>
                                <InputNumber
                                    id="quantidade"
                                    v-model="form.quantidade"
                                    :class="{ 'p-invalid': form.errors.quantidade }"
                                    placeholder="0"
                                    :min="1"
                                    
                                />
                                <small v-if="form.errors.quantidade" class="p-error">
                                    {{ form.errors.quantidade }}
                                </small>
                            </div>
                        </div>

                        <!-- Data de Atualização -->
                        <div class="col-12 md:col-4">
                            <div class="field">
                                <label for="data_atualizacao" >Data de Atualização</label>
                                <Calendar
                                    id="data_atualizacao"
                                    v-model="form.data_atualizacao"
                                    :class="{ 'p-invalid': form.errors.data_atualizacao }"
                                    dateFormat="dd/mm/yy"
                                    :showIcon="true"
                                    :maxDate="new Date()"
                                />
                                <small v-if="form.errors.data_atualizacao" class="p-error">
                                    {{ form.errors.data_atualizacao }}
                                </small>
                            </div>
                        </div>

                        <!-- Finalidade -->
                        <div class="col-12 md:col-4">
                            <div class="field">
                                <label for="">Finalidade <span class="text-red-500">*</span></label>
                                <Textarea
                                    id="finalidade"
                                    v-model="form.finalidade"
                                    :class="{ 'p-invalid': form.errors.finalidade }"
                                    placeholder="Ex: Produção de leite, corte, reprodução..."
                                    rows="3"
                                />
                                <small v-if="form.errors.finalidade" class="p-error">
                                    {{ form.errors.finalidade }}
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
/*  100% PrimeVue/PrimeFlex - Todos os estilos personalizados foram removidos */
</style>

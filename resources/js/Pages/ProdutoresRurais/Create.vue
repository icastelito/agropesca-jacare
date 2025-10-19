<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Calendar from 'primevue/calendar';
import Button from 'primevue/button';

// Formulário usando Inertia useForm
const form = useForm({
    nome: '',
    cpf_cnpj: '',
    telefone: '',
    email: '',
    endereco: '',
    data_cadastro: new Date(),
});

// Submeter formulário
// Nota: Toast é gerenciado automaticamente pelo MainLayout via flash messages
const submit = () => {
    form.post('/produtores-rurais');
};

// Cancelar e voltar
const cancel = () => {
    router.get('/produtores-rurais');
};
</script>

<template>
    <MainLayout>
        <Head title="Cadastrar Produtor Rural" />

        <div class="mb-4">
            <div>
                <h1 class="text-3xl font-bold text-900">Cadastrar Produtor Rural</h1>
                <p class="text-sm text-600 mt-2">Preencha os dados para cadastrar um novo produtor</p>
            </div>
        </div>

        <Card>
            <template #content>
                <form @submit.prevent="submit" class="p-fluid">
                    <div class="grid">
                        <!-- Nome -->
                        <div class="col-12 md:col-6">
                            <div class="field">
                                <label for="nome">Nome  Completo</label>
                                <InputText
                                    id="nome"
                                    v-model="form.nome"
                                    :class="{ 'p-invalid': form.errors.nome }"
                                    placeholder="Digite o nome completo"
                                />
                                <small v-if="form.errors.nome" class="p-error">
                                    {{ form.errors.nome }}
                                </small>
                            </div>
                        </div>

                        <!-- CPF/CNPJ -->
                        <div class="col-12 md:col-6">
                            <div class="field">
                                <label for="cpf_cnpj" >CPF/CNPJ</label>
                                <InputText
                                    id="cpf_cnpj"
                                    v-model="form.cpf_cnpj"
                                    :class="{ 'p-invalid': form.errors.cpf_cnpj }"
                                    placeholder="000.000.000-00 ou 00.000.000/0000-00"
                                />
                                <small v-if="form.errors.cpf_cnpj" class="p-error">
                                    {{ form.errors.cpf_cnpj }}
                                </small>
                            </div>
                        </div>

                        <!-- Telefone -->
                        <div class="col-12 md:col-6">
                            <div class="field">
                                <label for="telefone">Telefone</label>
                                <InputText
                                    id="telefone"
                                    v-model="form.telefone"
                                    :class="{ 'p-invalid': form.errors.telefone }"
                                    placeholder="(00) 00000-0000"
                                />
                                <small v-if="form.errors.telefone" class="p-error">
                                    {{ form.errors.telefone }}
                                </small>
                            </div>
                        </div>

                        <!-- E-mail -->
                        <div class="col-12 md:col-6">
                            <div class="field">
                                <label for="email">E-mail</label>
                                <InputText
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    :class="{ 'p-invalid': form.errors.email }"
                                    placeholder="email@exemplo.com"
                                />
                                <small v-if="form.errors.email" class="p-error">
                                    {{ form.errors.email }}
                                </small>
                            </div>
                        </div>

                        <!-- Endereço -->
                        <div class="col-12 md:col-8">
                            <div class="field">
                                <label for="endereco">Endereço</label>
                                <InputText
                                    id="endereco"
                                    v-model="form.endereco"
                                    :class="{ 'p-invalid': form.errors.endereco }"
                                    placeholder="Rua, número, bairro, cidade - UF"
                                />
                                <small v-if="form.errors.endereco" class="p-error">
                                    {{ form.errors.endereco }}
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
/*  100% PrimeVue/PrimeFlex - Todos os estilos personalizados foram removidos */
</style>

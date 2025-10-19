<template>
    <MainLayout>
        <Card >
            <template #title>
                <div class="mb-4">
                    <div>
                        <h1 class="text-3xl font-bold text-900 m-0">Logs de Queries</h1>
                        <p class="text-600 mt-2 mb-0">Monitoramento de queries do banco de dados</p>
                    </div>
                </div>
            </template>
            
            <template #content>
                <!-- Debug Info -->
                <div class="mb-3 p-3 surface-100 border-round" v-if="true">
                    <p class="text-sm m-0"><strong>Debug:</strong></p>
                    <p class="text-sm m-0">Tipo selecionado: {{ selectedType }}</p>
                    <p class="text-sm m-0">Data selecionada: {{ formatDateLong(selectedDate) }}</p>
                    <p class="text-sm m-0">Datas disponíveis: {{ props.availableDates?.length || 0 }} itens</p>
                    <p class="text-sm m-0">Logs carregados: {{ props.logs?.length || 0 }} itens</p>
                </div>
                
                <!-- Filtros -->
                <div class="mb-4">
                    <div class="grid">
                        <div class="col-12 md:col-6">
                            <label class="block mb-2 font-semibold">Tipo de Log</label>
                            <Dropdown
                                v-model="selectedType"
                                @change="handleTypeChange"
                                :options="logTypes"
                                optionLabel="label"
                                optionValue="value"
                                class="w-full"
                            />
                        </div>
                        
                        <div class="col-12 md:col-6">
                            <label class="block mb-2 font-semibold">Data</label>
                            <Dropdown
                                v-model="selectedDate"
                                @change="handleDateChange"
                                :options="dateOptions"
                                optionLabel="label"
                                optionValue="value"
                                placeholder="Selecione uma data"
                                class="w-full"
                            />
                        </div>
                    </div>
                    
                    <div class="flex gap-2 mt-3">
                        <Button
                            label="Atualizar"
                            icon="pi pi-refresh"
                            @click="refreshLogs"
                            severity="info"
                        />
                        <Button
                            label="Limpar Logs"
                            icon="pi pi-trash"
                            @click="clearLogs"
                            severity="danger"
                            :disabled="!props.logs || props.logs.length === 0"
                        />
                    </div>
                </div>

                <!-- Lista de Logs -->
                <div class="mt-4">
                    <div v-if="props.logs && props.logs.length > 0">
                        <div class="mb-3 p-3 surface-100 border-round">
                            <p class="text-sm m-0 font-semibold">
                                Total de registros: {{ props.logs.length }} logs
                            </p>
                        </div>
                        
                        <VirtualScroller 
                            :items="props.logs" 
                            :itemSize="100"
                            class="border-1 surface-border border-round"
                            style="height: 600px"
                        >
                            <template #item="{ item, index }">
                                <div
                                    class="p-3 border-bottom-1 surface-border"
                                    :class="getLogClass(item.time)"
                                >
                                    <div class="flex flex-column gap-1">
                                        <span class="text-600 text-sm">{{ item.timestamp }}</span>
                                        <span class="font-bold">{{ item.time }}</span>
                                        <span class="text-700 font-mono text-sm">{{ item.sql }}</span>
                                    </div>
                                </div>
                            </template>
                        </VirtualScroller>
                    </div>

                    <div v-else class="text-center py-6">
                        <i class="pi pi-inbox" style="font-size: 3rem; color: #94a3b8;"></i>
                        <p>Nenhum log encontrado</p>
                    </div>
                </div>
            </template>
        </Card>
    </MainLayout>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import VirtualScroller from 'primevue/virtualscroller';

const props = defineProps({
    type: String,
    date: String,
    logs: Array,
    statistics: Object,
    availableDates: Array,
});

const selectedType = ref(props.type || 'queries');
const selectedDate = ref(props.date || '');

// Formatar data de YYYY-MM-DD para DD/MM/YYYY
function formatDate(dateString) {
    if (!dateString) return '';
    
    const [year, month, day] = dateString.split('-');
    return `${day}/${month}/${year}`;
}

// Formatar data para exibição mais amigável (ex: 19 de outubro de 2025)
function formatDateLong(dateString) {
    if (!dateString) return '';
    
    const date = new Date(dateString + 'T00:00:00');
    const options = { day: 'numeric', month: 'long', year: 'numeric' };
    return date.toLocaleDateString('pt-BR', options);
}

// Criar opções formatadas para o dropdown
const dateOptions = computed(() => {
    if (!props.availableDates || props.availableDates.length === 0) {
        return [];
    }
    
    return props.availableDates.map(date => ({
        label: formatDate(date),
        value: date
    }));
});

// Watch para atualizar valores quando props mudarem
watch(() => props.type, (newType) => {
    if (newType) selectedType.value = newType;
});

watch(() => props.date, (newDate) => {
    selectedDate.value = newDate || '';
});

// Watch para garantir que selectedDate seja válido quando availableDates mudar
watch(() => props.availableDates, (newDates) => {
    if (!newDates || newDates.length === 0) {
        selectedDate.value = '';
        return;
    }
    
    // Se a data selecionada não existe mais na lista, usa a primeira disponível
    if (selectedDate.value && !newDates.includes(selectedDate.value)) {
        selectedDate.value = newDates[0];
    }
}, { immediate: true });

const logTypes = [
    { label: 'Todas as Queries', value: 'queries' },
    { label: 'Queries Lentas (>100ms)', value: 'slow_queries' }
];

function handleTypeChange() {
    console.log('Mudando tipo para:', selectedType.value);
    // Não envia a data, deixa o backend escolher a primeira disponível para o novo tipo
    router.get('/logs', {
        type: selectedType.value,
    }, {
        preserveState: false,
        preserveScroll: false,
    });
}

function handleDateChange() {
    console.log('Mudando data para:', selectedDate.value, 'Tipo:', selectedType.value);
    router.get('/logs', {
        type: selectedType.value,
        date: selectedDate.value,
    }, {
        preserveState: false,
        preserveScroll: false,
    });
}

function refreshLogs() {
    router.reload();
}

function clearLogs() {
    if (confirm('Tem certeza que deseja limpar os logs desta data?')) {
        router.delete('/logs/clear', {
            data: {
                type: selectedType.value,
                date: selectedDate.value,
            },
            preserveState: false,
            preserveScroll: false,
        });
    }
}

function getLogClass(time) {
    const timeStr = String(time).replace('ms', '').trim();
    const ms = parseFloat(timeStr);
    
    if (ms > 500) return 'log-slow';
    if (ms > 100) return 'log-moderate';
    return 'log-fast';
}
</script>

<style scoped>
/*  100% PrimeVue/PrimeFlex - Todos os estilos personalizados foram removidos */
</style>

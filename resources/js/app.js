import './bootstrap';
import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import Tooltip from 'primevue/tooltip';

// Importar CSS do PrimeVue (tema carregado via link no app.blade.php)
import 'primevue/resources/primevue.min.css';
import 'primeicons/primeicons.css';
import 'primeflex/primeflex.css';

// Configuração de locale em português para PrimeVue
const ptBR = {
    startsWith: 'Começa com',
    contains: 'Contém',
    notContains: 'Não contém',
    endsWith: 'Termina com',
    equals: 'Igual',
    notEquals: 'Diferente',
    noFilter: 'Sem filtro',
    lt: 'Menor que',
    lte: 'Menor ou igual a',
    gt: 'Maior que',
    gte: 'Maior ou igual a',
    dateIs: 'Data é',
    dateIsNot: 'Data não é',
    dateBefore: 'Data antes de',
    dateAfter: 'Data depois de',
    clear: 'Limpar',
    apply: 'Aplicar',
    matchAll: 'Combinar todos',
    matchAny: 'Combinar qualquer',
    addRule: 'Adicionar regra',
    removeRule: 'Remover regra',
    accept: 'Sim',
    reject: 'Não',
    choose: 'Escolher',
    upload: 'Upload',
    cancel: 'Cancelar',
    dayNames: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado'],
    dayNamesShort: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
    dayNamesMin: ['D', 'S', 'T', 'Q', 'Q', 'S', 'S'],
    monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
    monthNamesShort: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
    today: 'Hoje',
    weekHeader: 'Sm',
    firstDayOfWeek: 0,
    dateFormat: 'dd/mm/yy',
    weak: 'Fraco',
    medium: 'Médio',
    strong: 'Forte',
    passwordPrompt: 'Digite uma senha',
    emptyFilterMessage: 'Nenhum resultado encontrado',
    emptyMessage: 'Nenhuma opção disponível'
};

createInertiaApp({
    title: (title) => `${title} - Agropesca Jacaré`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        
        app.use(plugin);
        app.use(PrimeVue, { 
            ripple: true,
            locale: ptBR
        });
        app.use(ToastService);
        app.use(ConfirmationService);
        app.directive('tooltip', Tooltip);
        
        app.mount(el);
    },
    progress: {
        color: '#3b82f6',
        showSpinner: true,
    },
});
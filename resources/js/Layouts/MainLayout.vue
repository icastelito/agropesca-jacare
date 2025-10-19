<script setup lang="ts">
import { ref, watch, onMounted, nextTick, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import Menubar from 'primevue/menubar';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import Dialog from 'primevue/dialog';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import { useToast } from 'primevue/usetoast';
import { usePrimeVue } from 'primevue/config';

const logoImg = '/images/logo.png';

const page = usePage();
const toast = useToast();
const primevue = usePrimeVue();

// Dialog de configuração de tema
const showThemeDialog = ref(false);

// Controle de tema completo
const isDarkTheme = ref(true);
const selectedThemeFamily = ref('aura'); // aura, lara, md, mdc
const selectedColor = ref('purple'); // purple, blue, green, etc.

// Tema temporário (enquanto usuário escolhe no dialog)
const tempIsDarkTheme = ref(true);
const tempThemeFamily = ref('aura');
const tempThemeColor = ref('purple');

// Opções de tema
const themeFamilies = [
    { label: 'Aura', value: 'aura' },
    { label: 'Lara', value: 'lara' },
    { label: 'Material Design', value: 'md' },
    { label: 'Material Design Compact', value: 'mdc' }
];

// Opções de cores temporárias (baseadas na família temporária)
const tempColorOptions = computed(() => {
    if (tempThemeFamily.value === 'md' || tempThemeFamily.value === 'mdc') {
        return [
            { label: 'Indigo', value: 'indigo' },
            { label: 'Deep Purple', value: 'deeppurple' }
        ];
    }
    
    return [
        { label: 'Purple', value: 'purple' },
        { label: 'Blue', value: 'blue' },
        { label: 'Indigo', value: 'indigo' },
        { label: 'Teal', value: 'teal' },
        { label: 'Green', value: 'green' },
        { label: 'Amber', value: 'amber' },
        { label: 'Cyan', value: 'cyan' },
        { label: 'Pink', value: 'pink' }
    ];
});

// Watch para validar cor quando família mudar
watch(tempThemeFamily, () => {
    const availableColors = tempColorOptions.value.map(opt => opt.value);
    
    // Se a cor atual não existe na nova família
    if (!availableColors.includes(tempThemeColor.value)) {
        // Desselecionar cor
        tempThemeColor.value = '';
        
        // Avisar usuário
        toast.add({
            severity: 'warn',
            summary: 'Cor Incompatível',
            detail: 'A cor selecionada não está disponível nesta família. Por favor, escolha uma nova cor.',
            life: 5000
        } as any);
    }
});

// Construir nome do tema com base nas escolhas
const currentThemeName = computed(() => {
    const mode = isDarkTheme.value ? 'dark' : 'light';
    return `${selectedThemeFamily.value}-${mode}-${selectedColor.value}`;
});

// Construir nome do tema temporário
const tempThemeName = computed(() => {
    const mode = tempIsDarkTheme.value ? 'dark' : 'light';
    return `${tempThemeFamily.value}-${mode}-${tempThemeColor.value}`;
});

// Abrir dialog de configuração
const openThemeDialog = () => {
    // Copiar valores atuais para temporários
    tempIsDarkTheme.value = isDarkTheme.value;
    tempThemeFamily.value = selectedThemeFamily.value;
    tempThemeColor.value = selectedColor.value;
    showThemeDialog.value = true;
};

// Aplicar tema escolhido no dialog
const applySelectedTheme = () => {
    // Validar se uma cor foi selecionada
    if (!tempThemeColor.value) {
        toast.add({
            severity: 'error',
            summary: 'Cor Não Selecionada',
            detail: 'Por favor, selecione uma cor antes de aplicar o tema.',
            life: 4000
        } as any);
        return;
    }
    
    const oldTheme = currentThemeName.value;
    
    // Aplicar valores temporários aos definitivos
    isDarkTheme.value = tempIsDarkTheme.value;
    selectedThemeFamily.value = tempThemeFamily.value;
    selectedColor.value = tempThemeColor.value;
    
    const newTheme = currentThemeName.value;
    
    // Fechar dialog
    showThemeDialog.value = false;
    
    // Aplicar tema
    primevue.changeTheme(oldTheme, newTheme, 'theme-link', () => {
        saveThemePreferences();
        // Recarregar página após pequeno delay
        setTimeout(() => {
            window.location.reload();
        }, 300);
    });
};

// Salvar preferências
const saveThemePreferences = () => {
    localStorage.setItem('themeMode', isDarkTheme.value ? 'dark' : 'light');
    localStorage.setItem('themeFamily', selectedThemeFamily.value);
    localStorage.setItem('themeColor', selectedColor.value);
};

// Carregar preferências salvas
const loadSavedTheme = () => {
    const savedMode = localStorage.getItem('themeMode');
    const savedFamily = localStorage.getItem('themeFamily');
    const savedColor = localStorage.getItem('themeColor');
    
    if (savedMode) isDarkTheme.value = savedMode === 'dark';
    if (savedFamily) selectedThemeFamily.value = savedFamily;
    if (savedColor) selectedColor.value = savedColor;
    
    // Aplicar tema salvo SEM recarregar (para evitar loop)
    const savedTheme = currentThemeName.value;
    if (savedTheme !== 'aura-dark-purple') {
        primevue.changeTheme('aura-dark-purple', savedTheme, 'theme-link', () => {
            // Não recarrega aqui, apenas no carregamento inicial
        });
    }
};

// Rastrear última mensagem mostrada para evitar duplicação
const lastFlashMessage = ref<string>('');

// Controle para garantir que o PrimeVue Toast esteja montado antes de adicionar mensagens
const toastReady = ref(false);
// Fila de toasts pendentes até o componente Toast estar pronto
const pendingToasts = ref<Array<{ severity: string; summary: string; detail: string; life?: number }>>([]);
// (removed unused toastComponentRef)

// Menu items
const menuItems = ref([
    {
        label: 'Início',
        icon: 'pi pi-home',
        url: '/home',
    },
    {
        label: 'Dashboard',
        icon: 'pi pi-chart-line',
        url: '/dashboard',
    },
    {
        label: 'Produtores Rurais',
        icon: 'pi pi-users',
        items: [
            {
                label: 'Listar',
                icon: 'pi pi-list',
                url: '/produtores-rurais',
            },
            {
                label: 'Cadastrar',
                icon: 'pi pi-plus',
                url: '/produtores-rurais/create',
            },
        ],
    },
    {
        label: 'Propriedades',
        icon: 'pi pi-map-marker',
        items: [
            {
                label: 'Listar',
                icon: 'pi pi-list',
                url: '/propriedades',
            },
            {
                label: 'Cadastrar',
                icon: 'pi pi-plus',
                url: '/propriedades/create',
            },
        ],
    },
    {
        label: 'Unidades de Produção',
        icon: 'pi pi-th-large',
        items: [
            {
                label: 'Listar',
                icon: 'pi pi-list',
                url: '/unidades-producao',
            },
            {
                label: 'Cadastrar',
                icon: 'pi pi-plus',
                url: '/unidades-producao/create',
            },
        ],
    },
    {
        label: 'Rebanhos',
        icon: 'pi pi-flag',
        items: [
            {
                label: 'Listar',
                icon: 'pi pi-list',
                url: '/rebanhos',
            },
            {
                label: 'Cadastrar',
                icon: 'pi pi-plus',
                url: '/rebanhos/create',
            },
        ],
    },
    {
        label: 'Relatórios',
        icon: 'pi pi-chart-bar',
        url: '/relatorios',
    },
    {
        label: 'Logs',
        icon: 'pi pi-file',
        url: '/logs',
    },
]);

// Estado de logout
const loggingOut = ref(false);

function logout() {
    // confirmação simples
    if (!confirm('Deseja realmente sair do sistema?')) return;

    loggingOut.value = true;
    
    // Usar Inertia router diretamente com método POST
    router.post('/logout', {}, {
        onSuccess: () => {
            toast.add({ 
                severity: 'success', 
                summary: 'Saída', 
                detail: 'Logout efetuado com sucesso', 
                life: 3000 
            } as any);
        },
        onError: (errors: any) => {
            console.error('[MainLayout] erro no logout:', errors);
            toast.add({ 
                severity: 'error', 
                summary: 'Erro', 
                detail: 'Falha ao sair do sistema', 
                life: 4000 
            } as any);
        },
        onFinish: () => {
            loggingOut.value = false;
        }
    });
}

// Watch para mensagens flash
watch(
    () => page.props.flash,
    (flash: any) => {
        // Debug: log do flash recebido
        // eslint-disable-next-line no-console
        console.log('[MainLayout] page.props.flash changed:', flash);

        const currentMessage = flash?.success || flash?.error || '';

        // Só mostrar se houver mensagem e for diferente da última
            if (currentMessage && currentMessage !== lastFlashMessage.value) {
            lastFlashMessage.value = currentMessage;

            // Debug
            // eslint-disable-next-line no-console
            console.log('[MainLayout] exibindo toast:', { success: flash?.success, error: flash?.error });

            if (flash?.success) {
                // Debug: inspecionar serviço de toast e container no DOM
                // eslint-disable-next-line no-console
                console.log('[MainLayout] toast service before add:', toast, 'addExists=', typeof toast.add === 'function');
                try {
                    // eslint-disable-next-line no-console
                    console.log('[MainLayout] .p-toast element before add:', document.querySelector('.p-toast'));
                } catch (e) {
                    // eslint-disable-next-line no-console
                    console.log('[MainLayout] erro ao acessar DOM do .p-toast:', e);
                }

                const msg = { severity: 'success', summary: 'Sucesso', detail: flash.success, life: 3000 };
                if (toastReady.value && typeof toast.add === 'function') {
                    toast.add(msg as any);
                } else {
                    // Enfileira até o Toast estar pronto
                    pendingToasts.value.push(msg);
                    // Debug
                    // eslint-disable-next-line no-console
                    console.log('[MainLayout] Toast não pronto, adicionando à fila pendente');
                }
            }

            if (flash?.error) {
                // Debug: inspecionar serviço de toast antes de adicionar
                // eslint-disable-next-line no-console
                console.log('[MainLayout] toast service before add (error):', toast, 'addExists=', typeof toast.add === 'function');
                const msg = { severity: 'error', summary: 'Erro', detail: flash.error, life: 5000 };
                if (toastReady.value && typeof toast.add === 'function') {
                    toast.add(msg as any);
                } else {
                    pendingToasts.value.push(msg);
                    // eslint-disable-next-line no-console
                    console.log('[MainLayout] Toast não pronto (erro), adicionando à fila pendente');
                }
            }
        }
    },
    { deep: true, immediate: true }
);

// Ao montar, aguardar o próximo tick e marcar o Toast como pronto, então drenar a fila
onMounted(async () => {
    // Carregar tema salvo
    loadSavedTheme();
    
    await nextTick();
    // Se usarmos a ref do componente, podemos verificar que está presente
    toastReady.value = true;
    // eslint-disable-next-line no-console
    console.log('[MainLayout] toastReady -> true, drenando fila pendente:', pendingToasts.value.length);
    while (pendingToasts.value.length > 0) {
        const m = pendingToasts.value.shift();
        try {
            if (m && typeof toast.add === 'function') {
                toast.add(m as any);
            }
        } catch (e) {
            // eslint-disable-next-line no-console
            console.log('[MainLayout] erro ao adicionar toast da fila:', e);
        }
    }
});
</script>

<template>
    <div class="min-h-screen flex flex-column surface-ground">
        
        <header class="surface-0 shadow-2 sticky top-0 z-5">
            <Menubar :model="menuItems">
                <template #start>
                    <Link href="/home" class="flex align-items-center gap-3 no-underline font-semibold text-color p-3 border-round-md hover:surface-100 transition-colors transition-duration-200">
                        <img :src="logoImg" alt="Agropesca Jacaré" style="width: 40px; height: 40px; object-fit: contain;" />
                        <span class="text-xl">Agropesca Jacaré</span>
                    </Link>
                </template>
                <template #end>
                    <div class="flex align-items-center gap-2 pr-2">
                        <!-- Botão de Configuração de Tema -->
                        <Button 
                            icon="pi pi-cog" 
                            class="p-button-text p-button-plain p-button-sm" 
                            @click="openThemeDialog"
                            v-tooltip.bottom="'Configurar Tema'"
                        />
                        
                        <!-- Botão Sair -->
                        <Button 
                            label="Sair" 
                            icon="pi pi-sign-out" 
                            class="p-button-text p-button-plain p-button-sm" 
                            :loading="loggingOut" 
                            @click="logout" 
                        />
                    </div>
                </template>
                <template #item="{ item, props }">
                    <Link v-if="item.url" :href="item.url" class="p-menuitem-link" v-bind="props.action">
                        <span :class="item.icon" />
                        <span class="ml-2">{{ item.label }}</span>
                    </Link>
                    <a v-else class="p-menuitem-link" v-bind="props.action">
                        <span :class="item.icon" />
                        <span class="ml-2">{{ item.label }}</span>
                        <i class="pi pi-chevron-down text-xs ml-auto opacity-70 transition-transform transition-duration-200"></i>
                    </a>
                </template>
            </Menubar>
        </header>

        <main class="flex-1 py-5">
            <div class="px-3" style="max-width: 90vw; margin: 0 auto;">
                <slot />
            </div>
        </main>

        <footer class="surface-0 border-top-1 surface-border py-5 mt-6 text-center">
            <div class="px-3" style="max-width: 90vw; margin: 0 auto;">
                <p class="m-1 text-600">&copy; 2025 Agropesca Jacaré - Sistema de Gestão Agropecuária</p>
                <p class="m-1 text-600 text-sm">Jacaré dos Homens - AL</p>
            </div>
        </footer>

    <!-- Global Toast -->
    <Toast position="top-right" appendTo="body" />
        
        <!-- Global ConfirmDialog -->
        <ConfirmDialog />
        
        <!-- Dialog de Configuração de Tema -->
        <Dialog 
            v-model:visible="showThemeDialog" 
            modal 
            header="Configurar Tema" 
            :style="{ width: '30rem' }"
            :draggable="false"
        >
            <div class="flex flex-column gap-4 p-3">
                <!-- Família do Tema -->
                <div class="flex flex-column gap-2">
                    <label for="theme-family" class="font-semibold">Família do Tema</label>
                    <Dropdown 
                        id="theme-family"
                        v-model="tempThemeFamily" 
                        :options="themeFamilies" 
                        optionLabel="label" 
                        optionValue="value"
                        placeholder="Selecione a família"
                        class="w-full"
                    />
                </div>
                
                <!-- Cor do Tema -->
                <div class="flex flex-column gap-2">
                    <label for="theme-color" class="font-semibold">Cor</label>
                    <Dropdown 
                        id="theme-color"
                        v-model="tempThemeColor" 
                        :options="tempColorOptions" 
                        optionLabel="label" 
                        optionValue="value"
                        placeholder="Selecione a cor"
                        class="w-full"
                    />
                </div>
                
                <!-- Modo Claro/Escuro -->
                <div class="flex flex-column gap-2">
                    <label class="font-semibold">Modo</label>
                    <div class="flex gap-2">
                        <Button 
                            label="Claro" 
                            icon="pi pi-sun" 
                            :outlined="tempIsDarkTheme"
                            :severity="!tempIsDarkTheme ? 'primary' : 'secondary'"
                            class="flex-1"
                            @click="tempIsDarkTheme = false"
                        />
                        <Button 
                            label="Escuro" 
                            icon="pi pi-moon" 
                            :outlined="!tempIsDarkTheme"
                            :severity="tempIsDarkTheme ? 'primary' : 'secondary'"
                            class="flex-1"
                            @click="tempIsDarkTheme = true"
                        />
                    </div>
                </div>
                
                <!-- Preview do Tema -->
                <div class="flex flex-column gap-2">
                    <label class="font-semibold">Preview</label>
                    <div class="p-3 border-round surface-border border-1 text-center">
                        <span class="text-sm">{{ tempThemeName }}</span>
                    </div>
                </div>
            </div>
            
            <template #footer>
                <Button 
                    label="Cancelar" 
                    icon="pi pi-times" 
                    @click="showThemeDialog = false" 
                    severity="secondary"
                    outlined
                />
                <Button 
                    label="Aplicar Tema" 
                    icon="pi pi-check" 
                    @click="applySelectedTheme"
                    severity="success"
                />
            </template>
        </Dialog>
    </div>
</template>

<style scoped>
/* Customizações específicas do tema - usando apenas variáveis CSS do PrimeVue */

/* Efeito hover no chevron do submenu */
:deep(.p-menuitem-link:hover .pi-chevron-down) {
    opacity: 1;
}

/* Rotação do chevron quando menu está ativo */
:deep(.p-menuitem.p-menuitem-active .pi-chevron-down) {
    transform: rotate(180deg);
}

/* Dropdowns mais compactos no header */
:deep(.p-dropdown) {
    font-size: 0.875rem;
}

:deep(.p-dropdown .p-dropdown-label) {
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
}

:deep(.p-dropdown .p-dropdown-trigger) {
    width: 2rem;
}

/* Botões mais compactos */
:deep(.p-button-sm) {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

:deep(.p-button-sm .p-button-icon-only) {
    padding: 0.5rem;
    width: 2.5rem;
}

</style>

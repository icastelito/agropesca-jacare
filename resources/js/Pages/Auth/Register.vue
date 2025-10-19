<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';
import { Link, router } from '@inertiajs/vue3';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import logoImg from '@/images/logo.png';

const name = ref('');
const email = ref('');
const password = ref('');
const password_confirmation = ref('');
const loading = ref(false);
const toast = useToast();

// errors vindos do backend (422) ou mensagem genérica
const errors = reactive({ 
  name: null, 
  email: null, 
  password: null, 
  password_confirmation: null, 
  general: null 
});

async function submit() {
  // Limpar erros anteriores
  errors.name = null;
  errors.email = null;
  errors.password = null;
  errors.password_confirmation = null;
  errors.general = null;
  
  loading.value = true;
  
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    const payload = {
      name: name.value,
      email: email.value,
      password: password.value,
      password_confirmation: password_confirmation.value,
    };
    
    await axios.post('/register', payload, { withCredentials: true });
    toast.add({ 
      severity: 'success', 
      summary: 'Conta criada', 
      detail: 'Conta criada com sucesso! Você será redirecionado para fazer login.', 
      life: 3000 
    });
    
    // Aguardar um pouco para o toast aparecer antes de redirecionar
    setTimeout(() => {
      router.visit('/login');
    }, 1000);
    
  } catch (e) {
    const resp = e && e.response ? e.response : null;
    if (resp?.status === 422 && resp.data?.errors) {
      // Erros de validação específicos
      errors.name = resp.data.errors.name ? resp.data.errors.name.join(' ') : null;
      errors.email = resp.data.errors.email ? resp.data.errors.email.join(' ') : null;
      errors.password = resp.data.errors.password ? resp.data.errors.password.join(' ') : null;
      errors.password_confirmation = resp.data.errors.password_confirmation ? resp.data.errors.password_confirmation.join(' ') : null;
    } else {
      // Erro genérico
      errors.general = resp?.data?.message || 'Erro ao criar conta';
      toast.add({ 
        severity: 'error', 
        summary: 'Erro', 
        detail: errors.general, 
        life: 4000 
      });
    }
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="flex align-items-center justify-content-center min-h-screen p-3 surface-ground">
    <Toast />
    <Card style="width: 100%; max-width: 420px;">
      <template #header>
        <div class="flex gap-3 align-items-center mb-3">
          <img :src="logoImg" alt="Agropesca Jacaré" style="width: 64px; height: 64px; object-fit: contain;" />
          <div>
            <div class="font-semibold text-xl">Criar conta no Agropesca Jacaré</div>
            <div class="text-sm text-600">Preencha os dados abaixo para criar sua conta</div>
          </div>
        </div>
      </template>

      <template #content>
        <form class="p-fluid" @submit.prevent="submit" novalidate autocomplete="on">
          <div class="field mb-3">
            <label for="name">Nome completo</label>
            <InputText 
              id="name" 
              v-model="name" 
              placeholder="Seu nome completo" 
              aria-label="Nome completo" 
              :disabled="loading" 
              autocomplete="name" 
            />
            <small v-if="errors.name" class="p-error">{{ errors.name }}</small>
          </div>

          <div class="field mb-3">
            <label for="email">E-mail</label>
            <InputText 
              id="email" 
              v-model="email" 
              type="email"
              placeholder="seu@exemplo.com" 
              aria-label="E-mail" 
              :disabled="loading" 
              autocomplete="email" 
            />
            <small v-if="errors.email" class="p-error">{{ errors.email }}</small>
          </div>

          <div class="field mb-3">
            <label for="password">Senha</label>
            <Password 
              id="password" 
              v-model="password" 
              :feedback="false" 
              toggleMask 
              placeholder="Sua senha" 
              aria-label="Senha" 
              :disabled="loading" 
              autocomplete="new-password" 
            />
            <small v-if="errors.password" class="p-error">{{ errors.password }}</small>
          </div>

          <div class="field mb-3">
            <label for="password_confirmation">Confirmar senha</label>
            <Password 
              id="password_confirmation" 
              v-model="password_confirmation" 
              :feedback="false" 
              toggleMask 
              placeholder="Confirme sua senha" 
              aria-label="Confirmar senha" 
              :disabled="loading" 
              autocomplete="new-password" 
            />
            <small v-if="errors.password_confirmation" class="p-error">{{ errors.password_confirmation }}</small>
          </div>

          <div class="field mb-3 flex justify-content-center">
            <Link href="/login" class="text-primary no-underline hover:underline">Já tem uma conta? Faça login</Link>
          </div>

          <div v-if="errors.general" class="p-error mb-2">{{ errors.general }}</div>

          <div class="flex justify-content-end gap-2 mt-3">
            <Button label="Criar conta" type="submit" :loading="loading" :disabled="loading" />
          </div>
        </form>
      </template>

    </Card>
  </div>
</template>

<!-- Sem estilos customizados - usando 100% PrimeVue e PrimeFlex -->

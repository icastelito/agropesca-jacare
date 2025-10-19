<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';
import { Link, router } from '@inertiajs/vue3';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import Checkbox from 'primevue/checkbox';
import Button from 'primevue/button';
import Toast from 'primevue/toast';
import { useToast } from 'primevue/usetoast';
import logoImg from '@/images/logo.png';

const email = ref('');
const password = ref('');
const loading = ref(false);
const remember = ref(false);
const toast = useToast();

// errors vindos do backend (422) ou mensagem genérica
const errors = reactive({ email: null, password: null, general: null });

async function submit() {
  errors.email = null;
  errors.password = null;
  errors.general = null;
  loading.value = true;
  try {
    await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
    const payload = { email: email.value, password: password.value, remember: remember.value ? 1 : 0 };
    await axios.post('/login', payload, { withCredentials: true });
    toast.add({ severity: 'success', summary: 'Bem-vindo', detail: 'Login efetuado com sucesso', life: 3000 });
    router.visit('/home');
  } catch (e) {
    const resp = e && e.response ? e.response : null;
    if (resp?.status === 422 && resp.data?.errors) {
      errors.email = resp.data.errors.email ? resp.data.errors.email.join(' ') : null;
      errors.password = resp.data.errors.password ? resp.data.errors.password.join(' ') : null;
    } else {
      errors.general = resp?.data?.message || 'Erro ao efetuar login';
      toast.add({ severity: 'error', summary: 'Erro', detail: errors.general, life: 4000 });
    }
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="flex align-items-center justify-content-center min-h-screen p-3 surface-ground">
    <Toast />
    <Card style="width: 100%; max-width: 400px;">
      <template #header>
        <div class="flex gap-3 align-items-center mb-3">
          <img :src="logoImg" alt="Agropesca Jacaré" style="width: 64px; height: 64px; object-fit: contain;" />
          <div>
            <div class="font-semibold text-xl">Entrar no sistema Agropesca Jacaré</div>
            <div class="text-sm text-600">Acesse sua conta para gerenciar o sistema</div>
          </div>
        </div>
      </template>

      <template #content>
        <form class="p-fluid" @submit.prevent="submit" novalidate autocomplete="on">
        <div class="field mb-3">
          <label for="email">E-mail</label>
          <InputText id="email" v-model="email" placeholder="seu@exemplo.com" aria-label="E-mail" :disabled="loading" autocomplete="email" />
          <small v-if="errors.email" class="p-error">{{ errors.email }}</small>
        </div>

        <div class="field mb-3">
          <label for="password">Senha</label>
          <Password id="password" v-model="password" :feedback="false" toggleMask placeholder="Senha" aria-label="Senha" :disabled="loading" autocomplete="current-password" />
          <small v-if="errors.password" class="p-error">{{ errors.password }}</small>
        </div>

        <div class="field mb-3 flex justify-content-between align-items-center">
          <div class="flex align-items-center gap-2">
            <Checkbox v-model="remember" binary inputId="remember" :disabled="loading" />
            <label for="remember">Lembrar-me</label>
          </div>

          <div>
            <Link href="/register" class="text-primary no-underline hover:underline">Cadastre-se</Link>
          </div>
        </div>

        <div v-if="errors.general" class="p-error mb-2">{{ errors.general }}</div>

        <div class="flex justify-content-end gap-2 mt-3">
          <Button label="Entrar" type="submit" :loading="loading" :disabled="loading" />
        </div>
        </form>
      </template>

    </Card>
  </div>
</template>

<!-- Sem estilos customizados - usando 100% PrimeVue e PrimeFlex -->

<template>
  <div>
    <div class="page-head">
      <div>
        <a-button type="link" style="padding:0" @click="$router.push('/platform/tenants')">← Companies</a-button>
        <h2 style="margin:8px 0 0">{{ tenant?.name || 'Company' }}</h2>
        <p class="muted">{{ tenant?.slug }} · {{ tenant?.status }}</p>
      </div>
      <a-button type="primary" :loading="entering" @click="enterCompany">Enter company</a-button>
    </div>

    <a-row :gutter="16">
      <a-col :span="14">
        <a-card title="Modules" :loading="loading">
          <div class="mod-grid">
            <div v-for="m in catalog" :key="m" class="mod-row">
              <span>{{ labelFor(m) }}</span>
              <a-switch v-model:checked="flags[m]" @change="dirty = true" />
            </div>
          </div>
          <a-button type="primary" :disabled="!dirty" :loading="saving" @click="saveFlags">Save modules</a-button>
        </a-card>
      </a-col>
      <a-col :span="10">
        <a-card title="Admin users" :loading="loading">
          <a-list :data-source="admins" item-layout="horizontal">
            <template #renderItem="{ item }">
              <a-list-item>
                <a-list-item-meta :title="item.username || item.email" :description="item.email" />
              </a-list-item>
            </template>
          </a-list>
          <a-divider />
          <h4>Add admin</h4>
          <a-form layout="vertical" @finish="addAdmin">
            <a-form-item label="First name" name="firstname" :rules="[{ required: true }]">
              <a-input v-model:value="adminForm.firstname" />
            </a-form-item>
            <a-form-item label="Last name" name="lastname" :rules="[{ required: true }]">
              <a-input v-model:value="adminForm.lastname" />
            </a-form-item>
            <a-form-item label="Email" name="email" :rules="[{ required: true, type: 'email' }]">
              <a-input v-model:value="adminForm.email" />
            </a-form-item>
            <a-form-item label="Password" name="password" :rules="[{ required: true, min: 6 }]">
              <a-input-password v-model:value="adminForm.password" />
            </a-form-item>
            <a-button type="primary" html-type="submit" :loading="adding">Create login</a-button>
          </a-form>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { message } from 'ant-design-vue';
import http from '../../lib/http';
import { TOGGLEABLE_MODULES } from '../../config/modules';
import { useAuthStore } from '../../stores/auth';

const route = useRoute();
const router = useRouter();
const entering = ref(false);
const loading = ref(true);
const saving = ref(false);
const adding = ref(false);
const dirty = ref(false);
const tenant = ref(null);
const admins = ref([]);
const catalog = ref([]);
const flags = reactive({});
const adminForm = reactive({ firstname: '', lastname: '', email: '', password: '' });

function labelFor(key) {
  return TOGGLEABLE_MODULES.find(m => m.key === key)?.label || key;
}

async function load() {
  loading.value = true;
  try {
    const data = await http.get(`platform/tenants/${route.params.id}`);
    tenant.value = data.tenant;
    admins.value = data.admins || [];
    catalog.value = data.module_catalog || [];
    for (const k of catalog.value) {
      const map = data.tenant?.module_flags;
      flags[k] = !map || map[k] !== false;
    }
    dirty.value = false;
  } catch (e) {
    message.error(e?.message || 'Failed to load');
  } finally {
    loading.value = false;
  }
}

async function saveFlags() {
  saving.value = true;
  try {
    const payload = {};
    for (const k of catalog.value) payload[k] = !!flags[k];
    await http.put(`platform/tenants/${route.params.id}`, { module_flags: payload });
    dirty.value = false;
    message.success('Modules updated');
    await load();
  } catch (e) {
    message.error(e?.message || 'Save failed');
  } finally {
    saving.value = false;
  }
}

async function enterCompany() {
  entering.value = true;
  try {
    await http.post(`platform/tenants/${route.params.id}/switch`);
    await useAuthStore().reload();
    router.push('/dashboard');
  } catch (e) {
    message.error(e?.message || 'Could not enter company');
  } finally {
    entering.value = false;
  }
}

async function addAdmin() {
  adding.value = true;
  try {
    await http.post(`platform/tenants/${route.params.id}/admins`, { ...adminForm });
    message.success('Admin created');
    adminForm.firstname = '';
    adminForm.lastname = '';
    adminForm.email = '';
    adminForm.password = '';
    await load();
  } catch (e) {
    message.error(e?.message || 'Create failed');
  } finally {
    adding.value = false;
  }
}

onMounted(load);
</script>

<style scoped>
.page-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
.muted { color: #888; margin: 4px 0 0; }
.mod-grid { display: flex; flex-direction: column; gap: 4px; margin-bottom: 16px; max-height: 420px; overflow: auto; }
.mod-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid rgba(0,0,0,.06); }
</style>

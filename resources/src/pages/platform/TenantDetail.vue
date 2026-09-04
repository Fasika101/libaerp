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
      <a-col :xs="24" :lg="10">
        <a-card title="Modules" :loading="loading" style="margin-bottom: 16px">
          <div class="mod-grid">
            <div v-for="m in catalog" :key="m" class="mod-row">
              <span>{{ labelFor(m) }}</span>
              <a-switch v-model:checked="flags[m]" @change="dirty = true" />
            </div>
          </div>
          <a-button type="primary" :disabled="!dirty" :loading="saving" @click="saveFlags">Save modules</a-button>
        </a-card>
      </a-col>
      <a-col :xs="24" :lg="14">
        <a-card title="Company admins" :loading="loading">
          <template #extra>
            <a-button type="primary" size="small" @click="openCreate">Add admin</a-button>
          </template>
          <a-table
            :data-source="admins"
            :columns="adminColumns"
            row-key="id"
            :pagination="false"
            size="small"
          >
            <template #bodyCell="{ column, record }">
              <template v-if="column.key === 'name'">
                <div>{{ record.username || [record.firstname, record.lastname].filter(Boolean).join(' ') }}</div>
                <div class="muted">{{ record.email }}</div>
              </template>
              <template v-else-if="column.key === 'status'">
                <a-tag :color="record.statut === 1 ? 'green' : 'red'">
                  {{ record.statut === 1 ? 'Active' : 'Inactive' }}
                </a-tag>
              </template>
              <template v-else-if="column.key === 'actions'">
                <a-space>
                  <a-button size="small" @click="openEdit(record)">Edit</a-button>
                  <a-popconfirm
                    title="Delete this admin login?"
                    ok-text="Delete"
                    ok-type="danger"
                    cancel-text="Cancel"
                    @confirm="removeAdmin(record)"
                  >
                    <a-button size="small" danger :loading="removing === record.id">Delete</a-button>
                  </a-popconfirm>
                </a-space>
              </template>
            </template>
          </a-table>
        </a-card>
      </a-col>
    </a-row>

    <a-modal
      v-model:open="adminOpen"
      :title="editingAdmin ? 'Edit admin' : 'Add admin'"
      :mask-closable="false"
      :footer="null"
    >
      <a-form layout="vertical" @submit.prevent="saveAdmin">
        <a-row :gutter="12">
          <a-col :span="12">
            <a-form-item label="First name" required>
              <a-input v-model:value="adminForm.firstname" />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="Last name" required>
              <a-input v-model:value="adminForm.lastname" />
            </a-form-item>
          </a-col>
        </a-row>
        <a-form-item label="Email" required>
          <a-input v-model:value="adminForm.email" type="email" />
        </a-form-item>
        <a-form-item :label="editingAdmin ? 'New password (leave blank to keep)' : 'Password'" :required="!editingAdmin">
          <a-input-password v-model:value="adminForm.password" :placeholder="editingAdmin ? 'Unchanged' : ''" />
        </a-form-item>
        <a-form-item v-if="editingAdmin" label="Status">
          <a-select v-model:value="adminForm.statut" style="width: 100%">
            <a-select-option :value="1">Active</a-select-option>
            <a-select-option :value="0">Inactive</a-select-option>
          </a-select>
        </a-form-item>
        <div class="modal-actions">
          <a-button @click="adminOpen = false">Cancel</a-button>
          <a-button type="primary" html-type="submit" :loading="savingAdmin">
            {{ editingAdmin ? 'Save' : 'Create login' }}
          </a-button>
        </div>
      </a-form>
    </a-modal>
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
const savingAdmin = ref(false);
const removing = ref(null);
const dirty = ref(false);
const tenant = ref(null);
const admins = ref([]);
const catalog = ref([]);
const flags = reactive({});
const adminOpen = ref(false);
const editingAdmin = ref(null);
const adminForm = reactive({ firstname: '', lastname: '', email: '', password: '', statut: 1 });

const adminColumns = [
  { title: 'Admin', key: 'name' },
  { title: 'Status', key: 'status', width: 100 },
  { title: '', key: 'actions', width: 160 },
];

function labelFor(key) {
  return TOGGLEABLE_MODULES.find(m => m.key === key)?.label || key;
}

function apiError(e, fallback) {
  const data = e?.data;
  if (data?.errors && typeof data.errors === 'object') {
    const first = Object.values(data.errors).flat()[0];
    if (first) return first;
  }
  return data?.message || e?.message || fallback;
}

function resetAdminForm() {
  adminForm.firstname = '';
  adminForm.lastname = '';
  adminForm.email = '';
  adminForm.password = '';
  adminForm.statut = 1;
}

function openCreate() {
  editingAdmin.value = null;
  resetAdminForm();
  adminOpen.value = true;
}

function openEdit(record) {
  editingAdmin.value = record;
  adminForm.firstname = record.firstname || '';
  adminForm.lastname = record.lastname || '';
  adminForm.email = record.email || '';
  adminForm.password = '';
  adminForm.statut = record.statut === 0 ? 0 : 1;
  adminOpen.value = true;
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
    message.error(apiError(e, 'Failed to load'));
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
    message.error(apiError(e, 'Save failed'));
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
    message.error(apiError(e, 'Could not enter company'));
  } finally {
    entering.value = false;
  }
}

async function saveAdmin() {
  if (!adminForm.firstname || !adminForm.lastname || !adminForm.email) {
    message.error('First name, last name and email are required');
    return;
  }
  if (!editingAdmin.value && !adminForm.password) {
    message.error('Password is required');
    return;
  }
  savingAdmin.value = true;
  try {
    const payload = {
      firstname: adminForm.firstname,
      lastname: adminForm.lastname,
      email: adminForm.email,
    };
    if (adminForm.password) payload.password = adminForm.password;
    if (editingAdmin.value) {
      payload.statut = adminForm.statut;
      await http.put(`platform/tenants/${route.params.id}/admins/${editingAdmin.value.id}`, payload);
      message.success('Admin updated');
    } else {
      await http.post(`platform/tenants/${route.params.id}/admins`, payload);
      message.success('Admin created');
    }
    adminOpen.value = false;
    resetAdminForm();
    editingAdmin.value = null;
    await load();
  } catch (e) {
    message.error(apiError(e, editingAdmin.value ? 'Update failed' : 'Create failed'));
  } finally {
    savingAdmin.value = false;
  }
}

async function removeAdmin(record) {
  removing.value = record.id;
  try {
    await http.delete(`platform/tenants/${route.params.id}/admins/${record.id}`);
    message.success('Admin deleted');
    await load();
  } catch (e) {
    message.error(apiError(e, 'Delete failed'));
  } finally {
    removing.value = null;
  }
}

onMounted(load);
</script>

<style scoped>
.page-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
.muted { color: #888; margin: 4px 0 0; }
.mod-grid { display: flex; flex-direction: column; gap: 4px; margin-bottom: 16px; max-height: 420px; overflow: auto; }
.mod-row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid rgba(0,0,0,.06); }
.modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 8px; }
</style>

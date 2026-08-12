<template>
  <div>
    <div class="page-head">
      <div>
        <h2 style="margin:0">Companies</h2>
        <p class="muted">Create companies, assign modules, and provision admin logins.</p>
      </div>
      <a-button type="primary" @click="openCreate">New company</a-button>
    </div>

    <a-card :loading="loading">
      <a-table
        :data-source="tenants"
        :columns="columns"
        row-key="id"
        :pagination="{ pageSize: 20 }"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'status'">
            <a-tag :color="record.status === 'active' ? 'green' : 'red'">{{ record.status }}</a-tag>
          </template>
          <template v-else-if="column.key === 'modules'">
            <span>{{ enabledCount(record.module_flags) }} enabled</span>
          </template>
          <template v-else-if="column.key === 'actions'">
            <a-space>
              <a-button size="small" @click="openEdit(record)">Edit</a-button>
              <a-button size="small" @click="$router.push(`/platform/tenants/${record.id}`)">Manage</a-button>
            </a-space>
          </template>
        </template>
      </a-table>
    </a-card>

    <a-modal
      v-model:open="modalOpen"
      :title="editing ? 'Edit company' : 'Create company'"
      :confirm-loading="saving"
      @ok="save"
      width="720px"
    >
      <a-form layout="vertical">
        <a-form-item label="Company name" required>
          <a-input v-model:value="form.name" placeholder="Acme Hospital" />
        </a-form-item>
        <a-form-item v-if="!editing" label="Slug">
          <a-input v-model:value="form.slug" placeholder="auto from name" />
        </a-form-item>
        <a-form-item label="Status">
          <a-select v-model:value="form.status" style="width: 100%">
            <a-select-option value="active">Active</a-select-option>
            <a-select-option value="suspended">Suspended</a-select-option>
          </a-select>
        </a-form-item>

        <a-divider>Modules</a-divider>
        <div class="mod-grid">
          <div v-for="m in catalog" :key="m" class="mod-row">
            <span>{{ labelFor(m) }}</span>
            <a-switch v-model:checked="form.module_flags[m]" />
          </div>
        </div>

        <template v-if="!editing">
          <a-divider>Admin login</a-divider>
          <a-row :gutter="12">
            <a-col :span="12">
              <a-form-item label="First name" required>
                <a-input v-model:value="form.admin_firstname" />
              </a-form-item>
            </a-col>
            <a-col :span="12">
              <a-form-item label="Last name" required>
                <a-input v-model:value="form.admin_lastname" />
              </a-form-item>
            </a-col>
          </a-row>
          <a-form-item label="Email" required>
            <a-input v-model:value="form.admin_email" type="email" />
          </a-form-item>
          <a-form-item label="Password" required>
            <a-input-password v-model:value="form.admin_password" />
          </a-form-item>
        </template>
      </a-form>
    </a-modal>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import http from '../../lib/http';
import { TOGGLEABLE_MODULES } from '../../config/modules';

const loading = ref(true);
const saving = ref(false);
const modalOpen = ref(false);
const editing = ref(null);
const tenants = ref([]);
const catalog = ref([]);

const columns = [
  { title: 'Name', dataIndex: 'name', key: 'name' },
  { title: 'Slug', dataIndex: 'slug', key: 'slug' },
  { title: 'Status', key: 'status' },
  { title: 'Users', dataIndex: 'users_count', key: 'users_count' },
  { title: 'Modules', key: 'modules' },
  { title: '', key: 'actions' },
];

const form = reactive({
  name: '',
  slug: '',
  status: 'active',
  module_flags: {},
  admin_firstname: '',
  admin_lastname: '',
  admin_email: '',
  admin_password: '',
});

function labelFor(key) {
  return TOGGLEABLE_MODULES.find(m => m.key === key)?.label || key;
}

function enabledCount(flags) {
  if (!flags || typeof flags !== 'object') return 'all';
  return Object.values(flags).filter(Boolean).length;
}

function blankFlags(list) {
  const out = {};
  for (const k of list) out[k] = false;
  // Sensible default for new companies: stock on
  if (Object.prototype.hasOwnProperty.call(out, 'stock')) out.stock = true;
  return out;
}

function openCreate() {
  editing.value = null;
  form.name = '';
  form.slug = '';
  form.status = 'active';
  form.module_flags = blankFlags(catalog.value);
  form.admin_firstname = '';
  form.admin_lastname = '';
  form.admin_email = '';
  form.admin_password = '';
  modalOpen.value = true;
}

function openEdit(record) {
  editing.value = record;
  form.name = record.name;
  form.slug = record.slug;
  form.status = record.status;
  const flags = blankFlags(catalog.value);
  if (record.module_flags && typeof record.module_flags === 'object') {
    for (const k of catalog.value) {
      flags[k] = record.module_flags[k] !== false;
    }
  } else {
    for (const k of catalog.value) flags[k] = true;
  }
  form.module_flags = flags;
  modalOpen.value = true;
}

async function save() {
  saving.value = true;
  try {
    if (editing.value) {
      await http.put(`platform/tenants/${editing.value.id}`, {
        name: form.name,
        status: form.status,
        module_flags: { ...form.module_flags },
      });
      message.success('Company updated');
    } else {
      await http.post('platform/tenants', {
        name: form.name,
        slug: form.slug || undefined,
        status: form.status,
        module_flags: { ...form.module_flags },
        admin_firstname: form.admin_firstname,
        admin_lastname: form.admin_lastname,
        admin_email: form.admin_email,
        admin_password: form.admin_password,
      });
      message.success('Company created');
    }
    modalOpen.value = false;
    await load();
  } catch (e) {
    message.error(e?.message || 'Save failed');
  } finally {
    saving.value = false;
  }
}

async function load() {
  loading.value = true;
  try {
    const data = await http.get('platform/tenants');
    tenants.value = data.tenants || [];
    catalog.value = data.module_catalog || TOGGLEABLE_MODULES.map(m => m.key);
  } catch (e) {
    message.error(e?.message || 'Failed to load companies');
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>

<style scoped>
.page-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 16px;
  gap: 12px;
}
.muted { color: #888; margin: 4px 0 0; }
.mod-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px 16px;
  max-height: 280px;
  overflow: auto;
  margin-bottom: 8px;
}
.mod-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 0;
  border-bottom: 1px solid rgba(0,0,0,.06);
}
</style>

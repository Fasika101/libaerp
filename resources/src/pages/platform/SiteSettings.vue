<template>
  <div>
    <div class="page-head">
      <div>
        <h2 style="margin:0">Site Settings</h2>
        <p class="muted">
          Platform-wide branding and defaults. These apply to the login page and
          anywhere no company context exists; each company keeps its own settings.
        </p>
      </div>
    </div>

    <a-card :loading="loading" style="max-width: 720px">
      <a-form layout="vertical">
        <a-row :gutter="12">
          <a-col :span="12">
            <a-form-item label="Application name">
              <a-input v-model:value="form.app_name" placeholder="Stocky" />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="Page title suffix">
              <a-input v-model:value="form.page_title_suffix" placeholder="Shown after page titles" />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="12">
          <a-col :span="12">
            <a-form-item label="Company / site name">
              <a-input v-model:value="form.CompanyName" />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="Contact email">
              <a-input v-model:value="form.email" type="email" />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="12">
          <a-col :span="12">
            <a-form-item label="Phone">
              <a-input v-model:value="form.CompanyPhone" />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="Address">
              <a-input v-model:value="form.CompanyAdress" />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="12">
          <a-col :span="12">
            <a-form-item label="Footer text">
              <a-input v-model:value="form.footer" placeholder="© 2026 Your Platform" />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="Developed by">
              <a-input v-model:value="form.developed_by" />
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item label="Default language">
          <a-input v-model:value="form.default_language" placeholder="en" style="max-width: 160px" />
        </a-form-item>

        <a-row :gutter="12">
          <a-col :span="12">
            <a-form-item label="Logo (shown on login page and sidebar)">
              <div class="img-field">
                <img v-if="logoPreview || form.logo" :src="logoPreview || `/images/${form.logo}`" alt="logo" class="img-preview" />
                <input type="file" accept="image/*" @change="e => onFile(e, 'logo')" />
              </div>
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="Favicon (.ico or .png)">
              <div class="img-field">
                <img v-if="faviconPreview || form.favicon" :src="faviconPreview || `/images/${form.favicon}`" alt="favicon" class="img-preview img-preview--small" />
                <input type="file" accept=".ico,.png" @change="e => onFile(e, 'favicon')" />
              </div>
            </a-form-item>
          </a-col>
        </a-row>

        <a-button type="primary" :loading="saving" @click="save">Save site settings</a-button>
      </a-form>
    </a-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import http from '../../lib/http';
import { useAuthStore } from '../../stores/auth';

const loading = ref(true);
const saving = ref(false);

const form = reactive({
  app_name: '',
  page_title_suffix: '',
  CompanyName: '',
  email: '',
  CompanyPhone: '',
  CompanyAdress: '',
  footer: '',
  developed_by: '',
  default_language: 'en',
  logo: null,
  favicon: null,
});

// Picked files (sent as multipart) + local previews until the save round-trips.
const files = { logo: null, favicon: null };
const logoPreview = ref(null);
const faviconPreview = ref(null);

function onFile(e, kind) {
  const file = e.target.files && e.target.files[0];
  if (!file) return;
  files[kind] = file;
  const url = URL.createObjectURL(file);
  if (kind === 'logo') logoPreview.value = url;
  else faviconPreview.value = url;
}

async function load() {
  loading.value = true;
  try {
    const data = await http.get('platform/settings');
    Object.assign(form, data.settings || {});
  } catch (e) {
    message.error(e?.message || 'Failed to load site settings');
  } finally {
    loading.value = false;
  }
}

async function save() {
  saving.value = true;
  try {
    const fd = new FormData();
    for (const [key, value] of Object.entries(form)) {
      if (key === 'logo' || key === 'favicon') continue; // filenames are read-only
      if (value !== null && value !== undefined) fd.append(key, value);
    }
    if (files.logo) fd.append('logo', files.logo);
    if (files.favicon) fd.append('favicon', files.favicon);
    await http.postForm('platform/settings', fd);
    message.success('Site settings saved');
    files.logo = null;
    files.favicon = null;
    logoPreview.value = null;
    faviconPreview.value = null;
    await load();
    // Branding (sidebar name/logo, footer) comes from these settings for the
    // super admin, so refresh the chrome in place.
    await useAuthStore().reload();
  } catch (e) {
    message.error(e?.message || 'Save failed');
  } finally {
    saving.value = false;
  }
}

onMounted(load);
</script>

<style scoped>
.page-head { margin-bottom: 16px; }
.muted { color: #888; margin: 4px 0 0; max-width: 640px; }
.img-field { display: flex; align-items: center; gap: 12px; }
.img-preview {
  width: 48px;
  height: 48px;
  object-fit: contain;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 6px;
  background: #fff;
}
.img-preview--small { width: 32px; height: 32px; }
</style>

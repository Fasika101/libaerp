<template>
  <a-card :loading="loading">
    <a-alert
      type="info"
      show-icon
      message="Module entitlements are assigned by the platform"
      description="Your company modules are controlled by the Super Admin. Contact them to enable Hospital, Marketing, Stock, or other modules."
      style="margin-bottom: 16px"
    />

    <div class="mt-list">
      <div v-for="mod in modules" :key="mod.key" class="mt-row" :class="{ 'mt-off': !flags[mod.key] }">
        <span class="mt-icon">
          <component :is="mod.iconCmp" v-if="mod.iconCmp" :size="18" />
          <AppstoreOutlined v-else />
        </span>
        <div class="mt-text">
          <div class="mt-label">{{ mod.label }}</div>
          <div class="mt-desc">{{ mod.description }}</div>
        </div>
        <a-switch v-model:checked="flags[mod.key]" disabled />
      </div>
    </div>
  </a-card>
</template>

<script setup>
/**
 * Read-only view of tenant module entitlements (SaaS). Writes are platform-only.
 */
import { ref, reactive, onMounted } from 'vue';
import { message } from 'ant-design-vue';
import { AppstoreOutlined } from '@ant-design/icons-vue';
import { TOGGLEABLE_MODULES } from '../../config/modules';
import { MENU } from '../../config/menu';
import { MENU_ICONS } from '../../config/menuIcons';
import { useAuthStore } from '../../stores/auth';
import http from '../../lib/http';

const auth = useAuthStore();
const iconByKey = Object.fromEntries(MENU.map(e => [e.key, MENU_ICONS[e.icon]]));
iconByKey.stock = MENU_ICONS['shopping-basket'] || MENU_ICONS['shopping-cart'];
const modules = TOGGLEABLE_MODULES.map(m => ({ ...m, iconCmp: iconByKey[m.key] || iconByKey.stock }));

const loading = ref(true);
const flags = reactive({});

function applyFlags(map) {
  for (const m of TOGGLEABLE_MODULES) {
    flags[m.key] = !map || map[m.key] !== false;
  }
}

async function load() {
  loading.value = true;
  try {
    const data = await http.get('module_flags');
    applyFlags(data?.module_flags || auth.user?.module_flags || null);
  } catch (e) {
    applyFlags(auth.user?.module_flags || null);
    message.error(e?.message || 'Failed to load modules');
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>

<style scoped>
.mt-list { display: flex; flex-direction: column; gap: 4px; }
.mt-row {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 8px; border-bottom: 1px solid rgba(0,0,0,.06);
}
.mt-row.mt-off { opacity: .55; }
.mt-icon { width: 28px; display: flex; justify-content: center; }
.mt-text { flex: 1; }
.mt-label { font-weight: 600; }
.mt-desc { font-size: 12px; color: #888; }
</style>

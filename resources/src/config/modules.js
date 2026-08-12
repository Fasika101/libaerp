// Optional business modules. `key` MUST match (or map via MENU_KEY_TO_MODULE)
// the top-level entry key in config/menu.js. Flags live on tenants.module_flags
// and reach the SPA via get_user_auth (auth.user.module_flags).
//
// A null map or a missing key means the module is ENABLED.

export const TOGGLEABLE_MODULES = [
    { key: 'stock', label: 'Stock Management', description: 'Products, POS, sales, purchases, quotations, stock movements, customers and suppliers.', pathPrefixes: ['products', 'pos', 'sales', 'purchases', 'quotations', 'adjustments', 'transfers', 'damages', 'People', 'warehouses', 'brands', 'categories', 'units', 'sale_return', 'purchase_return', 'clients', 'providers'] },
    { key: 'Store', label: 'Store / E-commerce', description: 'Online storefront: orders, collections, banners, pages and Real Estate listings.', pathPrefixes: ['store', 'realestate'] },
    { key: 'hrm', label: 'HRM', description: 'Employees, attendance, payroll, contracts and the knowledge base.', pathPrefixes: ['hrm', 'contracts'] },
    { key: 'recruit', label: 'Recruits & Jobs', description: 'Job postings, candidates, applications and interviews.', pathPrefixes: ['recruit'] },
    { key: 'meeting', label: 'Meetings', description: 'Meeting planning, attendance and reports.', pathPrefixes: ['meeting', 'meetings'] },
    { key: 'marketing', label: 'Marketing', description: 'Campaigns and marketing tools.', pathPrefixes: ['marketing'] },
    { key: 'accounting', label: 'Accounting', description: 'Chart of accounts, journal entries, financial reports, expenses and deposits.', pathPrefixes: ['accounting-v2'] },
    { key: 'EWallet', label: 'E-Wallet', description: 'Customer wallet balances and wallet items.', pathPrefixes: ['ewallet'] },
    { key: 'commissions', label: 'Commissions', description: 'Agent commission programs, rules and receipts.', pathPrefixes: ['commissions'] },
    { key: 'promotions', label: 'Promotions', description: 'Discount promotions applied at the POS checkout.', pathPrefixes: ['promotions'] },
    { key: 'woocommerce_settings', label: 'WooCommerce', description: 'WooCommerce store synchronization.', pathPrefixes: [] },
    { key: 'shopify', label: 'Shopify', description: 'Shopify store synchronization and logs.', pathPrefixes: ['shopify'] },
    { key: 'documents', label: 'Document Archive', description: 'Central document storage and archiving.', pathPrefixes: ['documents'] },
    { key: 'subscription_product', label: 'Subscription Products', description: 'Recurring product subscriptions.', pathPrefixes: ['subscriptions'] },
    { key: 'manufacturing', label: 'Manufacturing (MRP)', description: 'Bills of materials, production orders, work centers, quality and planning.', pathPrefixes: ['mrp'] },
    { key: 'assets', label: 'Asset Management', description: 'Company assets, assignments, maintenance, transfers and depreciation.', pathPrefixes: ['assets'] },
    { key: 'projects', label: 'Projects & Tasks', description: 'Projects, tasks, milestones, timesheets and project reports.', pathPrefixes: ['projects', 'tasks'] },
    { key: 'bookings', label: 'Booking Management', description: 'Bookings, calendar and trays.', pathPrefixes: ['bookings'] },
    { key: 'service', label: 'Service & Maintenance', description: 'Service jobs, technicians and checklists.', pathPrefixes: ['service'] },
    { key: 'fleet', label: 'Fleet Management', description: 'Vehicles, maintenance, fuel logs, assignments and fleet reports.', pathPrefixes: ['fleet'] },
    { key: 'hospital', label: 'Hospital Management', description: 'Patients, doctors, appointments, visits, admissions, wards, lab and billing.', pathPrefixes: ['hospital'] },
    { key: 'school', label: 'School Management', description: 'Students, teachers, academics, exams, timetable and fees.', pathPrefixes: ['school'] },
];

/** Menu top-level keys that belong to the stock module. */
export const STOCK_MENU_KEYS = new Set([
    'products', 'sales', 'sale_return', 'purchases', 'purchase_return',
    'quotations', 'adjustments', 'transfers', 'damages', 'People',
]);

const MODULE_KEYS = new Set(TOGGLEABLE_MODULES.map(m => m.key));

// First URL segment -> module key, for the router guard.
const PREFIX_TO_MODULE = {};
for (const m of TOGGLEABLE_MODULES) {
    for (const p of m.pathPrefixes) PREFIX_TO_MODULE[p] = m.key;
}

/** Resolve which module owns a sidebar menu entry key. */
export function moduleKeyForMenuKey(menuKey) {
    if (STOCK_MENU_KEYS.has(menuKey)) return 'stock';
    if (MODULE_KEYS.has(menuKey)) return menuKey;
    return null; // always-on (dashboard, settings, reports, User_Management, …)
}

/** True when `key` is enabled under the given flags map (null map = all on). */
export function isModuleEnabled(flags, key) {
    const moduleKey = moduleKeyForMenuKey(key) || (MODULE_KEYS.has(key) ? key : null);
    if (!moduleKey) return true; // not toggleable -> always on
    if (!flags || typeof flags !== 'object') return true;
    return flags[moduleKey] !== false;
}

/** Module key owning an SPA path ('/hospital/patients' -> 'hospital'), or null. */
export function moduleKeyForPath(path) {
    const seg = String(path || '').replace(/^\/+/, '').split('/')[0];
    return PREFIX_TO_MODULE[seg] || null;
}

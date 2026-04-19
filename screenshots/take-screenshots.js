/**
 * Automated Screenshot Tool — Shreenathji Home Finance
 *
 * Captures every screen across multiple viewport sizes. Two coverage modes:
 *
 *   1. MAIN       — one screenshot per route (default tab only, no per-loan / per-role loops).
 *                   ~40 screens total per viewport. Fast sanity check.
 *
 *   2. COMPLETE   — every tab on tabbed pages, every seeded loan's show+stages page,
 *                   every loan-stages view under each of the 6 roles, plus per-DVR and per-task detail pages.
 *                   Hundreds of screenshots per viewport — used for full UI review.
 *
 * Reads `screenshot-fixtures.json` (written by `php artisan app:seed-screenshot-loans`)
 * to find the IDs for `/{id}` routes. Reads `loan-stage-map.json` for the loans list.
 *
 * Usage: node screenshots/take-screenshots.js
 * Requires: puppeteer
 */

const puppeteer = require('puppeteer');
const path = require('path');
const fs = require('fs');

const BASE_URL = 'https://oldloanproposal.test';
const LOGIN_EMAIL = 'admin@shf.com';
const LOGIN_PASSWORD = 'Admin@123';

// All viewport configurations (keyed by menu number)
const ALL_VIEWPORTS = {
  1: { key: 'mobile/portrait',   width: 375,  height: 812,  label: 'Mobile Portrait'  },
  2: { key: 'mobile/landscape',  width: 812,  height: 375,  label: 'Mobile Landscape' },
  3: { key: 'tablet/portrait',   width: 768,  height: 1024, label: 'Tablet Portrait'  },
  4: { key: 'tablet/landscape',  width: 1024, height: 768,  label: 'Tablet Landscape' },
  5: { key: 'laptop',            width: 1366, height: 768,  label: 'Laptop'           },
  6: { key: 'desktop',           width: 1920, height: 1080, label: 'Desktop (Full HD)'},
  7: { key: 'large-desktop',     width: 2560, height: 1440, label: 'Large Desktop (QHD)' },
};

// Populated after user selection
let VIEWPORTS = {};

// Full static-route catalog. `tabs` = shf-tab switching.
// In MAIN mode, tabs are ignored (one screenshot per page).
// In COMPLETE mode, each tab gets its own screenshot.
const PAGES = [
  // ── Auth (unauthenticated) ──
  { name: 'login',                  path: '/login',               auth: false },
  { name: 'forgot-password',        path: '/forgot-password',     auth: false },

  // ── Dashboard — tabs ──
  { name: 'dashboard',              path: '/dashboard', tabs: [
    { name: 'dashboard-personal-tasks', dataTab: 'dash-personal-tasks' },
    { name: 'dashboard-tasks',          dataTab: 'dash-tasks' },
    { name: 'dashboard-loans',          dataTab: 'dash-loans' },
    { name: 'dashboard-dvr',            dataTab: 'dash-dvr' },
    { name: 'dashboard-quotations',     dataTab: 'dash-quotations' },
  ]},

  // ── Quotations ──
  { name: 'quotations-index',       path: '/quotations' },
  { name: 'quotation-create',       path: '/quotations/create' },

  // ── Loans ──
  { name: 'loans-index',            path: '/loans' },
  { name: 'loan-create',            path: '/loans/create' },

  // ── Customers ──
  { name: 'customers-index',        path: '/customers' },

  // ── General Tasks ──
  { name: 'general-tasks-index',    path: '/general-tasks' },

  // ── DVR ──
  { name: 'dvr-index',              path: '/dvr' },

  // ── Reports ──
  { name: 'report-turnaround',      path: '/reports/turnaround' },

  // ── Users ──
  { name: 'users-index',            path: '/users' },
  { name: 'user-create',            path: '/users/create' },

  // ── Roles ──
  { name: 'roles-index',            path: '/roles' },
  { name: 'role-create',            path: '/roles/create' },

  // ── Permissions ──
  { name: 'permissions',            path: '/permissions' },

  // ── Notifications & Activity ──
  { name: 'notifications',          path: '/notifications' },
  { name: 'activity-log',           path: '/activity-log' },

  // ── Profile ──
  { name: 'profile',                path: '/profile' },

  // ── Quotation Settings — tabs ──
  { name: 'settings',               path: '/settings', tabs: [
    { name: 'settings-company',            dataTab: 'company' },
    { name: 'settings-charges',            dataTab: 'charges' },
    { name: 'settings-bank-charges',       dataTab: 'bank-charges' },
    { name: 'settings-gst',                dataTab: 'gst' },
    { name: 'settings-services',           dataTab: 'services' },
    { name: 'settings-tenures',            dataTab: 'tenures' },
    { name: 'settings-documents',          dataTab: 'documents' },
    { name: 'settings-dvr',                dataTab: 'dvr' },
    { name: 'settings-quotation-reasons',  dataTab: 'quotation-reasons' },
    { name: 'settings-permissions',        dataTab: 'permissions' },
  ]},

  // ── Loan Settings — tabs ──
  { name: 'loan-settings',          path: '/loan-settings', tabs: [
    { name: 'loan-settings-locations',        dataTab: 'locations' },
    { name: 'loan-settings-banks',            dataTab: 'banks' },
    { name: 'loan-settings-branches',         dataTab: 'branches' },
    { name: 'loan-settings-master-stages',    dataTab: 'master-stages' },
    { name: 'loan-settings-products',         dataTab: 'products' },
    { name: 'loan-settings-role-permissions', dataTab: 'role-permissions' },
  ]},
];

// Per-loan pages — captured for EVERY seeded loan in COMPLETE mode, skipped in MAIN.
const PER_LOAN_PAGES = [
  { suffix: 'show',   pathFn: (id) => `/loans/${id}` },
  { suffix: 'stages', pathFn: (id) => `/loans/${id}/stages` },
];

// Single-loan pages — captured once using the fixture loan in BOTH modes.
const SINGLE_LOAN_PAGES = [
  { name: 'loan-show',              pathFn: (id) => `/loans/${id}` },
  { name: 'loan-stages',            pathFn: (id) => `/loans/${id}/stages` },
  { name: 'loan-edit',              pathFn: (id) => `/loans/${id}/edit` },
  { name: 'loan-documents',         pathFn: (id) => `/loans/${id}/documents` },
  { name: 'loan-timeline',          pathFn: (id) => `/loans/${id}/timeline` },
  { name: 'loan-transfers',         pathFn: (id) => `/loans/${id}/transfers` },
  { name: 'loan-disbursement',      pathFn: (id) => `/loans/${id}/disbursement` },
  { name: 'loan-valuation',         pathFn: (id) => `/loans/${id}/valuation` },
  { name: 'loan-valuation-map',     pathFn: (id) => `/loans/${id}/valuation-map` },
  { name: 'loan-remarks',           pathFn: (id) => `/loans/${id}/remarks` },
];

const QUOTATION_PAGES = [
  { name: 'quotation-show',         pathFn: (id) => `/quotations/${id}` },
  { name: 'quotation-convert',      pathFn: (id) => `/quotations/${id}/convert` },
];

const USER_PAGES = [
  { name: 'user-edit',              pathFn: (id) => `/users/${id}/edit` },
];

const ROLE_DETAIL_PAGES = [
  { name: 'role-edit',              pathFn: (id) => `/roles/${id}/edit` },
];

const CUSTOMER_PAGES = [
  { name: 'customer-show',          pathFn: (id) => `/customers/${id}` },
  { name: 'customer-edit',          pathFn: (id) => `/customers/${id}/edit` },
];

const DVR_PAGES = [
  { name: 'dvr-show',               pathFn: (id) => `/dvr/${id}` },
];

const TASK_PAGES = [
  { name: 'general-task-show',      pathFn: (id) => `/general-tasks/${id}` },
];

const PRODUCT_STAGES_PAGES = [
  { name: 'product-stages',         pathFn: (id) => `/loan-settings/products/${id}/stages` },
];

// Users for multi-role loan-stage screenshots (COMPLETE mode only).
const ROLE_USERS = [
  { name: 'admin',           email: 'admin@shf.com',         password: 'Admin@123' },
  { name: 'branch-manager',  email: 'denish@shfworld.com',   password: 'password' },
  { name: 'bdh',             email: 'bdh@shfworld.com',      password: 'password' },
  { name: 'loan-advisor',    email: 'jaydeep@shfworld.com',  password: 'password' },
  { name: 'bank-employee',   email: 'hdfc@manager.cop',      password: 'password' },
  { name: 'office-employee', email: 'vipul@office.com',      password: 'password' },
];

const SCREENSHOT_DIR = path.join(__dirname);
const readline = require('readline');

// Runtime state — populated after user prompts.
let screenshotCounter = 0;
let screenshotMode = 2;      // 1=delete+regen, 2=replace, 3=keep-existing
let coverageMode = 'main';   // 'main' | 'complete'
let fixtures = {};

function nextNumber() {
  screenshotCounter++;
  return String(screenshotCounter).padStart(3, '0');
}

async function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function takeScreenshot(page, pageName, viewportName, viewportSize) {
  const dir = path.join(SCREENSHOT_DIR, viewportName);
  fs.mkdirSync(dir, { recursive: true });

  await page.setViewport({
    width: viewportSize.width,
    height: viewportSize.height,
    deviceScaleFactor: 1,
  });

  await sleep(500);

  const filePath = path.join(dir, `${pageName}.png`);

  if (screenshotMode === 3 && fs.existsSync(filePath)) {
    console.log(`  ⏭ ${viewportName}/${pageName}.png (exists, skipped)`);
    return;
  }

  await page.screenshot({ path: filePath, fullPage: true });
  console.log(`  ✓ ${viewportName}/${pageName}.png`);
}

async function clickTab(page, dataTab) {
  await page.evaluate((tab) => {
    const btn = document.querySelector(`.shf-tab[data-tab="${tab}"]`);
    if (btn) btn.click();
  }, dataTab);
  await sleep(800);
}

/**
 * Capture one page. In COMPLETE mode, iterate each tab. In MAIN mode, one screenshot per page.
 */
async function capturePage(page, pg, pagePath) {
  try {
    await page.goto(`${BASE_URL}${pagePath}`, { waitUntil: 'networkidle2' });
    await sleep(1000);

    if (coverageMode === 'complete' && pg.tabs && pg.tabs.length > 0) {
      for (const tab of pg.tabs) {
        const num = nextNumber();
        const numberedName = `${num}-${tab.name}`;
        console.log(`\n📄 ${numberedName} (${pagePath}#${tab.dataTab})`);
        await clickTab(page, tab.dataTab);
        for (const [vpName, vpSize] of Object.entries(VIEWPORTS)) {
          await takeScreenshot(page, numberedName, vpName, vpSize);
        }
      }
    } else {
      const num = nextNumber();
      const numberedName = `${num}-${pg.name}`;
      console.log(`\n📄 ${numberedName} (${pagePath})`);
      for (const [vpName, vpSize] of Object.entries(VIEWPORTS)) {
        await takeScreenshot(page, numberedName, vpName, vpSize);
      }
    }
  } catch (err) {
    console.log(`  ✗ Error: ${err.message}`);
  }
}

async function captureNamedPage(page, name, pagePath) {
  const num = nextNumber();
  const numberedName = `${num}-${name}`;
  console.log(`\n📄 ${numberedName} (${pagePath})`);
  try {
    await page.goto(`${BASE_URL}${pagePath}`, { waitUntil: 'networkidle2' });
    await sleep(1000);
    for (const [vpName, vpSize] of Object.entries(VIEWPORTS)) {
      await takeScreenshot(page, numberedName, vpName, vpSize);
    }
  } catch (err) {
    console.log(`  ✗ Error: ${err.message}`);
  }
}

async function selectCoverageMode() {
  console.log('\n╔══════════════════════════════════════════════╗');
  console.log('║   Coverage Mode                              ║');
  console.log('╠══════════════════════════════════════════════╣');
  console.log('║   1. Main — one shot per route (fast)        ║');
  console.log('║   2. Complete — every tab/stage/role         ║');
  console.log('╚══════════════════════════════════════════════╝');
  const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
  const answer = await new Promise(resolve => rl.question('\nYour choice (default: 1): ', resolve));
  rl.close();
  const choice = parseInt(answer.trim()) || 1;
  coverageMode = choice === 2 ? 'complete' : 'main';
  console.log(`\n✓ Coverage mode: ${coverageMode.toUpperCase()}`);
}

async function selectViewports() {
  console.log('\n╔══════════════════════════════════════╗');
  console.log('║   Select Viewport(s) to Capture      ║');
  console.log('╠══════════════════════════════════════╣');
  for (const [num, vp] of Object.entries(ALL_VIEWPORTS)) {
    console.log(`║   ${num}. ${vp.label.padEnd(30)} ║`);
  }
  console.log('║   8. All viewports                   ║');
  console.log('╚══════════════════════════════════════╝');
  console.log('\nEnter choice(s) — comma-separated or single number');
  console.log('Examples: 1  |  1,2,5  |  8\n');

  const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
  const answer = await new Promise(resolve => rl.question('Your choice: ', resolve));
  rl.close();

  const choices = answer.trim().split(/[,\s]+/).map(Number).filter(n => !isNaN(n));

  if (choices.includes(8)) {
    for (const [, vp] of Object.entries(ALL_VIEWPORTS)) {
      VIEWPORTS[vp.key] = { width: vp.width, height: vp.height };
    }
  } else {
    for (const num of choices) {
      const vp = ALL_VIEWPORTS[num];
      if (vp) {
        VIEWPORTS[vp.key] = { width: vp.width, height: vp.height };
      } else {
        console.log(`⚠ Ignoring invalid choice: ${num}`);
      }
    }
  }

  if (Object.keys(VIEWPORTS).length === 0) {
    console.log('No valid viewports selected. Exiting.');
    process.exit(1);
  }

  const selected = Object.keys(VIEWPORTS).join(', ');
  console.log(`\n✓ Selected: ${selected}`);

  const rl2 = readline.createInterface({ input: process.stdin, output: process.stdout });
  console.log('\n╔══════════════════════════════════════╗');
  console.log('║   Existing Screenshots               ║');
  console.log('╠══════════════════════════════════════╣');
  console.log('║   1. Delete all & regenerate         ║');
  console.log('║   2. Replace (overwrite matching)    ║');
  console.log('║   3. Keep existing, add new only     ║');
  console.log('╚══════════════════════════════════════╝');
  const modeAnswer = await new Promise(resolve => rl2.question('\nYour choice (default: 2): ', resolve));
  rl2.close();

  const mode = parseInt(modeAnswer.trim()) || 2;

  if (mode === 1) {
    for (const vpKey of Object.keys(VIEWPORTS)) {
      const dir = path.join(SCREENSHOT_DIR, vpKey);
      if (fs.existsSync(dir)) {
        const files = fs.readdirSync(dir).filter(f => f.endsWith('.png'));
        files.forEach(f => fs.unlinkSync(path.join(dir, f)));
      }
    }
    console.log('✓ Deleted old screenshots for selected viewports\n');
  } else if (mode === 2) {
    console.log('✓ Will overwrite existing screenshots with same name\n');
  } else {
    console.log('✓ Will skip files that already exist\n');
  }

  return mode;
}

function loadFixtures() {
  const fixturesPath = path.join(SCREENSHOT_DIR, 'screenshot-fixtures.json');
  if (!fs.existsSync(fixturesPath)) {
    console.log('\n⚠ screenshot-fixtures.json not found.');
    console.log(`  Run: php artisan app:seed-screenshot-loans --mode=${coverageMode}\n`);
    return {};
  }
  const data = JSON.parse(fs.readFileSync(fixturesPath, 'utf8'));
  console.log(`\n✓ Loaded fixtures (seeded mode: ${data.mode || 'unknown'})`);
  if (data.mode && data.mode !== coverageMode) {
    console.log(`  ⚠ Coverage mode mismatch: fixtures seeded for "${data.mode}", you picked "${coverageMode}".`);
    console.log(`  Re-run: php artisan app:seed-screenshot-loans --mode=${coverageMode}`);
  }
  return data;
}

function loadLoans() {
  const stageMapPath = path.join(SCREENSHOT_DIR, 'loan-stage-map.json');
  if (!fs.existsSync(stageMapPath)) {
    console.log('\n⚠ loan-stage-map.json not found — per-loan screenshots will be skipped.');
    return [];
  }
  const stageMap = JSON.parse(fs.readFileSync(stageMapPath, 'utf8'));
  return Object.entries(stageMap).map(([id, target]) => ({
    id,
    stageName: target.replace(/_/g, '-'),
  }));
}

async function run() {
  await selectCoverageMode();
  screenshotMode = await selectViewports();
  fixtures = loadFixtures();

  console.log('Launching browser...');
  const browser = await puppeteer.launch({
    headless: true,
    args: [
      '--no-sandbox',
      '--disable-setuid-sandbox',
      '--ignore-certificate-errors',
      '--allow-insecure-localhost',
    ],
    ignoreHTTPSErrors: true,
  });

  const context = await browser.createBrowserContext();
  const page = await context.newPage();
  page.setDefaultNavigationTimeout(30000);

  // ── Step 1: Login as admin ──
  console.log('\nLogging in...');
  await page.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2' });
  await page.type('input[name="email"]', LOGIN_EMAIL);
  await page.type('input[name="password"]', LOGIN_PASSWORD);
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 60000 }),
    page.click('button[type="submit"]'),
  ]);
  console.log('Logged in successfully.\n');

  // ── Step 2: Unauthenticated pages (separate guest context) ──
  const guestContext = await browser.createBrowserContext();
  const guestPage = await guestContext.newPage();
  guestPage.setDefaultNavigationTimeout(30000);

  const unauthPages = PAGES.filter(p => p.auth === false);
  for (const pg of unauthPages) {
    const num = nextNumber();
    const numberedName = `${num}-${pg.name}`;
    console.log(`📄 ${numberedName} (${pg.path})`);
    try {
      await guestPage.goto(`${BASE_URL}${pg.path}`, { waitUntil: 'networkidle2' });
      await sleep(500);
      for (const [vpName, vpSize] of Object.entries(VIEWPORTS)) {
        await takeScreenshot(guestPage, numberedName, vpName, vpSize);
      }
    } catch (err) {
      console.log(`  ✗ Error: ${err.message}`);
    }
  }
  await guestPage.close();
  await guestContext.close();

  // ── Step 3: Authenticated static pages (tabs honored in COMPLETE mode) ──
  const authPages = PAGES.filter(p => p.auth !== false);
  for (const pg of authPages) {
    await capturePage(page, pg, pg.path);
  }

  // ── Step 4: Fixture-driven detail pages (both modes) ──
  if (fixtures.loan_id) {
    for (const pg of SINGLE_LOAN_PAGES) {
      await captureNamedPage(page, pg.name, pg.pathFn(fixtures.loan_id));
    }
  }
  if (fixtures.quotation_id) {
    for (const pg of QUOTATION_PAGES) {
      await captureNamedPage(page, pg.name, pg.pathFn(fixtures.quotation_id));
    }
  }
  if (fixtures.user_id) {
    for (const pg of USER_PAGES) {
      await captureNamedPage(page, pg.name, pg.pathFn(fixtures.user_id));
    }
  }
  if (fixtures.role_id) {
    for (const pg of ROLE_DETAIL_PAGES) {
      await captureNamedPage(page, pg.name, pg.pathFn(fixtures.role_id));
    }
  }
  if (fixtures.customer_id) {
    for (const pg of CUSTOMER_PAGES) {
      await captureNamedPage(page, pg.name, pg.pathFn(fixtures.customer_id));
    }
  }
  if (fixtures.dvr_id) {
    for (const pg of DVR_PAGES) {
      await captureNamedPage(page, pg.name, pg.pathFn(fixtures.dvr_id));
    }
  }
  if (fixtures.task_id) {
    for (const pg of TASK_PAGES) {
      await captureNamedPage(page, pg.name, pg.pathFn(fixtures.task_id));
    }
  }
  if (fixtures.product_id) {
    for (const pg of PRODUCT_STAGES_PAGES) {
      await captureNamedPage(page, pg.name, pg.pathFn(fixtures.product_id));
    }
  }

  // ── Step 5: COMPLETE mode only — per-loan + per-role loan-stage views ──
  if (coverageMode === 'complete') {
    const loans = loadLoans();
    if (loans.length > 0) {
      console.log(`\n── Per-loan captures (${loans.length} loans) ──`);
      for (const loan of loans) {
        for (const pg of PER_LOAN_PAGES) {
          await captureNamedPage(page, `loan-${pg.suffix}-${loan.stageName}`, pg.pathFn(loan.id));
        }
      }

      console.log('\n── Multi-role loan-stage captures ──');
      for (const roleUser of ROLE_USERS) {
        if (roleUser.email === LOGIN_EMAIL) continue;

        console.log(`\n🔑 Switching to ${roleUser.name} (${roleUser.email})...`);
        const roleContext = await browser.createBrowserContext();
        const rolePage = await roleContext.newPage();
        rolePage.setDefaultNavigationTimeout(30000);

        try {
          await rolePage.goto(`${BASE_URL}/login`, { waitUntil: 'networkidle2' });
          await rolePage.type('input[name="email"]', roleUser.email);
          await rolePage.type('input[name="password"]', roleUser.password);
          await Promise.all([
            rolePage.waitForNavigation({ waitUntil: 'networkidle2', timeout: 60000 }),
            rolePage.click('button[type="submit"]'),
          ]);

          for (const loan of loans) {
            const pagePath = `/loans/${loan.id}/stages`;
            await captureNamedPage(rolePage, `loan-stages-${loan.stageName}-${roleUser.name}`, pagePath);
          }
        } catch (err) {
          console.log(`  ✗ Login failed for ${roleUser.name}: ${err.message}`);
        }

        await rolePage.close();
        await roleContext.close();
      }
    } else {
      console.log('\n⚠ No loans in loan-stage-map.json — per-loan + multi-role captures skipped.');
    }
  }

  await browser.close();

  let total = 0;
  for (const vpName of Object.keys(VIEWPORTS)) {
    const dir = path.join(SCREENSHOT_DIR, vpName);
    if (fs.existsSync(dir)) {
      const files = fs.readdirSync(dir).filter(f => f.endsWith('.png'));
      total += files.length;
    }
  }
  console.log(`\n✅ Done! ${total} screenshots saved to screenshots/ (${coverageMode} mode)`);
}

run().catch(err => {
  console.error('Fatal error:', err);
  process.exit(1);
});

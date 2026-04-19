<?php

namespace App\Services;

use App\Models\AppConfig;

class ConfigService
{
    /**
     * Load config from DB, or initialize from defaults.
     *
     * Self-heals drift: when app-defaults.php gains a new top-level key after
     * the DB row was first seeded (e.g. quotationHoldReasons added later),
     * the merge-on-read path silently covers it — but the DB row stays frozen
     * at its original shape, which is misleading and fragile. Detect missing
     * top-level keys and persist the merge back exactly once.
     */
    public function load(): array
    {
        $record = AppConfig::where('config_key', 'main')->first();

        if ($record && ! empty($record->config_json)) {
            $merged = $this->mergeWithDefaults($record->config_json);
            $loaded = is_array($record->config_json) ? $record->config_json : (json_decode($record->config_json, true) ?? []);

            if (array_diff_key($merged, $loaded)) {
                $this->save($merged);
            }

            return $merged;
        }

        // No DB config — initialize from defaults
        $defaults = $this->defaults();
        $this->save($defaults);

        return $defaults;
    }

    /**
     * Save config to DB.
     */
    public function save(array $config): void
    {
        AppConfig::updateOrCreate(
            ['config_key' => 'main'],
            ['config_json' => $config]
        );
    }

    /**
     * Reset config to defaults.
     */
    public function reset(): array
    {
        $defaults = $this->defaults();
        $this->save($defaults);

        return $defaults;
    }

    /**
     * Get a specific config value by dot-notation key.
     */
    public function get(string $key, $default = null)
    {
        $config = $this->load();

        return data_get($config, $key, $default);
    }

    /**
     * Update a specific section of the config.
     */
    public function updateSection(string $section, $value): array
    {
        $config = $this->load();

        // Handle nested keys like 'iomCharges'
        data_set($config, $section, $value);

        $this->save($config);

        return $config;
    }

    /**
     * Update multiple config keys at once.
     */
    public function updateMany(array $updates): array
    {
        $config = $this->load();

        foreach ($updates as $key => $value) {
            data_set($config, $key, $value);
        }

        $this->save($config);

        return $config;
    }

    /**
     * Load defaults directly from the defaults file, bypassing Laravel's
     * `config()` helper so `php artisan config:cache` never freezes the
     * admin-editable defaults. The AppConfig DB row remains the source of
     * truth; this file is only the fallback / seed shape.
     */
    protected function defaults(): array
    {
        return require base_path('config/app-defaults.php');
    }

    /**
     * Merge loaded config with defaults to ensure all keys exist.
     */
    protected function mergeWithDefaults($configJson): array
    {
        $defaults = $this->defaults();
        $loaded = is_array($configJson) ? $configJson : json_decode($configJson, true);

        if (! is_array($loaded)) {
            return $defaults;
        }

        $merged = array_replace_recursive($defaults, $loaded);

        // Sequential arrays (lists) must be replaced entirely from DB,
        // not merged per-index, so deleted items don't reappear from defaults.
        $this->replaceSequentialArrays($merged, $loaded);

        return $merged;
    }

    /**
     * Recursively replace sequential (indexed) arrays in merged config
     * with the DB values, so deletions from defaults are respected.
     */
    protected function replaceSequentialArrays(array &$merged, array $loaded): void
    {
        foreach ($loaded as $key => $value) {
            if (is_array($value)) {
                if (array_is_list($value)) {
                    $merged[$key] = $value;
                } elseif (isset($merged[$key]) && is_array($merged[$key])) {
                    $this->replaceSequentialArrays($merged[$key], $value);
                }
            }
        }
    }
}

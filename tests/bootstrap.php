<?php

/**
 * Minimal PHPUnit bootstrap for wp-easy-customizer.
 *
 * This intentionally does NOT pull in a WordPress runtime (the
 * johnpbloch/wordpress-core dev dependency is only used to feed phpstan
 * with WP stubs). Instead we define the handful of WordPress functions
 * that the library's src/ code actually calls, as minimal
 * passthroughs/recorders, so the classes can be exercised in isolation.
 */

require_once __DIR__ . '/../vendor/autoload.php';

if (!function_exists('sanitize_key')) {
    function sanitize_key($key)
    {
        $key = strtolower((string) $key);

        return preg_replace('/[^a-z0-9_\-]/', '', $key);
    }
}

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $acceptedArgs = 1)
    {
        // No-op recorder: the tests only care about object construction,
        // not about WordPress actually dispatching the hook.
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $acceptedArgs = 1)
    {
        // No-op recorder, see add_action() above.
        return true;
    }
}

if (!function_exists('get_theme_mod')) {
    function get_theme_mod($name, $default = false)
    {
        // No theme mods are set in the test environment, so behave like
        // WordPress does for an unset mod: return the given default.
        return $default;
    }
}

<?php

class WoodyTheme_Logger
{
    // main files
    const DEBUG = 'debug';
    const WARMUP = 'last_warmup';
    const EVENTS = 'events';

    /**
     * Log
     *
     * @param string $message
     */
    public static function log($message, $file = self::DEBUG, $rewrite = false)
    {
        if (!ENABLE_LOG_FILES) {
            return false;
        }
        $file = WP_ROOT_DIR . '/logs/' . $file . '.log';

        if (file_exists($file) && !$rewrite) {
            $existing_log = file_get_contents($file);
        } else {
            $existing_log = '';
        }

        if (is_array($message) || is_object($message)) {
            $message = json_encode($message, true);
        }

        $written = file_put_contents($file, $existing_log . "\n" . date('Y-m-d H:i:s') . ' : ' . $message);
        chmod($file, 0777);
    }


    /**
     * Error
     *
     * @param string $message
     */
    public static function error($message, $file = self::DEBUG, $rewrite = false)
    {
        if (!ENABLE_LOG_FILES) {
            return false;
        }
        $file = WP_ROOT_DIR . '/logs/' . $file . '.log';

        if (file_exists($file) && !$rewrite) {
            $existing_log = file_get_contents($file);
        } else {
            $existing_log = '';
        }

        if (is_array($message) || is_object($message)) {
            $message = json_encode($message, true);
        }

        file_put_contents($file, $existing_log . "\n" . date('Y-m-d H:i:s') . ' : ERROR => ' . $message);
    }
}

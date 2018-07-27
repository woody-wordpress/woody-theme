<?php

class WoodyTheme_Logger
{
    /**
     * Log
     *
     * @param string $message
     */
    public function log($message, $file = 'debug')
    {
        $file = WP_ROOT_DIR . '/logs/' . $file . '.log';

        if (file_exists($file)) {
            $existing_log = file_get_contents($file);
        } else {
            $existing_log = '';
        }

        if (is_array($message)) {
            $message = json_encode($message, true);
        }

        file_put_contents($file, $existing_log . "\n" . date('Y-m-d H:i:s') . ' : ' . $message);
    }
}

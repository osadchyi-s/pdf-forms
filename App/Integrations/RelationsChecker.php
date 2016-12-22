<?php
namespace PdfFormsLoader\Integrations;

class RelationsChecker
{
    protected $plugins;

    public function checker()
    {
        return ($this->pluginsCheck() && true); // Scallable
    }

    protected function pluginsCheck() {
        if (empty($this->plugins)) {
            return true;
        }

        foreach ($this->plugins as $plugin) {
            $check = $this->pluginItemCheck($plugin);
            if (!$check) {
                return false;
            }
        }

        return true;
    }

    protected function pluginItemCheck($plugin) {
        $plugins = get_option('active_plugins');
        return in_array( $plugin , $plugins );
    }
}
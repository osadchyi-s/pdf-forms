<?php
namespace PdfFormsLoader\Integrations;

class IntegrationsAPI
{
    protected static $integrations = [];

    protected function getIntegrations() {
        return self::$integrations;
    }

    protected function addIntegration($integration) {
        if ($integration instanceof Integration) {
            self::$integrations[get_class($integration)] = $integration;
        }

        return $this;
    }

    protected function setIntegrations($integrations) {
        self::$integrations = $integrations;
    }

    public function initIntegrations() {
        $this->setIntegrations(
            apply_filters( 'pdfform_integrations', $this->getIntegrations(), $this )
        );

        foreach($this->getIntegrations() as $class => $integration) {
            if ($integration instanceof Integration) {
                $integration->InitHooks();
            }
        }
    }
}
<?php
namespace PdfFormsLoader\Integrations;

class IntegrationFabric
{
    public static function getIntegration($integration)
    {
        $class = 'PdfFormsLoader\\Integrations\\' . $integration;
        return new $class;
    }
}
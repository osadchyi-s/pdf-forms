<?php

namespace PDFfiller\Validation;


abstract class Rules
{
    const RULES_PATH = "rules.json";

    public static function getRules()
    {
        $rulesJson = file_get_contents(__DIR__ . '/' . self::RULES_PATH);
        return json_decode($rulesJson, true);
    }

    public static function get($endpoints)
    {
        if (is_string($endpoints)) {
            $endpoints = explode('|', $endpoints);
        }

        if (is_array($endpoints)) {
            return self::mergeRules($endpoints);
        }

        return [];
    }

    private static function mergeRules($endpoints)
    {
        $all= self::getRules();
        $rules = [];
        foreach ($endpoints as $endpoint) {
            if (isset($all[$endpoint])) {
                $rules = array_merge($rules, $all[$endpoint]);
            }
        }

        return $rules;
    }
}
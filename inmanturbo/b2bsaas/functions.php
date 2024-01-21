<?php

namespace Inmanturbo\B2bSaas;

use Illuminate\Support\Facades\Http;

/**
 * Use laravel http client to check if domain supports https
 */
function preferHttps(string $domainName): string
{
    try {
        $response = Http::get("https://{$domainName}");
        if ($response && $response->status() === 200) {
            return "https://{$domainName}";
        }
    } catch (\Exception $e) {
        //do nothing
    }

    return "http://{$domainName}";
}

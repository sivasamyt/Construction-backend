<?php

namespace App\Services;

use App\Models\Domain;

class DomainSlugService
{
    public function generate(string $companyName): string
    {
        $name = trim($companyName);

        if ($name === '') {
            return '';
        }

        if (preg_match('/\s/u', $name)) {
            $slug = preg_replace('/\s+/u', '-', $name);
        } else {
            $slug = $name;
        }

        $slug = mb_strtolower($slug, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9-]/', '', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }

    public function isAvailable(string $domain): bool
    {
        if ($domain === '') {
            return false;
        }

        return ! Domain::where('domain', $domain)->exists();
    }

    public function makeUnique(string $baseSlug): string
    {
        if ($baseSlug === '') {
            return $baseSlug;
        }

        $slug = $baseSlug;
        $counter = 1;

        while (! $this->isAvailable($slug)) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function buildUrl(string $domain): string
    {
        $baseUrl = rtrim(config('app.url'), '/');

        return "{$baseUrl}/{$domain}";
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ThemeService
{
    /**
     * Resolve the inertia component to use for a tenant's landing page.
     * It checks if a bespoke theme is defined and the component exists.
     * Otherwise, it falls back to the standard Layouts.
     */
    public static function resolveLandingComponent($tenant): string
    {
        $themeName = $tenant->theme_name;
        
        if ($themeName) {
            $studlyTheme = Str::studly($themeName);
            $bespokePath = resource_path("js/Pages/Bespoke/{$studlyTheme}/Index.vue");
            
            if (File::exists($bespokePath)) {
                return "Bespoke/{$studlyTheme}/Index";
            }
        }
        
        $layout = $tenant->layout_id ?? 'HeroFirst';
        return "Tenant/Layouts/{$layout}";
    }
}

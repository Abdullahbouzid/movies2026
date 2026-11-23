<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // @versioned('css/movies.css') → /css/movies.css?v=<filemtime>
        Blade::directive('versioned', fn (string $expression) => "<?php echo e(\\App\\Support\\AssetVersion::url({$expression})); ?>");
    }
}

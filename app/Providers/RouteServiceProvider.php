<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';
    protected $mobileNamespace = 'App\Mobile\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapCmsRoutes();

        $this->mapMobileRoutes();

        $this->mapAdminRoutes();
    }

    protected function mapCmsRoutes()
    {
        Route::prefix('cms')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/cms.php'));
    }

    protected function mapMobileRoutes()
    {
        Route::prefix('mobile')
            ->middleware('api')
            // ->namespace($this->mobileNamespace)
            ->group(base_path('routes/mobile.php'));
    }

    protected function mapAdminRoutes()
    {
        Route::prefix('admin')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/admin.php'));
    }

    protected function mapApiRoutes()
    {
        Route::middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }
}

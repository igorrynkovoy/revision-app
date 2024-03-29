<?php

namespace App\Providers;

use App\Models\Blockchain\DepthSync;
use App\Models\Workspace;
use App\Repositories\Interfaces\WorkspaceRepositoryInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->namespace('App\Http\Controllers\Api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        Route::bind('rootDepthSync', function ($value) {
            return DepthSync::query()
                ->whereNull('root_sync_id')
                ->where('id', $value)
                ->firstOrFail();
        });

        Route::bind('boardLayout', function ($value) {
            return Workspace\Board\BoardLayout::query()
                ->where('id', $value)
                ->firstOrFail();
        });

        Route::bind('boardJob', function ($value) {
            return Workspace\Board\BoardJob::query()
                ->where('id', $value)
                ->firstOrFail();
        });

        Route::bind('workspace', function ($value) {
            /** @var WorkspaceRepositoryInterface $repository */
            $repository = app(WorkspaceRepositoryInterface::class);
            return $repository->getById($value);
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}

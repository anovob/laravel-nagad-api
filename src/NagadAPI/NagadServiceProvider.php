<?php 
namespace NagadAPI;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;

class NagadServiceProvider extends ServiceProvider
{    
    /**
     * boot
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Publishes the configuration file
         */
        $this->publishes([
            __DIR__ . '/config/nagad.php' => config_path('nagad.php'),
        ], 'nagad-config');

        /**
         * Loads the Facade
         */
        AliasLoader::getInstance()->alias('Nagad', 'NagadAPI\Facades\Nagad');
    }
    
    /**
     * register
     *
     * @return void
     */
    public function register()
    {
        /**
         * Registers the Facade Binding
         */
        $this->app->bind('nagad', function () {
            return new Nagad();
        });
    }
}
<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Registered;
use App\Listeners\RegisteredListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
        Registered::class => [
            RegisteredListener::class
        ],
        \App\Events\OrderPaid::class => [
            \App\Listeners\UpdateProductSoldCount::class,
            \App\Listeners\SendOrderPaidMail::class
        ],
        \App\Events\OrderReviewed::class => [
            \App\Listeners\UpdateProductRating::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}

<?php

use Illuminate\Auth\Access\AuthorizationException;
use Imanghafoori\HeyMan\Facades\HeyMan;
use Imanghafoori\HeyMan\WatchingStrategies\EventManager;

class EventsAuthorizationTest extends TestCase
{
    public function testEventIsAuthorized1()
    {
        HeyMan::whenEventHappens(['myEvent', 'myEvent1'])->youShouldHaveRole('reader')->otherwise()->weDenyAccess();
        HeyMan::whenEventHappens('myEvent4')->youShouldHaveRole('reader')->otherwise()->weDenyAccess();
        app(EventManager::class)->start();

        Heyman::makeSure($this)->whenEventHappens('myEvent1')->exceptionIsThrown(AuthorizationException::class);
    }

    public function testEventIsAuthorized2()
    {
        HeyMan::whenEventHappens(['myEvent', 'myEvent1'])->always()->weDenyAccess();
        HeyMan::whenEventHappens('myEvent4')->thisValueShouldAllow(true)->otherwise()->weDenyAccess();
        app(EventManager::class)->start();
        $this->expectException(AuthorizationException::class);

        event('myEvent1');
    }

    public function test_Event_Is_forgotten()
    {
        HeyMan::whenEventHappens(['myEvent', 'myEvent1'])->always()->weDenyAccess();
        HeyMan::whenEventHappens('myEvent4')->thisValueShouldAllow(true)->otherwise()->weDenyAccess();
        HeyMan::whenEventHappens('myEvent4')->thisValueShouldAllow(true)->otherwise()->weThrowNew(\Illuminate\Validation\UnauthorizedException::class);
        HeyMan::forget()->aboutEvent(['myEvent', 'myEvent1', 'myEvent4']);
        app(EventManager::class)->start();

        event('myEvent');
        event('myEvent1');
        event('myEvent4');
        $this->assertTrue(true);
    }

    public function test_Event_Is_forgotten2()
    {
        HeyMan::whenEventHappens(['myEvent', 'myEvent1'])->always()->weDenyAccess();
        HeyMan::whenEventHappens('myEvent4')->thisValueShouldAllow(true)->otherwise()->weDenyAccess();
        HeyMan::whenEventHappens('myEvent4')->thisValueShouldAllow(true)->otherwise()->weThrowNew(\Illuminate\Validation\UnauthorizedException::class);
        HeyMan::forget()->aboutEvent('myEvent4');
        app(EventManager::class)->start();

        event('myEvent4');

        $this->assertTrue(true);
    }

    public function testAlways()
    {
        Route::get('/event/{event}', function ($event) {
            event($event);
        });

        HeyMan::whenEventHappens(['my-event', 'myEvent1'])->always()->abort(402);
        app(EventManager::class)->start();

        Heyman::makeSure($this)
            ->sendingGetRequest('event/my-event')
            ->isRespondedWith()
            ->statusCode(402);
    }

    public function testAlways1()
    {
        Route::get('/event/{event}', function ($event) {
            event($event);
        });

        HeyMan::whenEventHappens(['my-event', 'myEvent1'])->always()->redirect()->to('welcome');
        app(EventManager::class)->start();

        Heyman::makeSure($this)
            ->sendingGetRequest('event/my-event')
            ->isRespondedWith()
            ->redirect('/welcome', 302);
    }

    public function testCheckPoints()
    {
        Heyman::whenYouReachCheckPoint('*AreYou')->always()->redirect()->to('welcome');
        app(EventManager::class)->start();

        \Route::get('oh', function () {
            Heyman::checkPoint('whoAreYou');
        });

        Heyman::makeSure($this)
            ->sendingGetRequest('oh')
            ->isRespondedWith()
            ->redirect('/welcome', 302);
    }
}

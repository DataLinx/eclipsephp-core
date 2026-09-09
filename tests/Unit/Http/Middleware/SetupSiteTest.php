<?php

namespace Tests\Unit\Http\Middleware;

use Eclipse\Core\Http\Middleware\SetupSite;
use Eclipse\Core\Models\Site;
use Eclipse\Core\Services\Registry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

test('it logs error and aborts 404 when site is not found for host', function () {
    Log::spy();

    $request = Request::create('https://unknown-domain.test');
    $middleware = new SetupSite;

    expect(fn () => $middleware->handle($request, fn () => new Response))
        ->toThrow(HttpException::class, '');

    Log::shouldHaveReceived('error')
        ->once()
        ->with('Site not found for host: {host}', ['host' => 'unknown-domain.test']);
});

test('it sets current site in registry when site exists', function () {
    $site = Site::first();

    $request = Request::create("https://{$site->domain}/test");
    $middleware = new SetupSite;

    $response = $middleware->handle($request, function ($req) {
        return new Response('OK');
    });

    expect($response->getContent())->toBe('OK')
        ->and(Registry::getSite()->id)->toBe($site->id);
});

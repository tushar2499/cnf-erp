<?php

namespace Tests\Feature;

use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ExpiredSessionRedirectTest extends TestCase
{
    public function test_expired_csrf_redirects_to_login_page(): void
    {
        Route::post('/csrf-expired-test', fn () => throw new TokenMismatchException('CSRF token mismatch.'));

        $this->post('/csrf-expired-test')
            ->assertRedirect(route('login'));
    }

    public function test_expired_csrf_on_json_request_returns_419_json(): void
    {
        Route::post('/csrf-expired-test', fn () => throw new TokenMismatchException('CSRF token mismatch.'));

        $this->postJson('/csrf-expired-test')
            ->assertStatus(419)
            ->assertJsonPath('message', 'CSRF token mismatch.');
    }
}

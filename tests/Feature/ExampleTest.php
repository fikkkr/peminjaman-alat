<?php
namespace Tests\Feature;
use Tests\TestCase;
class ExampleTest extends TestCase
{
    public function test_guest_root_redirects_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }
}

<?php

namespace Tests\Feature\Livewire;

use App\Livewire\UserForm;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class UserFormTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(UserForm::class)
            ->assertStatus(200);
    }
}

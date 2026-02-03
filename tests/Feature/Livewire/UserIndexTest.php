<?php

namespace Tests\Feature\Livewire;

use App\Livewire\UserIndex;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class UserIndexTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(UserIndex::class)
            ->assertStatus(200);
    }
}

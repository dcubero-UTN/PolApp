<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserForm extends Component
{
    public ?User $user = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $is_editing = false;

    public function mount(?User $user = null)
    {
        if ($user && $user->exists) {
            $this->user = $user;
            $this->name = $user->name;
            $this->email = $user->email;
            $this->is_editing = true;
        }
    }

    public function save()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                $this->is_editing ? Rule::unique('users')->ignore($this->user->id) : 'unique:users',
            ],
        ];

        if (!$this->is_editing || $this->password) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $this->validate($rules);

        if ($this->is_editing) {
            $this->user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            if ($this->password) {
                $this->user->update(['password' => Hash::make($this->password)]);
            }

            session()->flash('message', 'Vendedor actualizado correctamente.');
        } else {
            $newUser = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
            ]);

            $newUser->assignRole('vendedor');

            session()->flash('message', 'Vendedor creado correctamente.');
        }

        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.user-form')->layout('layouts.app');
    }
}

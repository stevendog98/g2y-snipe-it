<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;

class PersonalAccessTokens extends Component
{
    public function render()
    {
        return view('livewire.personal-access-tokens', [
            'tokens' => Auth::user()->tokens,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'scopes' => 'nullable|array',
        ];
    }

    public function createToken(): void
    {
       Auth::user()->createToken($this->name, $this->scopes);
    }

    public function deleteToken($tokenId): void
    {
        Log::info('poo');
        //this needs safety (though the scope of auth::user might kind of do it...)
        //seems like it does, test more
        Auth::user()->tokens()->find($tokenId)->delete();
    }

    public function getTokensProperty(): array
    {
        return Auth::user()->tokens;
    }
}

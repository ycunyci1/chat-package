<?php
declare(strict_types=1);

namespace Dd1\Chat\Controllers;

use Dd1\Chat\Services\JwtService;

class UserController extends Controller
{
    public function getUserInfo()
    {
        $user = auth()->user();
        return response()->json([
            'id' => $user->id,
            'avatar' => $user->avatar,
            'name' => $user->name
        ]);
    }

    public function updateCentrifugoToken()
    {
        return response()->json([
            'centrifugo_token' => JwtService::generateJwt(auth()->id())
        ]);
    }
}

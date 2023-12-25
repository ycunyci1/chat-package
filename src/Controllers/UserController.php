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
            'avatar' => $user->avatar,
            'name' => $user->name
        ]);
    }
}

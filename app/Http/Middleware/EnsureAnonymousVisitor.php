<?php

namespace App\Http\Middleware;

use App\Models\Visitor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureAnonymousVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        $visitorUuid = $request->cookie('visitor_uuid');
        if (! is_string($visitorUuid) || ! Str::isUuid($visitorUuid)) {
            $visitorUuid = (string) Str::uuid();
        }

        $visitor = Visitor::query()->firstOrCreate(['uuid' => $visitorUuid]);
        $request->attributes->set('visitor', $visitor);

        return $next($request)->withCookie(cookie()->forever('visitor_uuid', $visitorUuid, secure: $request->isSecure(), httpOnly: true, sameSite: 'lax'));
    }
}

<?php

namespace App\Core;

interface Middleware
{
    /**
     * Handle middleware logic
     *
     * @param Request $request
     * @param callable $next
     * @return void
     */
    public function handle(Request $request, callable $next): void;
}

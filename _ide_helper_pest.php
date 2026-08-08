<?php

/**
 * IDE-only stubs for Pest + Laravel TestCase.
 * Not autoloaded at runtime — Intelephense/PhpStorm read this for `$this` types.
 */

use Closure;
use Pest\Configuration;
use Pest\PendingCalls\AfterEachCall;
use Pest\PendingCalls\BeforeEachCall;
use Pest\PendingCalls\TestCall;
use Pest\PendingCalls\UsesCall;
use Pest\Support\HigherOrderTapProxy;
use Tests\TestCase;

/**
 * @param-closure-this TestCase $closure
 *
 * @return ($description is Closure ? HigherOrderTapProxy|TestCase : TestCall)
 */
function test(string|Closure|null $description = null, ?Closure $closure = null): TestCall|HigherOrderTapProxy|TestCase
{
    throw new RuntimeException('IDE helper only.');
}

/**
 * @param-closure-this TestCase $closure
 */
function it(string $description, ?Closure $closure = null): TestCall
{
    throw new RuntimeException('IDE helper only.');
}

/**
 * @param-closure-this TestCase $closure
 */
function beforeEach(?Closure $closure = null): BeforeEachCall
{
    throw new RuntimeException('IDE helper only.');
}

/**
 * @param-closure-this TestCase $closure
 */
function afterEach(?Closure $closure = null): AfterEachCall
{
    throw new RuntimeException('IDE helper only.');
}

function uses(string ...$classAndTraits): UsesCall
{
    throw new RuntimeException('IDE helper only.');
}

function pest(): Configuration
{
    throw new RuntimeException('IDE helper only.');
}

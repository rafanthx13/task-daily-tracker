<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Classe-base dos testes da aplicação.
 *
 * Centraliza o bootstrap definido em CreatesApplication para que os testes de
 * Feature possam usar os helpers HTTP do Laravel, como post(), putJson() e deleteJson().
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
}

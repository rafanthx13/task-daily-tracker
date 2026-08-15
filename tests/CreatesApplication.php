<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;

/**
 * Inicializa uma instância isolada da aplicação Laravel para cada execução de teste.
 */
trait CreatesApplication
{
    /**
     * Carrega a aplicação definida em bootstrap/app.php e executa seu bootstrap,
     * disponibilizando container, rotas, banco configurado para teste e demais serviços.
     */
    public function createApplication(): \Illuminate\Foundation\Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}

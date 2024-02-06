<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeneralJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $controllerClass;
    protected $methodName;
    protected $params;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($controllerClass, $methodName, $params)
    {
        $this->controllerClass = $controllerClass;
        $this->methodName      = $methodName;
        $this->params          = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Asegúrate de que la clase del controlador exista y sea válida
        if (!class_exists($this->controllerClass)) {
            // Maneja el error adecuadamente
            return;
        }

        $controller = resolve($this->controllerClass);
        Log::info('GeneralJob: 3');

        // Verifica si el método existe en la clase
        if (!method_exists($controller, $this->methodName)) {
            // Maneja el error adecuadamente
            return;
        }

        // Llama al método de manera dinámica
        call_user_func_array([$controller, $this->methodName], $this->params);
    }
}

<?php

namespace App\Console\Commands\Texts;

use App\Http\Controllers\Processes\StudentsExcelController;
use Carbon\Carbon;
use Illuminate\Console\Command;

class FreeCoursesCompletedText extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-complete-free-courses-text';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return any
     */
    public function handle($students = null)
    {

        // return $this->line(json_encode("Hola mundo"));

        if (!$students) {
            $data = new StudentsExcelController();
            $students = $data->index('test');
        }

        return $this->line(json_encode($students));
    }
}

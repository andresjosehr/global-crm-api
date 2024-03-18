<?php

namespace App\Console\Commands\crm;

use App\Models\Wordpress\WpUser;
use Illuminate\Console\Command;

class SetWpId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:set-wp-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $students = \App\Models\Student::all();
        $students->each(function ($student) {

            $wp_user = WpUser::where('user_email', $student->email)->first();

            if ($wp_user) {
                $student->wp_user_id = $wp_user->ID;
                $student->save();
            }
        });
    }
}

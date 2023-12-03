<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalesActivitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // truncate table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('sales_activities')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $salesUsers = User  ::where('role_id', 2)->with('leadAssignments')->get();

        foreach($salesUsers as $user){
            foreach($user->leadAssignments as $leadAssignment){
                $salesActivity = [
                    'type' => 'call',
                    'start' => $leadAssignment->created_at,
                    'end' => $leadAssignment->created_at,
                    'user_id' => $user->id,
                    'lead_id' => $leadAssignment->lead_id,
                    'lead_assignment_id' => $leadAssignment->id,
                ];
                DB::table('sales_activities')->insert($salesActivity);
            }

        }

    }
}

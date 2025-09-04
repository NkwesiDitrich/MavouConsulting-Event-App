<?php

namespace Database\Seeders;

use App\Models\Attendee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::all();
        $events = \App\Models\Event::all();

       foreach ($users as $user) {
    $max = min(3, $events->count());
    if ($max === 0) {
        continue; // No events to assign
    }
    $eventsToAttend = $events->random(rand(1, $max));

    foreach ($eventsToAttend as $event) {
        Attendee::create([
            'user_id' => $user->id,
            'event_id' => $event->id
        ]);
    }
}
    }
}
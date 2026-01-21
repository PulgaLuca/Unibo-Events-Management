<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class EventSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     */
public function run(): void
    {
        /*
         * EVENT_TYPE
         */
        $eventTypes = [
            ['id' => 'ET001', 'name' => 'Internal Hackathon'],
            ['id' => 'ET002', 'name' => 'Student Workshop'],
            ['id' => 'ET003', 'name' => 'Study Group'],
            ['id' => 'ET004', 'name' => 'External Competition'],
            ['id' => 'ET005', 'name' => 'Tech Talk'],
        ];

        $this->table('event_type')->insert($eventTypes)->saveData();

        /*
         * PARTICIPATION_TYPE
         */
        $participationTypes = [
            ['id' => 'PT001', 'name' => 'On-site'],
            ['id' => 'PT002', 'name' => 'Remote'],
            ['id' => 'PT003', 'name' => 'Hybrid'],
        ];

        $this->table('participation_type')->insert($participationTypes)->saveData();

        /*
         * TEAM
         */
        $teams = [
            [
                'id' => 'TM001',
                'name' => 'Unibois',
                'description' => 'Team focused on competitive programming',
                'status' => 'Searching',
                'max_participants' => 5,
                'min_participants' => 3,
                'mentor_id' => 1,
            ],
        ];

        $this->table('team')->insert($teams)->saveData();

        /*
         * EVENT
         */
        $events = [
            [
                'id' => 'EV001',
                'title' => 'Unibo Internal Hackathon 2026',
                'description' => '24h hackathon for university students',
                'start_date' => '2026-03-10 09:00:00',
                'end_date' => '2026-03-11 09:00:00',
                'image_url' => '/assets/images/events/bologna-hack.jpg',
                'location' => 'Bologna Campus',
                'min_participants' => 10,
                'max_participants' => 50,
                'status' => 'Active',
                'type_id' => 'ET001',
                'participation_type_id' => 'PT003',
                'creator_user_id' => 1,
            ],
            [
                'id' => 'EV002',
                'title' => 'Advanced PHP Workshop',
                'description' => 'Hands-on workshop on modern PHP',
                'start_date' => '2026-04-05 14:00:00',
                'image_url' => '/assets/images/events/web.jpg',
                'status' => 'Draft',
                'type_id' => 'ET002',
                'participation_type_id' => 'PT001',
                'creator_user_id' => 2,
            ],
        ];

        $this->table('event')->insert($events)->saveData();

        /*
         * EVENT_REQUIRED_SKILL
         */
        $eventSkills = [
            [
                'event_id' => 'EV001',
                'skill_id' => 1,
            ],
            [
                'event_id' => 'EV002',
                'skill_id' => 2,
            ],
        ];

        $this->table('event_required_skill')->insert($eventSkills)->saveData();

        /*
         * EVENT_PARTICIPATION
         */
        $eventParticipations = [
            [
                'id' => 'EP001',
                'event_id' => 'EV001',
                'user_id' => 1,
                'team_id' => null,
                'role' => 'Lead',
            ],
            [
                'id' => 'EP002',
                'event_id' => 'EV001',
                'user_id' => null,
                'team_id' => 'TM001',
                'role' => 'Participant',
            ],
        ];

        $this->table('event_participation')->insert($eventParticipations)->saveData();
    }
}

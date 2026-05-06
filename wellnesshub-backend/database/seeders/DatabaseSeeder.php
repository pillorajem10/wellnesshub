<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Protocol;
use App\Models\Review;
use App\Models\Thread;
use App\Models\User;
use App\Models\Vote;
use App\Services\TypesenseService;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Demo login (documented in README):
     * - Email: demo@wellnesshub.test
     * - Password: user123
     *
     * All seeded users share the same password for local testing.
     */
    public function run(): void
    {
        $demo = User::query()->create([
            'tbl_user_fname' => 'Demo',
            'tbl_user_lname' => 'User',
            'tbl_user_email' => 'demo@wellnesshub.test',
            'tbl_user_password' => Hash::make('user123'),
        ]);

        $others = User::factory(7)->create();
        $users = collect([$demo])->merge($others);

        /**
         * Topic-aware seeding: each protocol gets related threads, comments (with replies), and reviews.
         * Votes are added only after all threads/comments exist to avoid orphans.
         *
         * @var array<int, array<string, mixed>> $protocolSeeds
         */
        $protocolSeeds = [
            [
                'title' => 'Better Sleep Routine Protocol',
                'tags' => ['sleep', 'recovery', 'circadian-rhythm', 'bedtime'],
                'content' => "A practical bedtime routine to improve sleep quality and consistency.\n\nCore steps:\n- Set a consistent sleep and wake time.\n- Reduce bright screens 60–90 minutes before bed.\n- Keep the room cool, dark, and quiet.\n- Use a short wind-down (reading, stretching, or breathing).\n\nNotes:\nIf you miss a night, reset the next evening—consistency matters more than perfection.",
                'threads' => [
                    [
                        'title' => 'Did the no-screen rule actually help you fall asleep faster?',
                        'body' => 'I’m trying the "no screens 60 minutes before bed" step. For people who stuck with it for a week, did it help with falling asleep or staying asleep?',
                        'tags' => ['sleep', 'bedtime', 'habits', 'screens'],
                        'comments' => [
                            [
                                'body' => 'Yes. Moving my phone charger out of the bedroom was the turning point. I stopped doom-scrolling and fell asleep faster by night three.',
                                'replies' => [
                                    'Same here. I also switched to a paperback book so I wasn’t tempted to “just check one more thing.”',
                                ],
                            ],
                            [
                                'body' => 'It helped more with staying asleep. My sleep felt deeper and I woke up fewer times.',
                                'replies' => [],
                            ],
                            [
                                'body' => 'I struggled at first. Setting an app timer and dimming the lights made it easier to transition.',
                                'replies' => [
                                    'The light dimming tip is underrated. Warm lamps + no overhead lights made me feel sleepy much earlier.',
                                ],
                            ],
                        ],
                    ],
                ],
                'reviews' => [
                    ['rating' => 5, 'feedback' => 'Simple steps that feel realistic. The screen cut-off and consistent wake time improved my sleep within a week.'],
                    ['rating' => 4, 'feedback' => 'Good structure. I’d add a reminder about caffeine timing, but the wind-down routine was very effective.'],
                    ['rating' => 4, 'feedback' => 'Helpful for consistency. The environment tips (cool/dark room) made a bigger difference than I expected.'],
                ],
            ],
            [
                'title' => 'Morning Sunlight Exposure Protocol',
                'tags' => ['circadian-rhythm', 'energy', 'morning', 'wellness'],
                'content' => "Use morning light to anchor your circadian rhythm and improve daytime energy.\n\nCore steps:\n- Go outside within 60 minutes of waking.\n- Aim for 5–15 minutes of daylight (longer on cloudy days).\n- Keep eyes safe: never stare at the sun.\n\nNotes:\nConsistency beats intensity. Pair it with a short walk for an easy habit stack.",
                'threads' => [
                    [
                        'title' => 'Morning light: how long is enough on cloudy days?',
                        'body' => 'On sunny days I do 8–10 minutes outside, but on cloudy days it feels like nothing changes. How long are you staying out when it’s overcast?',
                        'tags' => ['morning', 'sunlight', 'circadian-rhythm'],
                        'comments' => [
                            [
                                'body' => 'I double it on cloudy days. Around 20 minutes while walking is my sweet spot.',
                                'replies' => [
                                    'Same. A brisk walk also wakes me up more than standing still.',
                                ],
                            ],
                            [
                                'body' => 'Even 10 minutes helps if I do it consistently. The big change for me was doing it right after waking, not later.',
                                'replies' => [],
                            ],
                        ],
                    ],
                ],
                'reviews' => [
                    ['rating' => 5, 'feedback' => 'Easy win. Morning light + short walk improved my morning energy and sleep timing.'],
                    ['rating' => 4, 'feedback' => 'Good reminders about eye safety and consistency. Works best when I do it immediately after waking.'],
                ],
            ],
            [
                'title' => 'Beginner Cold Shower Protocol',
                'tags' => ['cold-exposure', 'recovery', 'breathing', 'safety'],
                'content' => "A gradual approach to cold exposure for beginners.\n\nCore steps:\n- Start warm, finish with 15–30 seconds cold.\n- Increase cold time slowly over 2–4 weeks.\n- Use calm nasal breathing and long exhales.\n\nSafety:\nStop if you feel lightheaded. Avoid if you have contraindications—when in doubt, ask a clinician.",
                'threads' => [
                    [
                        'title' => 'Best way to ease in without panicking?',
                        'body' => 'I can handle about 10 seconds cold before I tense up. Any tips to stay calm and build tolerance without forcing it?',
                        'tags' => ['cold-exposure', 'breathing', 'beginner'],
                        'comments' => [
                            [
                                'body' => 'Long exhales helped me the most. I count a slow 4-second inhale and 6–8 second exhale while the water is cold.',
                                'replies' => [
                                    'Same. I also start with cold on my legs first and finish on the shoulders so it feels less shocking.',
                                ],
                            ],
                            [
                                'body' => 'I progressed by 5 seconds every few days. Keeping it “easy enough” made me stick with it.',
                                'replies' => [],
                            ],
                        ],
                    ],
                ],
                'reviews' => [
                    ['rating' => 5, 'feedback' => 'Great beginner pacing. The warm-first approach plus breathing cues made it doable.'],
                    ['rating' => 4, 'feedback' => 'Helpful and safety-conscious. I liked the slow progression instead of “just jump in.”'],
                    ['rating' => 4, 'feedback' => 'Solid protocol. The breathing focus kept me from tensing up so much.'],
                ],
            ],
            [
                'title' => 'Stress Relief Breathing Protocol',
                'tags' => ['breathing', 'stress', 'calm', 'nervous-system'],
                'content' => "Breathing drills to downshift stress quickly.\n\nCore steps:\n- Box breathing (4-4-4-4) for 2–3 minutes.\n- Or try longer exhales (inhale 4, exhale 6–8).\n- Use it before meetings, after conflict, or when you feel “wired.”\n\nNotes:\nIf you feel dizzy, reduce the breath holds and slow down.",
                'threads' => [
                    [
                        'title' => 'Box breathing vs longer exhales: which works better for you?',
                        'body' => 'I like box breathing but sometimes the breath-hold feels uncomfortable. Do longer exhales work better for anyone?',
                        'tags' => ['breathing', 'stress', 'relaxation'],
                        'comments' => [
                            [
                                'body' => 'Longer exhales are smoother for me. Breath holds can make me feel more tense if I’m already anxious.',
                                'replies' => [
                                    'Same. Inhale 4 / exhale 8 is my go-to when I feel my heart racing.',
                                ],
                            ],
                            [
                                'body' => 'Box breathing helps me focus when I’m scattered, but I shorten the holds to 2 seconds if I feel uncomfortable.',
                                'replies' => [],
                            ],
                            [
                                'body' => 'I use longer exhales at night and box breathing during the day. Different tools for different moments.',
                                'replies' => [],
                            ],
                        ],
                    ],
                ],
                'reviews' => [
                    ['rating' => 5, 'feedback' => 'Fast and practical. Longer exhales helped me calm down during stressful work days.'],
                    ['rating' => 4, 'feedback' => 'Clear steps and good safety note about dizziness. Box breathing is great for focus.'],
                ],
            ],
            [
                'title' => 'Hydration Reset Protocol',
                'tags' => ['hydration', 'electrolytes', 'energy', 'habits'],
                'content' => "Build a consistent hydration routine.\n\nCore steps:\n- Start your morning with a full glass of water.\n- Add electrolytes if you sweat a lot or train often.\n- Watch for signs of dehydration (headache, dark urine, fatigue).\n\nNotes:\nHydration is easier when you pair it with existing habits (after brushing teeth, before coffee, after lunch).",
                'threads' => [
                    [
                        'title' => 'Electrolytes every day or only after workouts?',
                        'body' => 'I’m trying to increase water intake. Do you use electrolytes daily or only on training days? Any signs you watch for?',
                        'tags' => ['hydration', 'electrolytes', 'recovery'],
                        'comments' => [
                            [
                                'body' => 'Only on workout days for me. If I do it daily I feel like I retain too much water.',
                                'replies' => [
                                    'Interesting—same. I focus on consistent water first and add electrolytes only when I sweat a lot.',
                                ],
                            ],
                            [
                                'body' => 'I use a small amount daily because I drink a lot of coffee. It reduced headaches for me.',
                                'replies' => [],
                            ],
                        ],
                    ],
                ],
                'reviews' => [
                    ['rating' => 4, 'feedback' => 'Good habit stacking tips. The dehydration signs list was helpful and practical.'],
                    ['rating' => 5, 'feedback' => 'Simple and effective. Morning water + a bottle at my desk fixed my afternoon fatigue.'],
                ],
            ],
            [
                'title' => 'Mindful Eating Protocol',
                'tags' => ['mindful-eating', 'digestion', 'habits', 'wellness'],
                'content' => "A protocol for eating more slowly and noticing hunger cues.\n\nCore steps:\n- Eat without screens for at least one meal per day.\n- Put utensils down between bites.\n- Check in: hungry, satisfied, or full?\n\nNotes:\nStart with lunch or dinner. It’s easier when you’re not rushing out the door.",
                'threads' => [
                    [
                        'title' => 'How do you stop eating too fast during work lunches?',
                        'body' => 'I notice I inhale lunch while answering messages. Any strategies that helped you slow down without feeling annoyed?',
                        'tags' => ['mindful-eating', 'habits', 'focus'],
                        'comments' => [
                            [
                                'body' => 'I set a 15-minute “no email” block for lunch. Even once a day helped me slow down.',
                                'replies' => [
                                    'That’s smart. I started by eating away from my desk and it made a big difference.',
                                ],
                            ],
                            [
                                'body' => 'Chewing a bit more and taking sips of water between bites helped me feel satisfied sooner.',
                                'replies' => [],
                            ],
                        ],
                    ],
                ],
                'reviews' => [
                    ['rating' => 5, 'feedback' => 'Practical. The “one screen-free meal” idea is easy to adopt and helped my digestion.'],
                    ['rating' => 4, 'feedback' => 'Helpful cues and simple steps. Works best when I plan lunch instead of grabbing something fast.'],
                ],
            ],
            [
                'title' => 'Evening Digital Detox Protocol',
                'tags' => ['digital-detox', 'sleep', 'focus', 'habits'],
                'content' => "Reduce evening screen time to support sleep and focus.\n\nCore steps:\n- Set an evening “screens off” window.\n- Use app limits for social apps.\n- Replace screens with a relaxing activity (reading, journaling, stretching).\n\nNotes:\nStart with 30 minutes. Increase when it feels stable.",
                'threads' => [
                    [
                        'title' => 'What do you do instead of scrolling at night?',
                        'body' => 'I want to stop scrolling in bed, but I don’t know what to replace it with. What routines actually stick for you?',
                        'tags' => ['digital-detox', 'sleep', 'habits'],
                        'comments' => [
                            [
                                'body' => 'I keep a book on my pillow so I see it first. If I start reading, I fall asleep faster.',
                                'replies' => [
                                    'I do the same but with a simple journal. Writing 3 quick bullets clears my mind.',
                                ],
                            ],
                            [
                                'body' => 'Stretching for 5 minutes helped. It gave me something “active” to do without a screen.',
                                'replies' => [],
                            ],
                            [
                                'body' => 'Charging my phone in the kitchen was the biggest win. If it’s not in reach, I don’t scroll.',
                                'replies' => [],
                            ],
                        ],
                    ],
                ],
                'reviews' => [
                    ['rating' => 5, 'feedback' => 'Excellent habit framing. The “replace with something relaxing” step made it realistic.'],
                    ['rating' => 4, 'feedback' => 'Helpful for boundaries. App limits + phone out of the bedroom reduced late-night scrolling.'],
                ],
            ],
            [
                'title' => 'Gentle Mobility Protocol',
                'tags' => ['mobility', 'stretching', 'lower-back', 'hips'],
                'content' => "A gentle mobility routine for stiff neck, shoulders, hips, and lower back.\n\nCore steps:\n- 5 minutes daily of easy joint circles.\n- Hip flexor and hamstring stretches.\n- Neck and shoulder mobility without forcing range.\n\nNotes:\nMove slowly and stay below pain. Consistency is more important than intensity.",
                'threads' => [
                    [
                        'title' => 'Any favorite stretches for tight hips after sitting all day?',
                        'body' => 'My hips feel stiff after long desk days. Which stretches helped you the most without aggravating your back?',
                        'tags' => ['mobility', 'hips', 'desk-life'],
                        'comments' => [
                            [
                                'body' => 'A gentle hip flexor stretch plus a short walk helped me. I keep it mild and focus on breathing.',
                                'replies' => [
                                    'The walk is a great idea. I also do a few slow lunges to loosen up without pushing too far.',
                                ],
                            ],
                            [
                                'body' => 'Figure-4 stretch worked for me, but I stop before it turns into pain. Slow is the key.',
                                'replies' => [],
                            ],
                        ],
                    ],
                ],
                'reviews' => [
                    ['rating' => 4, 'feedback' => 'Good gentle approach. The reminder to stay below pain keeps it safe and consistent.'],
                    ['rating' => 5, 'feedback' => 'Perfect for desk stiffness. Five minutes a day was enough to feel looser within a week.'],
                ],
            ],
            [
                'title' => 'Focus and Productivity Protocol',
                'tags' => ['productivity', 'deep-work', 'planning', 'habits'],
                'content' => "A simple productivity protocol built around planning and deep work.\n\nCore steps:\n- Choose 1–3 priorities for the day.\n- Time block a deep work session (30–90 minutes).\n- Use short breaks and reduce notifications.\n\nNotes:\nTrack what distracted you. Fix one distraction at a time.",
                'threads' => [
                    [
                        'title' => 'Time blocking: do you plan the night before or in the morning?',
                        'body' => 'I’m experimenting with time blocking. Do you plan your deep work the night before, or do it first thing in the morning?',
                        'tags' => ['productivity', 'planning', 'time-blocking'],
                        'comments' => [
                            [
                                'body' => 'Night before works best for me. I wake up knowing exactly what to do and I start faster.',
                                'replies' => [
                                    'Same. If I don’t plan, I get pulled into messages and lose the morning.',
                                ],
                            ],
                            [
                                'body' => 'Morning planning works if I keep it short—10 minutes max. Otherwise it turns into procrastination.',
                                'replies' => [],
                            ],
                        ],
                    ],
                    [
                        'title' => 'What’s your best way to avoid “just checking” notifications?',
                        'body' => 'I can start deep work, but I keep breaking focus to check notifications. Any tactics that actually stick?',
                        'tags' => ['productivity', 'deep-work', 'distractions'],
                        'comments' => [
                            [
                                'body' => 'I turn on Do Not Disturb and put my phone in another room. Out of sight really works.',
                                'replies' => [
                                    'Putting the phone away is huge. I also close email completely so I can’t “peek.”',
                                ],
                            ],
                            [
                                'body' => 'I set a timer for 45 minutes and tell myself I’m allowed to check after. The timer makes it feel finite.',
                                'replies' => [],
                            ],
                        ],
                    ],
                ],
                'reviews' => [
                    ['rating' => 5, 'feedback' => 'Time blocking + fewer notifications improved my output immediately. Simple and actionable.'],
                    ['rating' => 4, 'feedback' => 'Good focus on priorities. The “fix one distraction at a time” note made it sustainable.'],
                ],
            ],
            [
                'title' => 'Post-Workout Recovery Protocol',
                'tags' => ['recovery', 'sleep', 'hydration', 'mobility'],
                'content' => "A recovery protocol to reduce soreness and support adaptation.\n\nCore steps:\n- Cool down: easy walk or cycling for 5–10 minutes.\n- Hydration and protein within a reasonable window.\n- Light stretching or mobility.\n- Prioritize sleep.\n\nNotes:\nRecovery is a routine. Don’t “wait until you’re sore” to start.",
                'threads' => [
                    [
                        'title' => 'Does a cooldown actually reduce soreness for you?',
                        'body' => 'I usually finish my workout and sit down right away. People say a cooldown helps soreness—have you noticed a difference?',
                        'tags' => ['recovery', 'cooldown', 'soreness'],
                        'comments' => [
                            [
                                'body' => 'Yes. A 5-minute easy walk lowers that “stiff” feeling later. It’s not magic but it helps.',
                                'replies' => [
                                    'Agreed. It also helps my heart rate settle so I don’t feel wired after training.',
                                ],
                            ],
                            [
                                'body' => 'Cooldown + hydration is the combo for me. If I skip both, I feel it the next day.',
                                'replies' => [],
                            ],
                        ],
                    ],
                ],
                'reviews' => [
                    ['rating' => 5, 'feedback' => 'Solid recovery basics. The cooldown reminder helped me feel less stiff after leg days.'],
                    ['rating' => 4, 'feedback' => 'Good balance of hydration, protein, and sleep. Easy to follow without overthinking.'],
                ],
            ],
            [
                'title' => 'Anxiety Grounding Protocol',
                'tags' => ['anxiety', 'grounding', 'breathing', 'calm'],
                'content' => "Grounding techniques to reduce anxiety in the moment.\n\nCore steps:\n- 5-4-3-2-1 senses exercise.\n- Slow breathing with longer exhales.\n- Name the feeling and choose one small next action.\n\nNotes:\nIf anxiety is persistent or severe, consider professional support alongside these tools.",
                'threads' => [
                    [
                        'title' => 'Has the 5-4-3-2-1 grounding exercise helped you during panic?',
                        'body' => 'I tried 5-4-3-2-1 and it helped a bit, but I’m not sure I’m doing it right. Has it helped anyone during panic or high anxiety?',
                        'tags' => ['anxiety', 'grounding', 'calm'],
                        'comments' => [
                            [
                                'body' => 'Yes. I speak it out loud quietly and it pulls me out of the spiral. Touching something cold also helps me.',
                                'replies' => [
                                    'Touching something cold helps me too. I keep a cold water bottle nearby when I’m anxious.',
                                ],
                            ],
                            [
                                'body' => 'It helps more if I slow down. I used to rush through the list, but taking time makes it work better.',
                                'replies' => [],
                            ],
                            [
                                'body' => 'Breathing first makes it easier. A few long exhales and then the senses checklist feels more effective.',
                                'replies' => [],
                            ],
                        ],
                    ],
                ],
                'reviews' => [
                    ['rating' => 5, 'feedback' => 'Practical, calming tools. The senses exercise and longer exhales help me reset quickly.'],
                    ['rating' => 4, 'feedback' => 'Helpful for acute anxiety. I appreciated the note about getting support when needed.'],
                ],
            ],
            [
                'title' => 'Healthy Morning Routine Protocol',
                'tags' => ['morning', 'hydration', 'sunlight', 'mobility'],
                'content' => "A simple morning routine to start the day with energy and clarity.\n\nCore steps:\n- Drink water soon after waking.\n- Get outside light.\n- Light movement (mobility or a short walk).\n- Write a quick plan for the day.\n\nNotes:\nKeep it short. The goal is consistency, not a perfect routine.",
                'threads' => [
                    [
                        'title' => 'What’s the smallest morning routine that still feels effective?',
                        'body' => 'I want a morning routine but I don’t have an hour. What’s the smallest version that still makes a difference for you?',
                        'tags' => ['morning', 'habits', 'planning'],
                        'comments' => [
                            [
                                'body' => 'Water + 5 minutes outside + writing my top priority. That’s it. It keeps me on track.',
                                'replies' => [
                                    'Same. I keep it to 10 minutes total and it’s much easier to maintain.',
                                ],
                            ],
                            [
                                'body' => 'If I move my body for even 3 minutes (neck/shoulders/hips), I feel less stiff all day.',
                                'replies' => [],
                            ],
                        ],
                    ],
                ],
                'reviews' => [
                    ['rating' => 5, 'feedback' => 'Great MVP routine. Water + light + a short plan improved my mornings without feeling complicated.'],
                    ['rating' => 4, 'feedback' => 'Simple and realistic. I liked the emphasis on consistency over a “perfect” routine.'],
                ],
            ],
        ];

        // Ensure we have all 12 required protocols; add the missing one not yet included above.
        $protocolSeeds[] = [
            'title' => 'Evening Digital Detox Protocol', // already included above (safety net if edits happen)
            'tags' => ['digital-detox', 'sleep', 'focus', 'habits'],
            'content' => 'This protocol is already seeded earlier in the list. If you see this, remove duplicates.',
            'threads' => [],
            'reviews' => [],
        ];

        // Remove accidental duplicate placeholders by title (keeps the first).
        $protocolSeeds = collect($protocolSeeds)
            ->unique('title')
            ->values()
            ->all();

        if (count($protocolSeeds) !== 12) {
            throw new Exception('Seeder configuration error: expected 12 unique protocols in $protocolSeeds.');
        }

        /** @var Collection<int, Thread> $allThreads */
        $allThreads = collect();
        /** @var Collection<int, Comment> $allComments */
        $allComments = collect();

        foreach ($protocolSeeds as $seed) {
            $author = $users->random();

            /** @var Protocol $protocol */
            $protocol = Protocol::query()->create([
                'tbl_protocol_title' => $seed['title'],
                'tbl_protocol_slug' => Str::slug($seed['title']).'-'.Str::lower(Str::random(6)),
                'tbl_protocol_content' => $seed['content'],
                'tbl_protocol_tags' => $seed['tags'],
                'tbl_protocol_author_id' => $author->tbl_user_id,
            ]);

            // Threads + comments + replies
            foreach (($seed['threads'] ?? []) as $threadSeed) {
                $threadAuthor = $users->random();
                /** @var Thread $thread */
                $thread = Thread::query()->create([
                    'tbl_thread_protocol_id' => $protocol->tbl_protocol_id,
                    'tbl_thread_author_id' => $threadAuthor->tbl_user_id,
                    'tbl_thread_title' => $threadSeed['title'],
                    'tbl_thread_body' => $threadSeed['body'],
                    'tbl_thread_tags' => $threadSeed['tags'] ?? [],
                ]);

                $allThreads->push($thread);

                foreach (($threadSeed['comments'] ?? []) as $commentSeed) {
                    $commentAuthor = $users->random();
                    /** @var Comment $comment */
                    $comment = Comment::query()->create([
                        'tbl_comment_thread_id' => $thread->tbl_thread_id,
                        'tbl_comment_author_id' => $commentAuthor->tbl_user_id,
                        'tbl_comment_parent_id' => null,
                        'tbl_comment_body' => $commentSeed['body'],
                    ]);

                    $allComments->push($comment);

                    foreach (($commentSeed['replies'] ?? []) as $replyBody) {
                        $replyAuthor = $users->random();
                        /** @var Comment $reply */
                        $reply = Comment::query()->create([
                            'tbl_comment_thread_id' => $thread->tbl_thread_id,
                            'tbl_comment_author_id' => $replyAuthor->tbl_user_id,
                            'tbl_comment_parent_id' => $comment->tbl_comment_id,
                            'tbl_comment_body' => (string) $replyBody,
                        ]);
                        $allComments->push($reply);
                    }
                }
            }

            // Reviews (2-4), context-aware feedback. Ensure unique (protocol, author).
            $reviewSeeds = $seed['reviews'] ?? [];
            if (count($reviewSeeds) < 2) {
                throw new Exception('Seeder configuration error: each protocol must have at least 2 reviews.');
            }

            $reviewAuthors = $users->shuffle()->take(min(count($reviewSeeds), $users->count()));
            foreach ($reviewSeeds as $idx => $reviewSeed) {
                $reviewAuthor = $reviewAuthors->get($idx) ?? $users->random();
                Review::query()->updateOrCreate(
                    [
                        'tbl_review_protocol_id' => $protocol->tbl_protocol_id,
                        'tbl_review_author_id' => $reviewAuthor->tbl_user_id,
                    ],
                    [
                        'tbl_review_rating' => (int) $reviewSeed['rating'],
                        'tbl_review_feedback' => (string) $reviewSeed['feedback'],
                    ]
                );
            }
        }

        // Minimum data checks
        if (Protocol::count() < 12) {
            throw new Exception('Seeder relationship error: expected at least 12 protocols.');
        }
        if ($allThreads->count() < 10) {
            throw new Exception('Seeder relationship error: expected at least 10 threads.');
        }

        // Votes (thread + comment), only after all records exist; no duplicates per user+votable.
        foreach ($allThreads as $thread) {
            foreach ($users->shuffle()->take(3) as $user) {
                Vote::query()->updateOrCreate(
                    [
                        'tbl_vote_user_id' => $user->tbl_user_id,
                        'tbl_vote_votable_id' => $thread->tbl_thread_id,
                        'tbl_vote_votable_type' => Thread::class,
                    ],
                    [
                        'tbl_vote_value' => fake()->randomElement([1, 1, 1, -1]),
                    ]
                );
            }
        }

        foreach ($allComments->shuffle()->take(min(30, $allComments->count())) as $comment) {
            foreach ($users->shuffle()->take(2) as $user) {
                Vote::query()->updateOrCreate(
                    [
                        'tbl_vote_user_id' => $user->tbl_user_id,
                        'tbl_vote_votable_id' => $comment->tbl_comment_id,
                        'tbl_vote_votable_type' => Comment::class,
                    ],
                    [
                        'tbl_vote_value' => fake()->randomElement([1, -1]),
                    ]
                );
            }
        }

        // Recalculate cached counters
        foreach (Protocol::pluck('tbl_protocol_id') as $protocolId) {
            Review::updateProtocolRating((int) $protocolId);
            Thread::syncProtocolVotesCount((int) $protocolId);
        }
        foreach (Thread::pluck('tbl_thread_id') as $threadId) {
            Comment::syncThreadCommentsCount((int) $threadId);
        }

        // Sanity checks (relationships + no orphan votes + no dup vote rows)
        $badThread = DB::table('tbl_threads as t')
            ->leftJoin('tbl_protocols as p', 'p.tbl_protocol_id', '=', 't.tbl_thread_protocol_id')
            ->whereNull('p.tbl_protocol_id')
            ->first();
        if ($badThread) {
            throw new Exception('Seeder relationship error: thread references missing protocol.');
        }

        $badComment = DB::table('tbl_comments as c')
            ->leftJoin('tbl_threads as t', 't.tbl_thread_id', '=', 'c.tbl_comment_thread_id')
            ->whereNull('t.tbl_thread_id')
            ->first();
        if ($badComment) {
            throw new Exception('Seeder relationship error: comment references missing thread.');
        }

        $badReply = DB::table('tbl_comments as r')
            ->join('tbl_comments as p', 'p.tbl_comment_id', '=', 'r.tbl_comment_parent_id')
            ->whereNotNull('r.tbl_comment_parent_id')
            ->whereColumn('r.tbl_comment_thread_id', '!=', 'p.tbl_comment_thread_id')
            ->first();
        if ($badReply) {
            throw new Exception('Seeder relationship error: reply parent belongs to a different thread.');
        }

        $badReview = DB::table('tbl_reviews as r')
            ->leftJoin('tbl_protocols as p', 'p.tbl_protocol_id', '=', 'r.tbl_review_protocol_id')
            ->whereNull('p.tbl_protocol_id')
            ->first();
        if ($badReview) {
            throw new Exception('Seeder relationship error: review references missing protocol.');
        }

        $threadVoteOrphan = DB::table('tbl_votes as v')
            ->where('v.tbl_vote_votable_type', Thread::class)
            ->leftJoin('tbl_threads as t', 't.tbl_thread_id', '=', 'v.tbl_vote_votable_id')
            ->whereNull('t.tbl_thread_id')
            ->first();
        if ($threadVoteOrphan) {
            throw new Exception('Seeder relationship error: vote references missing thread.');
        }

        $commentVoteOrphan = DB::table('tbl_votes as v')
            ->where('v.tbl_vote_votable_type', Comment::class)
            ->leftJoin('tbl_comments as c', 'c.tbl_comment_id', '=', 'v.tbl_vote_votable_id')
            ->whereNull('c.tbl_comment_id')
            ->first();
        if ($commentVoteOrphan) {
            throw new Exception('Seeder relationship error: vote references missing comment.');
        }

        $dupVote = DB::table('tbl_votes')
            ->select('tbl_vote_user_id', 'tbl_vote_votable_id', 'tbl_vote_votable_type', DB::raw('COUNT(*) as c'))
            ->groupBy('tbl_vote_user_id', 'tbl_vote_votable_id', 'tbl_vote_votable_type')
            ->having('c', '>', 1)
            ->first();
        if ($dupVote) {
            throw new Exception('Seeder relationship error: duplicate vote exists for user + votable.');
        }

        try {
            app(TypesenseService::class)->reindexAll();
        } catch (\Throwable $e) {
            Log::warning('Typesense reindex after seed failed: '.$e->getMessage(), ['exception' => $e]);
        }
    }
}

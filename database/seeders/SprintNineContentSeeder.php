<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\GrammarLesson;
use App\Models\ListeningContent;
use App\Models\Passage;
use App\Models\Question;
use App\Models\SpeakingPrompt;
use App\Models\Topic;
use App\Models\Vocabulary;
use App\Models\WritingPrompt;
use Illuminate\Database\Seeder;

class SprintNineContentSeeder extends Seeder
{
    private const SOURCE_NOTES = 'Original Sprint 9 batch 1; editorial, answer, and provenance review completed 2026-09-12.';

    public function run(): void
    {
        $topics = $this->seedTopics();
        $this->seedVocabulary($topics);
        $this->seedGrammar($topics['core-tenses']);
        $passages = $this->seedPassages($topics);
        $questions = $this->seedReadingQuestions($topics, $passages);
        $this->seedExercises($topics, $questions);
        $listening = $this->seedListening($topics);
        $this->seedListeningQuestions($topics, $listening);
        $this->seedWriting($topics);
        $this->seedSpeaking($topics);
    }

    /** @return array<string, Topic> */
    private function seedTopics(): array
    {
        $definitions = [
            'personal-identity-relationships' => ['Personal identity and relationships', 'vocabulary', 'Original vocabulary for describing people, support, and changing relationships.', 30, 1],
            'work-and-careers' => ['Work and careers', 'vocabulary', 'Original vocabulary for jobs, applications, responsibilities, and teamwork.', 40, 1],
            'travel-tourism-transport' => ['Travel, tourism and transport', 'vocabulary', 'Original vocabulary for journeys, reservations, delays, and destinations.', 50, 1],
            'health-and-lifestyle' => ['Health and lifestyle', 'vocabulary', 'Original vocabulary for healthy routines, symptoms, recovery, and stress.', 60, 1],
            'technology-communication' => ['Technology and communication', 'vocabulary', 'Original vocabulary for devices, updates, privacy, and online communication.', 70, 1],
            'environment-nature-weather' => ['Environment, nature and weather', 'vocabulary', 'Original vocabulary for practical environmental action, weather, and wildlife.', 80, 1],
            'shopping-money-services' => ['Shopping, money and services', 'vocabulary', 'Original vocabulary for value, payments, returns, and customer services.', 90, 1],
            'society-community-public-services' => ['Society, community and public services', 'vocabulary', 'Original vocabulary for local facilities, volunteering, safety, and public life.', 100, 1],
        ];

        $topics = [
            'core-tenses' => Topic::query()->where('slug', 'core-tenses')->firstOrFail(),
        ];

        foreach ($definitions as $slug => [$name, $area, $description, $position, $priority]) {
            $topics[$slug] = Topic::query()->updateOrCreate(
                ['slug' => $slug],
                compact('name', 'area', 'description', 'position', 'priority') + ['status' => 'active'],
            );
        }

        return $topics;
    }

    /** @param array<string, Topic> $topics */
    private function seedVocabulary(array $topics): void
    {
        $items = [
            'personal-identity-relationships' => [
                ['supportive', 'adjective', 'Giving help and encouragement when someone needs it.', 'hỗ trợ', 'My study partner is supportive when I make a mistake.', 'Often used with person, friend, or family.'],
                ['reliable', 'adjective', 'Able to be trusted to do what is expected.', 'đáng tin cậy', 'A reliable teammate arrives on time and completes the agreed task.', 'The opposite is unreliable.'],
                ['get along with', 'phrase', 'To have a friendly relationship with someone.', 'hòa thuận với', 'I get along with my classmates because we listen to one another.', 'Use with a person or group.'],
                ['take responsibility', 'collocation', 'To accept that you must deal with a duty or result.', 'chịu trách nhiệm', 'She took responsibility for sending the meeting details.', 'Commonly followed by for and a noun or -ing form.'],
            ],
            'work-and-careers' => [
                ['applicant', 'noun', 'A person who asks formally for a job or place on a course.', 'ứng viên', 'Each applicant received an email about the interview time.', 'Related verb: apply.'],
                ['responsibility', 'noun', 'A duty that you are expected to complete.', 'trách nhiệm', 'One responsibility of the assistant is to update the weekly schedule.', 'Often used with take, have, or share.'],
                ['flexible', 'adjective', 'Able to change or adapt to a different situation.', 'linh hoạt', 'A flexible work plan helps the team respond to busy weeks.', 'Can describe hours, plans, or a person.'],
                ['meet a deadline', 'collocation', 'To finish something by the required time.', 'kịp hạn chót', 'We divided the task so that we could meet the deadline.', 'Use meet, not do, with deadline.'],
            ],
            'travel-tourism-transport' => [
                ['reservation', 'noun', 'An arrangement to keep a seat, room, or place for someone.', 'đặt chỗ', 'I made a reservation for a small room near the station.', 'Common with make, confirm, and cancel.'],
                ['delay', 'noun', 'A period when travel or an activity starts later than planned.', 'sự trì hoãn', 'The train delay gave us time to buy some water.', 'A delay can be caused by weather or repairs.'],
                ['destination', 'noun', 'The place where someone or something is going.', 'điểm đến', 'Our final destination is a quiet village near the coast.', 'Use arrive at a destination.'],
                ['affordable', 'adjective', 'Not too expensive for the people who want to buy it.', 'giá phải chăng', 'The hostel is affordable and close to public transport.', 'Useful for accommodation, tickets, and services.'],
            ],
            'health-and-lifestyle' => [
                ['balanced', 'adjective', 'Including a healthy variety of different things.', 'cân bằng', 'A balanced routine includes sleep, movement, and time to relax.', 'Often used with diet, meal, or lifestyle.'],
                ['symptom', 'noun', 'A change in the body or mind that may show illness.', 'triệu chứng', 'A sore throat was the first symptom of her cold.', 'Use medical advice for serious or continuing symptoms.'],
                ['recover', 'verb', 'To become healthy or return to a normal condition.', 'hồi phục', 'He needed two quiet days to recover from the flu.', 'Recover from an illness, injury, or difficult event.'],
                ['stressful', 'adjective', 'Making someone feel worried or under pressure.', 'căng thẳng', 'Travelling during a storm can be stressful.', 'Describe a situation; stressed describes a person.'],
            ],
            'technology-communication' => [
                ['device', 'noun', 'A piece of equipment designed for a particular purpose.', 'thiết bị', 'This small device measures the room temperature.', 'Phones, tablets, and sensors are examples.'],
                ['privacy', 'noun', 'The right to keep personal information or activities away from public view.', 'quyền riêng tư', 'The app explains how it protects user privacy.', 'Common with protect, respect, and online.'],
                ['update', 'verb', 'To make software or information more recent.', 'cập nhật', 'Please update the app before joining the online class.', 'As a noun, an update is new information or software.'],
                ['troubleshoot', 'verb', 'To find and solve the cause of a technical problem.', 'khắc phục sự cố', 'The guide helps users troubleshoot a weak connection.', 'Often used for devices, software, and networks.'],
            ],
            'environment-nature-weather' => [
                ['recycle', 'verb', 'To process used material so it can be used again.', 'tái chế', 'The school encourages students to recycle paper and cans.', 'Recycling is the noun form.'],
                ['reduce', 'verb', 'To make something smaller in amount or degree.', 'giảm', 'We can reduce waste by carrying a reusable bottle.', 'Common with waste, energy, cost, and risk.'],
                ['shortage', 'noun', 'A situation in which there is less of something than people need.', 'sự thiếu hụt', 'A water shortage made the town change its garden plans.', 'Often followed by of and a noun.'],
                ['wildlife', 'noun', 'Animals and plants that live in natural conditions.', 'động vật hoang dã', 'The reserve protects local wildlife from traffic and litter.', 'Usually treated as an uncountable noun.'],
            ],
            'shopping-money-services' => [
                ['refund', 'noun', 'Money returned to someone for a product or service.', 'tiền hoàn lại', 'The shop offered a refund because the headphones did not work.', 'Ask for a refund; receive a refund.'],
                ['receipt', 'noun', 'A written record showing that payment has been made.', 'biên lai', 'Keep the receipt in case you need to return the jacket.', 'A paper or digital receipt can prove a purchase.'],
                ['value', 'noun', 'How useful or worthwhile something is compared with its cost.', 'giá trị', 'The course offers good value because materials are included.', 'Good value does not always mean the lowest price.'],
                ['afford', 'verb', 'To have enough money to pay for something.', 'có khả năng chi trả', 'We cannot afford a taxi, so we will take the bus.', 'Use can or cannot afford plus a noun or to-infinitive.'],
            ],
            'society-community-public-services' => [
                ['volunteer', 'verb', 'To offer to do something without being paid.', 'tình nguyện', 'Many residents volunteer to clean the park at weekends.', 'Volunteer can also be a noun for the person.'],
                ['facility', 'noun', 'A place or equipment provided for a particular purpose.', 'cơ sở vật chất', 'The community centre has a study facility with free internet.', 'Common with public, sports, and medical.'],
                ['pedestrian', 'noun', 'A person who is walking rather than travelling in a vehicle.', 'người đi bộ', 'The new crossing gives pedestrians more time to cross safely.', 'Pedestrian can also describe a path or area.'],
                ['neighborhood', 'noun', 'An area of a town where people live.', 'khu phố', 'Our neighborhood holds a small market on the first Sunday.', 'British spelling: neighbourhood.'],
            ],
        ];

        foreach ($items as $slug => $topicItems) {
            foreach ($topicItems as [$term, $partOfSpeech, $definition, $translation, $example, $notes]) {
                Vocabulary::query()->updateOrCreate(
                    ['topic_id' => $topics[$slug]->id, 'term' => $term, 'part_of_speech' => $partOfSpeech],
                    [
                        'definition' => $definition,
                        'translation' => $translation,
                        'example_sentence' => $example,
                        'notes' => $notes,
                        'source_type' => 'original',
                        'source_notes' => self::SOURCE_NOTES,
                        'status' => 'active',
                    ],
                );
            }
        }
    }

    private function seedGrammar(Topic $topic): void
    {
        $lessons = [
            [
                'slug' => 'past-simple-and-present-perfect',
                'title' => 'Past simple and present perfect',
                'objectives' => 'Choose between a finished past event and an experience or result connected with the present.',
                'prerequisites' => 'Basic past forms and common time expressions.',
                'body' => "Use the past simple for a finished event at a stated or understood past time.\n\nUse the present perfect for an experience, a recent result, or an action that began in the past and remains connected with now. Do not use the present perfect with a finished time such as yesterday.",
                'examples' => [
                    ['example' => 'I visited Da Nang last summer.', 'explanation' => 'Last summer gives a finished past time.'],
                    ['example' => 'I have visited Da Nang twice.', 'explanation' => 'The sentence describes experience without a finished time.'],
                ],
                'common_mistakes' => 'Avoid “I have visited it last year.” Use “I visited it last year.”',
                'position' => 20,
            ],
            [
                'slug' => 'future-plans-and-predictions',
                'title' => 'Future plans and predictions',
                'objectives' => 'Use will, be going to, and the present continuous for common future meanings.',
                'prerequisites' => 'Present forms and the verb be.',
                'body' => "Use be going to for an intention or a prediction based on present evidence. Use the present continuous for a fixed arrangement. Use will for a decision made now, a promise, or a prediction based on an opinion.",
                'examples' => [
                    ['example' => 'I am meeting my tutor on Thursday.', 'explanation' => 'The present continuous shows a fixed arrangement.'],
                    ['example' => 'Look at those clouds. It is going to rain.', 'explanation' => 'The prediction is based on present evidence.'],
                ],
                'common_mistakes' => 'Use “I will help you” for a decision made at the moment of speaking, not “I am going to help you” unless it was already planned.',
                'position' => 30,
            ],
            [
                'slug' => 'modals-for-advice-and-obligation',
                'title' => 'Modals for advice and obligation',
                'objectives' => 'Express advice, necessity, and prohibition with should, must, have to, and must not.',
                'prerequisites' => 'Basic sentence order and the meaning of need.',
                'body' => "Use should and should not for advice. Use must or have to for a strong obligation. Use must not for something that is prohibited. The form have to changes for different subjects and tenses, but modal verbs do not take to or an -s ending.",
                'examples' => [
                    ['example' => 'You should check the address before travelling.', 'explanation' => 'Should gives helpful advice.'],
                    ['example' => 'Visitors must not enter this area.', 'explanation' => 'Must not expresses prohibition.'],
                ],
                'common_mistakes' => 'Do not write “She shoulds rest.” Use “She should rest.”',
                'position' => 40,
            ],
            [
                'slug' => 'comparatives-and-superlatives',
                'title' => 'Comparatives and superlatives',
                'objectives' => 'Compare people, places, and choices clearly in everyday and study contexts.',
                'prerequisites' => 'Common adjectives and the verb be.',
                'body' => "Use a comparative to compare two things, often with than. Use the superlative to compare one thing with a group, usually with the. Short adjectives often take -er and -est; longer adjectives use more and most. Some common forms are irregular.",
                'examples' => [
                    ['example' => 'The bus is cheaper than a taxi.', 'explanation' => 'Cheaper compares two transport choices.'],
                    ['example' => 'This is the most useful part of the guide.', 'explanation' => 'Most useful identifies one item in a group.'],
                ],
                'common_mistakes' => 'Use “more useful,” not “usefuler,” and include than when the second item is named.',
                'position' => 50,
            ],
        ];

        foreach ($lessons as $lesson) {
            GrammarLesson::query()->updateOrCreate(
                ['slug' => $lesson['slug']],
                $lesson + [
                    'topic_id' => $topic->id,
                    'source_type' => 'original',
                    'source_notes' => self::SOURCE_NOTES,
                    'status' => 'active',
                ],
            );
        }
    }

    /** @param array<string, Topic> $topics @return array<string, Passage> */
    private function seedPassages(array $topics): array
    {
        $items = [
            'community-workshop' => [
                'title' => 'A quieter start at the community workshop',
                'topic' => 'society-community-public-services',
                'body' => 'When the Riverside Community Centre opened a free repair workshop, the organisers expected most visitors to arrive on Saturday afternoon. Instead, the busiest time was early Tuesday evening. People came directly from work with lamps, bags, and small kitchen machines. A volunteer called Linh showed visitors how to describe a problem before choosing a solution. The centre did not promise to repair every item. It first checked whether a safe repair was possible and explained the likely cost of parts. This approach reduced waste and helped visitors decide whether to repair, reuse, or replace something. After three months, the organisers added a booking list because the short sessions were regularly full.',
                'difficulty' => 1,
            ],
            'healthier-evenings' => [
                'title' => 'A simple plan for healthier evenings',
                'topic' => 'health-and-lifestyle',
                'body' => 'Thanh wanted to sleep better, but he did not want a complicated health programme. He began by changing the last hour of his evening. He prepared the next day before dinner, placed his phone on a shelf, and walked around the block after eating. The first week felt difficult because he was used to checking messages in bed. After two weeks, he noticed that he fell asleep more quickly and felt less tired in the morning. He still used his phone when necessary, but he stopped treating every message as urgent. His doctor had suggested that small, regular changes were more useful than a perfect plan that lasted only a few days. Thanh now keeps the routine on busy evenings as well.',
                'difficulty' => 1,
            ],
            'repair-before-replacement' => [
                'title' => 'Repair before replacement',
                'topic' => 'technology-communication',
                'body' => 'A student group at Westbridge College started a monthly technology desk. Members bring phones and laptops that are slow, damaged, or difficult to use. The desk does not sell equipment and does not keep personal files. Instead, volunteers explain simple checks, such as clearing unused files, updating software, and testing a charger. If a problem needs a professional repair, they say so clearly. The group also talks about privacy. Visitors are asked to back up important material before a device is examined, and no volunteer asks for a password. The project has helped students save money, but its organisers say the main benefit is confidence. People are more willing to solve a small problem when they understand what caused it.',
                'difficulty' => 2,
            ],
            'bus-route-feedback' => [
                'title' => 'A bus route shaped by its passengers',
                'topic' => 'travel-tourism-transport',
                'body' => 'The number 18 bus used to pass the market every hour, but it did not stop near the public library. Older passengers had to cross a busy road and walk several minutes. The transport office invited residents to describe their usual journeys and suggest a safer stop. Some people requested a later evening service, while others preferred more frequent buses in the morning. After reviewing the feedback, the office moved one stop and added a morning bus on weekdays. The change was small, but it helped library staff, market workers, and students. The office plans to collect more comments before changing the evening timetable because a new service must be useful enough to justify its cost.',
                'difficulty' => 2,
            ],
        ];

        $passages = [];
        foreach ($items as $key => $item) {
            $passages[$key] = Passage::query()->updateOrCreate(
                ['title' => $item['title']],
                [
                    'topic_id' => $topics[$item['topic']]->id,
                    'body' => $item['body'],
                    'word_count' => $this->countWords($item['body']),
                    'cefr_level' => 'B1',
                    'difficulty' => $item['difficulty'],
                    'source_type' => 'original',
                    'source_notes' => self::SOURCE_NOTES,
                    'status' => 'active',
                ],
            );
        }

        return $passages;
    }

    /** @param array<string, Topic> $topics @param array<string, Passage> $passages @return array<string, Question> */
    private function seedReadingQuestions(array $topics, array $passages): array
    {
        $items = [
            'community-workshop-purpose' => ['community-workshop', 'What was one result of the repair workshop?', 'It helped visitors make informed choices about their items.', 'The workshop explained whether repair, reuse, or replacement was sensible.', ['It sold new kitchen machines.', 'It offered free parts to every visitor.', 'It moved all sessions to Saturday.', 'It helped visitors make informed choices about their items.'], 1],
            'community-workshop-booking' => ['community-workshop', 'Why did the organisers add a booking list?', 'The short sessions were often full.', 'The passage says the sessions were regularly full after three months.', ['The centre moved to a new building.', 'The short sessions were often full.', 'Visitors could not describe their problems.', 'The volunteers stopped attending.'], 1],
            'healthier-evenings-change' => ['healthier-evenings', 'What did Thanh change first?', 'The last hour of his evening.', 'He changed the last hour by preparing for the next day, moving his phone, and walking.', ['The last hour of his evening.', 'His doctor and his medication.', 'His working hours at the centre.', 'The food he bought at the market.'], 1],
            'healthier-evenings-main-idea' => ['healthier-evenings', 'What is the main idea of the passage?', 'Small regular changes can support a healthier routine.', 'Thanh improved his evenings through a small routine that he could keep on busy days.', ['A perfect health programme is easy to follow.', 'Phones should never be used after dinner.', 'Small regular changes can support a healthier routine.', 'Doctors should design every evening schedule.'], 2],
            'repair-before-replacement-safety' => ['repair-before-replacement', 'What does the technology desk avoid asking visitors for?', 'A password.', 'The passage says that no volunteer asks for a password.', ['A receipt.', 'A password.', 'A charger.', 'A timetable.'], 1],
            'repair-before-replacement-benefit' => ['repair-before-replacement', 'According to the organisers, what is the main benefit of the project?', 'People gain confidence in solving small problems.', 'The organisers say confidence is more important than only saving money.', ['People buy newer equipment.', 'People gain confidence in solving small problems.', 'People share personal files with volunteers.', 'People avoid all professional repairs.'], 2],
            'bus-route-change' => ['bus-route-feedback', 'What change was made after residents gave feedback?', 'A stop was moved and a weekday morning bus was added.', 'The transport office made both of these small changes.', ['The library was moved closer to the market.', 'A stop was moved and a weekday morning bus was added.', 'The evening service was removed.', 'The road near the market was closed.'], 1],
            'bus-route-next-step' => ['bus-route-feedback', 'Why will the office collect more comments before changing the evening timetable?', 'A new service needs to justify its cost.', 'The final sentence connects more feedback with checking whether the service is useful enough for its cost.', ['The library is closed in the evening.', 'A new service needs to justify its cost.', 'Residents cannot use the number 18 bus.', 'Morning buses are no longer available.'], 2],
        ];

        $questions = [];
        foreach ($items as $key => [$passageKey, $prompt, $correct, $explanation, $options, $difficulty]) {
            $passage = $passages[$passageKey];
            $question = Question::query()->updateOrCreate(
                ['prompt' => $prompt],
                [
                    'topic_id' => $passage->topic_id,
                    'passage_id' => $passage->id,
                    'listening_content_id' => null,
                    'skill' => 'reading',
                    'type' => 'single_choice',
                    'explanation' => $explanation,
                    'cefr_level' => 'B1',
                    'difficulty' => $difficulty,
                    'metadata' => ['batch' => 'sprint-9-batch-1', 'review_status' => 'answer-reviewed'],
                    'source_type' => 'original',
                    'source_notes' => self::SOURCE_NOTES,
                    'status' => 'active',
                ],
            );
            foreach ($options as $position => $content) {
                $question->options()->updateOrCreate(
                    ['option_key' => chr(65 + $position)],
                    ['content' => $content, 'is_correct' => $content === $correct, 'position' => $position],
                );
            }
            $questions[$key] = $question;
        }

        return $questions;
    }

    /** @param array<string, Topic> $topics @param array<string, Question> $questions */
    private function seedExercises(array $topics, array $questions): void
    {
        $definitions = [
            ['title' => 'Community repair reading practice', 'topic' => 'society-community-public-services', 'questionKeys' => ['community-workshop-purpose', 'community-workshop-booking']],
            ['title' => 'Healthier evenings reading practice', 'topic' => 'health-and-lifestyle', 'questionKeys' => ['healthier-evenings-change', 'healthier-evenings-main-idea']],
            ['title' => 'Technology desk reading practice', 'topic' => 'technology-communication', 'questionKeys' => ['repair-before-replacement-safety', 'repair-before-replacement-benefit']],
            ['title' => 'Bus route feedback reading practice', 'topic' => 'travel-tourism-transport', 'questionKeys' => ['bus-route-change', 'bus-route-next-step']],
        ];

        foreach ($definitions as $definition) {
            $exercise = Exercise::query()->updateOrCreate(
                ['title' => $definition['title']],
                [
                    'topic_id' => $topics[$definition['topic']]->id,
                    'skill' => 'reading',
                    'instructions' => 'Read the original passage, choose the best answer, and review the evidence after submitting.',
                    'difficulty' => 1,
                    'time_limit_seconds' => 240,
                    'metadata' => ['batch' => 'sprint-9-batch-1', 'review_status' => 'content-reviewed'],
                    'status' => 'active',
                ],
            );
            foreach ($definition['questionKeys'] as $position => $questionKey) {
                $exercise->exerciseQuestions()->updateOrCreate(
                    ['question_id' => $questions[$questionKey]->id],
                    ['position' => $position, 'points' => 1.00],
                );
            }
        }
    }

    /** @param array<string, Topic> $topics @return array<string, ListeningContent> */
    private function seedListening(array $topics): array
    {
        $items = [
            'library-workshop' => ['title' => 'A change to the library workshop', 'topic' => 'society-community-public-services', 'transcript' => 'The library workshop will start thirty minutes later this Thursday because the room is being cleaned. Please bring your registration email, but you do not need to bring a laptop. The tutor will provide printed notes.', 'path' => 'audio/listening/pending/sprint9-library-workshop.mp3', 'duration' => 35, 'speakers' => 1],
            'train-connection' => ['title' => 'Checking a train connection', 'topic' => 'travel-tourism-transport', 'transcript' => 'Our train is delayed by ten minutes, so we should still catch the connection at the main station. If the platform changes, I will check the information board near the entrance. We have enough time to buy water.', 'path' => 'audio/listening/pending/sprint9-train-connection.mp3', 'duration' => 30, 'speakers' => 2],
        ];

        $listening = [];
        foreach ($items as $key => $item) {
            $listening[$key] = ListeningContent::query()->updateOrCreate(
                ['title' => $item['title']],
                [
                    'topic_id' => $topics[$item['topic']]->id,
                    'transcript' => $item['transcript'],
                    'audio_path' => $item['path'],
                    'audio_mime' => 'audio/mpeg',
                    'audio_size_bytes' => 1,
                    'duration_seconds' => $item['duration'],
                    'speaker_count' => $item['speakers'],
                    'accent_notes' => 'Original B1 script; recording pending owner audio and licence review.',
                    'cefr_level' => 'B1',
                    'difficulty' => 1,
                    'source_type' => 'original',
                    'source_notes' => 'Original Sprint 9 batch 1 script. Draft remains inactive until a permitted recording is supplied and reviewed.',
                    'status' => 'draft',
                ],
            );
        }

        return $listening;
    }

    /** @param array<string, Topic> $topics @param array<string, ListeningContent> $listening */
    private function seedListeningQuestions(array $topics, array $listening): void
    {
        $items = [
            ['library-workshop', 'When will the library workshop start?', 'Thirty minutes later than usual.', ['Thirty minutes earlier than usual.', 'Thirty minutes later than usual.'], 'The announcement says the workshop will start thirty minutes later.'],
            ['library-workshop', 'What should participants bring?', 'Their registration email.', ['A laptop.', 'Their registration email.'], 'The tutor provides printed notes, but participants should bring the registration email.'],
            ['train-connection', 'How long is the train delayed?', 'Ten minutes.', ['Ten minutes.', 'One hour.'], 'The speaker says the train is delayed by ten minutes.'],
            ['train-connection', 'Where will the speaker check for a platform change?', 'Near the entrance.', ['Near the entrance.', 'Inside the café.'], 'The information board is near the station entrance.'],
        ];

        foreach ($items as [$listeningKey, $prompt, $correct, $options, $explanation]) {
            $content = $listening[$listeningKey];
            $question = Question::query()->updateOrCreate(
                ['prompt' => $prompt],
                [
                    'topic_id' => $content->topic_id,
                    'passage_id' => null,
                    'listening_content_id' => $content->id,
                    'skill' => 'listening',
                    'type' => 'single_choice',
                    'explanation' => $explanation,
                    'cefr_level' => 'B1',
                    'difficulty' => 1,
                    'metadata' => ['batch' => 'sprint-9-batch-1', 'review_status' => 'draft-audio-pending'],
                    'source_type' => 'original',
                    'source_notes' => 'Original Sprint 9 batch 1 listening question. Keep draft until the audio is recorded and checked.',
                    'status' => 'draft',
                ],
            );
            foreach ($options as $position => $option) {
                $question->options()->updateOrCreate(
                    ['option_key' => chr(65 + $position)],
                    ['content' => $option, 'is_correct' => $option === $correct, 'position' => $position],
                );
            }
        }
    }

    /** @param array<string, Topic> $topics */
    private function seedWriting(array $topics): void
    {
        $items = [
            ['title' => 'Ask a community centre about a course', 'topic' => 'society-community-public-services', 'task_type' => 'task_1', 'instructions' => 'Write an email of at least 120 words to a community centre. Ask about an evening course, explain why you want to join, and ask what you should bring.', 'guidance' => 'Use a clear subject and polite questions. Include a reason for joining and a practical question about the course.', 'minutes' => 20],
            ['title' => 'Explain a change to a travel plan', 'topic' => 'travel-tourism-transport', 'task_type' => 'task_1', 'instructions' => 'Write an email of at least 120 words to a friend. Explain a change to a planned journey, give a reason, and suggest a new arrangement.', 'guidance' => 'Make the change clear, show how it affects the reader, and end with a specific suggestion.', 'minutes' => 20],
            ['title' => 'Are digital reminders helpful for learners?', 'topic' => 'technology-communication', 'task_type' => 'task_2', 'instructions' => 'Write an essay of at least 250 words about this question: Are digital reminders helpful for people who are learning a language? Give reasons and examples.', 'guidance' => 'Present a balanced position, discuss usefulness and limits, and support each main idea with a realistic example.', 'minutes' => 40],
            ['title' => 'Should cities invest in walking routes?', 'topic' => 'environment-nature-weather', 'task_type' => 'task_2', 'instructions' => 'Write an essay of at least 250 words about this question: Should cities invest more money in safe walking routes? Give reasons and examples.', 'guidance' => 'Explain benefits for people and the environment, consider one practical limitation, and finish with a clear recommendation.', 'minutes' => 40],
        ];

        foreach ($items as $item) {
            WritingPrompt::query()->updateOrCreate(
                ['title' => $item['title']],
                [
                    'topic_id' => $topics[$item['topic']]->id,
                    'task_type' => $item['task_type'],
                    'instructions' => $item['instructions'],
                    'minimum_words' => $item['task_type'] === 'task_1' ? 120 : 250,
                    'recommended_minutes' => $item['minutes'],
                    'guidance' => $item['guidance'],
                    'checklist' => ['I answered every part of the task.', 'My ideas follow a clear order.', 'I used useful B1 vocabulary and grammar.', 'I checked spelling, punctuation, and word count.'],
                    'model_answer' => null,
                    'source_type' => 'original',
                    'source_notes' => self::SOURCE_NOTES,
                    'status' => 'active',
                ],
            );
        }
    }

    /** @param array<string, Topic> $topics */
    private function seedSpeaking(array $topics): void
    {
        $items = [
            ['title' => 'Describe a supportive person', 'topic' => 'personal-identity-relationships', 'part_type' => 'social_interaction', 'instructions' => 'Talk about a person who has supported you in learning or work. Say who the person is, what they did, and why the support mattered.', 'preparation' => 5, 'speaking' => 45, 'ideas' => ['the person', 'a specific action', 'the effect on you']],
            ['title' => 'Describe a healthy evening habit', 'topic' => 'health-and-lifestyle', 'part_type' => 'social_interaction', 'instructions' => 'Talk about one evening habit that helps people feel ready for the next day. Give a reason and a personal or imagined example.', 'preparation' => 5, 'speaking' => 45, 'ideas' => ['the habit', 'why it helps', 'an example']],
            ['title' => 'Choose a low-waste change', 'topic' => 'environment-nature-weather', 'part_type' => 'solution_discussion', 'instructions' => 'Your community wants to reduce waste. Recommend one practical change and explain how residents could make it part of daily life.', 'preparation' => 60, 'speaking' => 120, 'ideas' => ['the change', 'how to organise it', 'a likely difficulty']],
            ['title' => 'Improve a local bus service', 'topic' => 'travel-tourism-transport', 'part_type' => 'solution_discussion', 'instructions' => 'A local bus service has limited money. Discuss whether it should add an early bus, a later bus, or a safer stop, and explain your choice.', 'preparation' => 60, 'speaking' => 120, 'ideas' => ['the main users', 'the best change', 'cost and benefit']],
            ['title' => 'How technology changes study', 'topic' => 'technology-communication', 'part_type' => 'topic_development', 'instructions' => 'Discuss how technology has changed the way people study. Compare one benefit with one problem and give a recommendation for learners.', 'preparation' => 60, 'speaking' => 120, 'ideas' => ['a benefit', 'a problem', 'your recommendation']],
            ['title' => 'A useful community facility', 'topic' => 'society-community-public-services', 'part_type' => 'topic_development', 'instructions' => 'Describe a community facility that people need or already use. Explain who benefits from it and how it could be improved.', 'preparation' => 60, 'speaking' => 120, 'ideas' => ['the facility', 'the users', 'one improvement']],
        ];

        foreach ($items as $item) {
            SpeakingPrompt::query()->updateOrCreate(
                ['title' => $item['title']],
                [
                    'topic_id' => $topics[$item['topic']]->id,
                    'part_type' => $item['part_type'],
                    'instructions' => $item['instructions'],
                    'preparation_seconds' => $item['preparation'],
                    'speaking_seconds' => $item['speaking'],
                    'suggested_ideas' => $item['ideas'],
                    'follow_up_questions' => ['What is one possible difficulty?', 'How would you know that the change was useful?'],
                    'checklist' => ['I answered all parts of the prompt.', 'I used a clear beginning, middle, and ending.', 'I gave a specific reason or example.', 'I chose one target for a second attempt.'],
                    'source_type' => 'original',
                    'source_notes' => self::SOURCE_NOTES,
                    'status' => 'active',
                ],
            );
        }
    }

    private function countWords(string $text): int
    {
        return preg_match_all('/\S+/u', trim($text), $matches) ?: 0;
    }
}

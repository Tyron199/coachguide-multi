<?php

namespace Database\Seeders\Tenant;

use App\Enums\Tenant\ResourceLibraryItemType;
use App\Models\Tenant\ResourceLibraryItem;
use Illuminate\Database\Seeder;

class ResourceLibrarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $resources = [
            // Books - Tony Robbins: Empowerment & Peak Performance
            [
                'title' => 'Awaken the Giant Within',
                'description' => 'Take immediate control of your mental, emotional, physical and financial destiny by Tony Robbins.',
                'link' => 'https://www.amazon.com/Awaken-Giant-Within-Immediate-Emotional/dp/0671791540',
                'type' => ResourceLibraryItemType::BOOK->value,
                'image_path' => '/images/resource-library/books/awaken-the-giant-within.jpg',
            ],
            [
                'title' => 'Unlimited Power',
                'description' => 'The New Science Of Personal Achievement by Tony Robbins. Master your mind, body and emotions.',
                'link' => 'https://www.amazon.com/Unlimited-Power-New-Science-Achievement/dp/0684845776',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'Money: Master the Game',
                'description' => '7 Simple Steps to Financial Freedom by Tony Robbins. Transform your financial life.',
                'link' => 'https://www.amazon.com/MONEY-Master-Game-Financial-Freedom/dp/1476757860',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],

            // Books - Brené Brown: Vulnerability, Courage & Leadership
            [
                'title' => 'Daring Greatly',
                'description' => 'How the Courage to Be Vulnerable Transforms the Way We Live, Love, Parent, and Lead by Brené Brown.',
                'link' => 'https://www.amazon.com/Daring-Greatly-Courage-Vulnerable-Transforms/dp/1592408419',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'The Gifts of Imperfection',
                'description' => 'Let Go of Who You Think You\'re Supposed to Be and Embrace Who You Are by Brené Brown.',
                'link' => 'https://www.amazon.com/Gifts-Imperfection-Think-Supposed-Embrace/dp/159285849X',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'Braving the Wilderness',
                'description' => 'The Quest for True Belonging and the Courage to Stand Alone by Brené Brown.',
                'link' => 'https://www.amazon.com/Braving-Wilderness-Quest-Belonging-Courage/dp/0812995848',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],

            // Books - Carl Jung: Depth Psychology & Archetypes
            [
                'title' => 'The Archetypes and the Collective Unconscious',
                'description' => 'Essential reading for understanding the foundational concepts of analytical psychology by Carl Jung.',
                'link' => 'https://www.amazon.com/Archetypes-Collective-Unconscious-Collected-Works/dp/0691018332',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'Man and His Symbols',
                'description' => 'Carl Jung\'s accessible introduction to his theories on the human psyche and symbolism.',
                'link' => 'https://www.amazon.com/Man-His-Symbols-Carl-Jung/dp/0964037815',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'Psychological Types',
                'description' => 'The foundation of personality typology and understanding individual differences by Carl Jung.',
                'link' => 'https://www.amazon.com/Psychological-Types-Collected-Works-Jung/dp/0691018138',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],

            // Books - Claudio Naranjo: Enneagram & Psychospiritual Coaching
            [
                'title' => 'Character and Neurosis',
                'description' => 'An Integrative View of personality types through the Enneagram by Claudio Naranjo.',
                'link' => 'https://www.amazon.com/Character-Neurosis-Integrative-View-Enneagram/dp/0895561646',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'Ennea-Type Structures',
                'description' => 'Self-Analysis for the Seeker by Claudio Naranjo. Deep dive into Enneagram psychology.',
                'link' => 'https://www.amazon.com/Ennea-type-Structures-Self-Analysis-Seeker/dp/1883647169',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'The End of Patriarchy',
                'description' => 'Toward a Posthuman Future by Claudio Naranjo. Transformation of consciousness and culture.',
                'link' => 'https://www.amazon.com/End-Patriarchy-Posthuman-Future/dp/1556439717',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],

            // Books - Executive & Leadership Coaching
            [
                'title' => 'The 7 Habits of Highly Effective People',
                'description' => 'Powerful Lessons in Personal Change by Stephen R. Covey. Timeless principles for effectiveness.',
                'link' => 'https://www.amazon.com/Habits-Highly-Effective-People-Powerful/dp/1982137274',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'Triggers',
                'description' => 'Creating Behavior That Lasts by Marshall Goldsmith. Become the person you want to be.',
                'link' => 'https://www.amazon.com/Triggers-Creating-Behavior-Lasts-Becoming-Person/dp/0804141231',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'What Got You Here Won\'t Get You There',
                'description' => 'How successful people become even more successful by Marshall Goldsmith.',
                'link' => 'https://www.amazon.com/What-Got-Here-Wont-There/dp/1401301304',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],

            // Books - Habits & Behavioural Change
            [
                'title' => 'Atomic Habits',
                'description' => 'An Easy & Proven Way to Build Good Habits & Break Bad Ones by James Clear.',
                'link' => 'https://www.amazon.com/Atomic-Habits-Proven-Build-Break/dp/0735211299',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'The Coaching Habit',
                'description' => 'Say Less, Ask More & Change the Way You Lead Forever by Michael Bungay Stanier.',
                'link' => 'https://www.amazon.com/Coaching-Habit-Less-Change-Forever/dp/0978440749',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'The Advice Trap',
                'description' => 'Be Humble, Stay Curious & Change the Way You Lead Forever by Michael Bungay Stanier.',
                'link' => 'https://www.amazon.com/Advice-Trap-Humble-Curious-Forever/dp/1989603718',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],

            // Books - Neuroscience & Emotional Intelligence
            [
                'title' => 'Your Brain at Work',
                'description' => 'Strategies for Overcoming Distraction and Increasing Your Focus by David Rock.',
                'link' => 'https://www.amazon.com/Your-Brain-Work-Strategies-Distraction/dp/0061771295',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'Quiet Leadership',
                'description' => 'Six Steps to Transforming Performance at Work by David Rock. Neuroscience-based coaching.',
                'link' => 'https://www.amazon.com/Quiet-Leadership-Steps-Transforming-Performance/dp/0060835915',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'Emotional Intelligence',
                'description' => 'Why It Can Matter More Than IQ by Daniel Goleman. Revolutionary insights into EQ.',
                'link' => 'https://www.amazon.com/Emotional-Intelligence-Matter-More-Than/dp/055338371X',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'Focus',
                'description' => 'The Hidden Driver of Excellence by Daniel Goleman. Master your attention for better results.',
                'link' => 'https://www.amazon.com/Focus-Hidden-Driver-Excellence/dp/0062114867',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],

            // Books - Mindfulness & Self-Compassion
            [
                'title' => 'Radical Acceptance',
                'description' => 'Embracing Your Life With the Heart of a Buddha by Tara Brach. Transformative mindfulness.',
                'link' => 'https://www.amazon.com/Radical-Acceptance-Embracing-Heart-Buddha/dp/0553380990',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'Radical Compassion',
                'description' => 'Learning to Love Yourself and Your World with the Practice of RAIN by Tara Brach.',
                'link' => 'https://www.amazon.com/Radical-Compassion-Learning-Yourself-Practice/dp/0525522808',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],

            // Books - Listening & Thinking Environments
            [
                'title' => 'Time to Think',
                'description' => 'Listening to Ignite the Human Mind by Nancy Kline. Create thinking environments for breakthrough.',
                'link' => 'https://www.amazon.com/Time-Think-Listening-Ignite-Human/dp/0706377265',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],

            // Books - Creativity & Life Coaching
            [
                'title' => 'The Artist\'s Way',
                'description' => 'A Spiritual Path to Higher Creativity by Julia Cameron. Unlock your creative potential.',
                'link' => 'https://www.amazon.com/Artists-Way-25th-Anniversary/dp/0143129252',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],
            [
                'title' => 'The Way of Integrity',
                'description' => 'Finding the Path to Your True Self by Martha Beck. Live in alignment with your values.',
                'link' => 'https://www.amazon.com/Way-Integrity-Finding-Path-Your/dp/1984881809',
                'type' => ResourceLibraryItemType::BOOK->value,
            ],

            // Podcasts
            [
                'title' => 'The Tim Ferriss Show',
                'description' => 'Interviews with world-class performers from various fields. Learn tactics and strategies from top achievers.',
                'link' => 'https://tim.blog/podcast/',
                'type' => ResourceLibraryItemType::PODCAST->value,
            ],
            [
                'title' => 'The Tony Robbins Podcast',
                'description' => 'Strategies and tactics for peak performance in business and life. Actionable insights from a master coach.',
                'link' => 'https://www.tonyrobbins.com/podcasts/',
                'type' => ResourceLibraryItemType::PODCAST->value,
            ],
            [
                'title' => 'Coaching for Leaders',
                'description' => 'Weekly conversations with leadership coaches and experts. Practical coaching techniques and insights.',
                'link' => 'https://coachingforleaders.com/podcast/',
                'type' => ResourceLibraryItemType::PODCAST->value,
            ],
            [
                'title' => 'The Coaching Podcast',
                'description' => 'Hosted by Lyssa deHart, featuring interviews with master coaches and thought leaders in the coaching profession.',
                'link' => 'https://www.schoolofcoaching.com/podcast',
                'type' => ResourceLibraryItemType::PODCAST->value,
            ],
            [
                'title' => 'WorkLife with Adam Grant',
                'description' => 'Organizational psychologist Adam Grant explores how to make work not suck. Evidence-based insights for coaches.',
                'link' => 'https://www.ted.com/podcasts/worklife',
                'type' => ResourceLibraryItemType::PODCAST->value,
            ],

            // Videos
            [
                'title' => 'The Power of Coaching',
                'description' => 'ICF\'s video series on professional coaching fundamentals. Learn core competencies from the gold standard.',
                'link' => 'https://www.youtube.com/c/InternationalCoachingFederation',
                'type' => ResourceLibraryItemType::VIDEO->value,
            ],
            [
                'title' => 'Simon Sinek on Leadership',
                'description' => 'Start with Why and inspiring leadership talks. Essential viewing for leadership coaches.',
                'link' => 'https://www.youtube.com/@SimonSinek',
                'type' => ResourceLibraryItemType::VIDEO->value,
            ],
            [
                'title' => 'Brené Brown on Vulnerability',
                'description' => 'The Power of Vulnerability TED Talk and other powerful presentations on courage and authenticity.',
                'link' => 'https://www.youtube.com/watch?v=iCvmsMzlF7o',
                'type' => ResourceLibraryItemType::VIDEO->value,
            ],
            [
                'title' => 'Carol Dweck: Growth Mindset',
                'description' => 'Learn about the power of believing you can improve. Essential for developmental coaching.',
                'link' => 'https://www.youtube.com/watch?v=_X0mgOOSpLU',
                'type' => ResourceLibraryItemType::VIDEO->value,
            ],

            // Courses
            [
                'title' => 'ICF Core Competencies',
                'description' => 'Professional coach training and certification program. The gold standard in coaching credentials.',
                'link' => 'https://coachingfederation.org/credentials-and-standards',
                'type' => ResourceLibraryItemType::COURSE->value,
            ],
            [
                'title' => 'Positive Psychology Coaching',
                'description' => 'Certificate in Applied Positive Psychology. Learn science-based coaching interventions.',
                'link' => 'https://www.udemy.com/topic/life-coach-training/',
                'type' => ResourceLibraryItemType::COURSE->value,
            ],
            [
                'title' => 'Executive Coaching Certification',
                'description' => 'Advanced program for coaching C-suite leaders. Specialized skills for high-stakes coaching.',
                'link' => 'https://www.coachtrainingedu.com/executive-coaching-certification/',
                'type' => ResourceLibraryItemType::COURSE->value,
            ],
            [
                'title' => 'Neuroscience for Coaches',
                'description' => 'Understanding brain-based coaching. Apply neuroscience research to your coaching practice.',
                'link' => 'https://www.neurolead.com/',
                'type' => ResourceLibraryItemType::COURSE->value,
            ],

            // Articles
            [
                'title' => 'The GROW Model Explained',
                'description' => 'A comprehensive guide to the GROW coaching framework. Goal, Reality, Options, Will.',
                'link' => 'https://www.mindtools.com/au1nm7r/the-grow-model',
                'type' => ResourceLibraryItemType::ARTICLE->value,
            ],
            [
                'title' => 'Active Listening Skills',
                'description' => 'Essential techniques for effective coaching conversations. Master the art of listening deeply.',
                'link' => 'https://www.forbes.com/sites/forbescoachescouncil/2021/07/26/15-ways-to-practice-active-listening/',
                'type' => ResourceLibraryItemType::ARTICLE->value,
            ],
            [
                'title' => 'Powerful Questions in Coaching',
                'description' => 'How to ask questions that create breakthrough insights. The foundation of great coaching.',
                'link' => 'https://www.forbes.com/sites/forbescoachescouncil/2018/08/30/16-powerful-questions-coaches-should-ask-their-clients/',
                'type' => ResourceLibraryItemType::ARTICLE->value,
            ],
            [
                'title' => 'Building Emotional Intelligence',
                'description' => 'Harvard Business Review on developing EQ. Critical capability for coaches and leaders.',
                'link' => 'https://hbr.org/topic/subject/emotional-intelligence',
                'type' => ResourceLibraryItemType::ARTICLE->value,
            ],
            [
                'title' => 'The Science of Goal Setting',
                'description' => 'Research-backed strategies for effective goal achievement. Help clients set and reach meaningful goals.',
                'link' => 'https://positivepsychology.com/goal-setting-psychology/',
                'type' => ResourceLibraryItemType::ARTICLE->value,
            ],
            [
                'title' => 'Coaching vs. Therapy',
                'description' => 'Understanding the difference and maintaining professional boundaries. Essential ethical knowledge.',
                'link' => 'https://coachingfederation.org/blog/coaching-vs-therapy',
                'type' => ResourceLibraryItemType::ARTICLE->value,
            ],
            [
                'title' => 'Creating Coaching Agreements',
                'description' => 'Best practices for establishing effective coaching relationships. Set clear expectations from the start.',
                'link' => 'https://www.forbes.com/sites/forbescoachescouncil/2019/06/06/establishing-a-coaching-agreement-15-items-to-include/',
                'type' => ResourceLibraryItemType::ARTICLE->value,
            ],
        ];

        foreach ($resources as $resource) {
            ResourceLibraryItem::updateOrCreate(
                ['title' => $resource['title']],
                $resource
            );
        }
    }
}


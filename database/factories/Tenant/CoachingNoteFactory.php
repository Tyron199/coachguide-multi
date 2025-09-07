<?php

namespace Database\Factories\Tenant;

use App\Models\Tenant\CoachingNote;
use App\Models\Tenant\CoachingSession;
use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant\CoachingNote>
 */
class CoachingNoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3, true),
            'content' => $this->faker->paragraphs(fake()->numberBetween(2, 5), true),
            'created_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Create a session-specific note
     */
    public function sessionNote(): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $this->faker->randomElement([
                'Session Summary',
                'Key Insights from Today',
                'Progress Notes',
                'Action Items Discussed',
                'Client Breakthrough',
                'Session Reflection',
                'Goals Review',
                'Challenges Addressed',
            ]),
            'content' => $this->generateSessionContent(),
        ]);
    }

    /**
     * Create a general client note (not session-specific)
     */
    public function generalNote(): static
    {
        return $this->state(fn (array $attributes) => [
            'session_id' => null,
            'title' => $this->faker->randomElement([
                'Client Check-in',
                'Progress Update',
                'Goal Adjustment',
                'Personal Observation',
                'Client Feedback',
                'Motivation Strategy',
                'Behavioral Pattern',
                'Success Milestone',
            ]),
            'content' => $this->generateGeneralContent(),
        ]);
    }

    /**
     * Generate realistic session content
     */
    private function generateSessionContent(): string
    {
        $templates = [
            "Today's session focused on {topic}. {client_name} showed {progress} and we discussed {discussion}. Key takeaways: {takeaways}. Next steps: {next_steps}.",
            
            "Excellent progress in today's session. {client_name} demonstrated {achievement}. We worked on {focus_area} and identified {insight}. Action items for next week: {actions}.",
            
            "Session highlights: {highlights}. {client_name} expressed {feelings} about {subject}. We explored {exploration} and developed strategies for {strategy_area}. Follow-up needed on {follow_up}.",
            
            "Today we addressed {challenge}. {client_name} showed great {quality} when discussing {topic}. Breakthrough moment: {breakthrough}. Plan moving forward: {plan}.",
        ];

        $replacements = [
            '{topic}' => $this->faker->randomElement(['goal setting', 'stress management', 'work-life balance', 'confidence building', 'communication skills', 'leadership development']),
            '{client_name}' => 'Client',
            '{progress}' => $this->faker->randomElement(['significant improvement', 'steady progress', 'positive attitude', 'increased awareness', 'strong commitment']),
            '{discussion}' => $this->faker->randomElement(['overcoming obstacles', 'new strategies', 'personal insights', 'recent challenges', 'success stories']),
            '{takeaways}' => $this->faker->randomElement(['increased self-awareness', 'clearer action plan', 'improved mindset', 'better coping strategies']),
            '{next_steps}' => $this->faker->randomElement(['practice daily affirmations', 'implement new routine', 'track progress weekly', 'schedule follow-up']),
            '{achievement}' => $this->faker->randomElement(['improved confidence', 'better time management', 'clearer communication', 'stronger boundaries']),
            '{focus_area}' => $this->faker->randomElement(['emotional regulation', 'goal prioritization', 'relationship dynamics', 'career development']),
            '{insight}' => $this->faker->randomElement(['limiting beliefs', 'behavior patterns', 'core values', 'personal strengths']),
            '{actions}' => $this->faker->randomElement(['journal daily thoughts', 'practice new techniques', 'implement feedback', 'set weekly goals']),
            '{highlights}' => $this->faker->randomElement(['breakthrough realization', 'emotional breakthrough', 'skill development', 'mindset shift']),
            '{feelings}' => $this->faker->randomElement(['excitement', 'concern', 'optimism', 'determination', 'curiosity']),
            '{subject}' => $this->faker->randomElement(['career goals', 'personal relationships', 'health objectives', 'life balance']),
            '{exploration}' => $this->faker->randomElement(['root causes', 'alternative approaches', 'past experiences', 'future possibilities']),
            '{strategy_area}' => $this->faker->randomElement(['stress reduction', 'goal achievement', 'relationship building', 'personal growth']),
            '{follow_up}' => $this->faker->randomElement(['homework assignments', 'resource recommendations', 'skill practice', 'goal tracking']),
            '{challenge}' => $this->faker->randomElement(['procrastination issues', 'confidence barriers', 'communication difficulties', 'work stress']),
            '{quality}' => $this->faker->randomElement(['resilience', 'openness', 'determination', 'self-reflection', 'courage']),
            '{breakthrough}' => $this->faker->randomElement(['recognized personal pattern', 'identified core value', 'overcame mental block', 'gained new perspective']),
            '{plan}' => $this->faker->randomElement(['weekly check-ins', 'gradual skill building', 'structured practice', 'milestone tracking']),
        ];

        $template = $this->faker->randomElement($templates);
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Generate realistic general note content
     */
    private function generateGeneralContent(): string
    {
        $templates = [
            "Quick check-in with client. {observation} Notable progress in {area}. {recommendation}",
            
            "Client update: {update}. Continuing to work on {focus}. {note}",
            
            "Observation: {client_behavior}. This suggests {interpretation}. Consider {suggestion} in future sessions.",
            
            "Client feedback received: {feedback}. {response} Plan to {action} moving forward.",
        ];

        $replacements = [
            '{observation}' => $this->faker->randomElement([
                'Client appears more confident than last session.',
                'Noticed improved body language and communication.',
                'Client seems more focused on goals.',
                'Positive attitude shift observed.',
                'Increased engagement in discussions.'
            ]),
            '{area}' => $this->faker->randomElement(['self-confidence', 'goal clarity', 'stress management', 'communication skills', 'work-life balance']),
            '{recommendation}' => $this->faker->randomElement([
                'Recommend continuing current approach.',
                'Suggest exploring deeper in next session.',
                'Consider introducing new techniques.',
                'May benefit from additional resources.'
            ]),
            '{update}' => $this->faker->randomElement([
                'making steady progress on goals',
                'facing new challenges but handling well',
                'showing increased self-awareness',
                'implementing strategies successfully'
            ]),
            '{focus}' => $this->faker->randomElement(['personal development', 'career advancement', 'relationship skills', 'health goals']),
            '{note}' => $this->faker->randomElement([
                'Schedule follow-up in two weeks.',
                'Client requested additional resources.',
                'Consider group session opportunity.',
                'Positive momentum building.'
            ]),
            '{client_behavior}' => $this->faker->randomElement([
                'Client consistently arrives early and prepared',
                'Increased willingness to share personal insights',
                'More proactive in suggesting solutions',
                'Demonstrates improved self-reflection skills'
            ]),
            '{interpretation}' => $this->faker->randomElement([
                'growing commitment to the coaching process',
                'increased self-confidence and ownership',
                'development of problem-solving skills',
                'stronger self-awareness and motivation'
            ]),
            '{suggestion}' => $this->faker->randomElement([
                'exploring advanced goal-setting techniques',
                'introducing leadership development topics',
                'focusing on accountability strategies',
                'discussing long-term vision planning'
            ]),
            '{feedback}' => $this->faker->randomElement([
                'sessions are very helpful and insightful',
                'feeling more confident in daily interactions',
                'appreciates the structured approach',
                'would like to explore additional topics'
            ]),
            '{response}' => $this->faker->randomElement([
                'This aligns with observed progress.',
                'Great to see positive impact.',
                'Encouraging feedback received.',
                'Validates current coaching approach.'
            ]),
            '{action}' => $this->faker->randomElement([
                'incorporate more practical exercises',
                'explore advanced techniques',
                'adjust session frequency',
                'introduce peer learning opportunities'
            ]),
        ];

        $template = $this->faker->randomElement($templates);
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
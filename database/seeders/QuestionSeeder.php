<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate existing questions for a clean seed
        Question::truncate();

        $this->seedMathematics();
        $this->seedEnglish();
        $this->seedBiology();
        $this->seedPhysics();
        $this->seedChemistry();
        $this->seedEconomics();
        $this->seedGovernment();
        $this->seedLiterature();
    }

    /**
     * Shuffle the options for a question and update correct_answer to match.
     */
    private function shuffleOptions(array $question): array
    {
        $options = $question['options'];
        $correctText = $options[$question['correct_answer']];

        shuffle($options);

        $question['options'] = $options;
        $question['correct_answer'] = array_search($correctText, $options);

        return $question;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // MATHEMATICS
    // ─────────────────────────────────────────────────────────────────────────
    private function seedMathematics(): void
    {
        $questions = [
            // ── Algebra ──────────────────────────────────────────────────────
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Algebra',
                'year' => 2022,
                'question_text' => 'Solve for x: 2x + 5 = 13',
                'options' => ['x = 2', 'x = 3', 'x = 4', 'x = 9'],
                'correct_answer' => 2,
                'explanation' => '2x = 13 − 5 = 8, so x = 8 ÷ 2 = 4.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Algebra',
                'year' => 2021,
                'question_text' => 'Find the value of x if 3(x − 2) = 2x + 4.',
                'options' => ['x = 10', 'x = −2', 'x = 2', 'x = 6'],
                'correct_answer' => 0,
                'explanation' => '3x − 6 = 2x + 4 → x = 10.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Algebra',
                'year' => 2020,
                'question_text' => 'Factorise completely: x² − 5x + 6.',
                'options' => ['(x − 2)(x − 3)', '(x + 2)(x + 3)', '(x − 1)(x − 6)', '(x + 1)(x − 6)'],
                'correct_answer' => 0,
                'explanation' => 'We need two numbers that multiply to 6 and add to −5: −2 and −3. So (x − 2)(x − 3).',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Algebra',
                'year' => 2023,
                'question_text' => 'If f(x) = 2x² − 3x + 1, find f(2).',
                'options' => ['3', '5', '7', '11'],
                'correct_answer' => 0,
                'explanation' => 'f(2) = 2(4) − 3(2) + 1 = 8 − 6 + 1 = 3.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Algebra',
                'year' => 2022,
                'question_text' => 'Solve: x² − 7x + 12 = 0.',
                'options' => ['x = 3 or 4', 'x = −3 or −4', 'x = 1 or 12', 'x = 2 or 6'],
                'correct_answer' => 0,
                'explanation' => 'Factorise: (x − 3)(x − 4) = 0 → x = 3 or 4.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Algebra',
                'year' => 2019,
                'question_text' => 'If 2^(x+1) = 32, find x.',
                'options' => ['4', '3', '5', '6'],
                'correct_answer' => 0,
                'explanation' => '32 = 2^5, so x + 1 = 5 → x = 4.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Algebra',
                'year' => 2021,
                'question_text' => 'Simplify: (3x²y)(2xy³).',
                'options' => ['6x³y⁴', '5x³y⁴', '6x²y³', '6x⁴y³'],
                'correct_answer' => 0,
                'explanation' => 'Multiply coefficients: 3×2 = 6; add exponents: x^(2+1)=x³, y^(1+3)=y⁴.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Algebra',
                'year' => 2018,
                'question_text' => 'The sum of two numbers is 15 and their product is 56. What are the numbers?',
                'options' => ['7 and 8', '6 and 9', '5 and 11', '4 and 12'],
                'correct_answer' => 0,
                'explanation' => '7 + 8 = 15 and 7 × 8 = 56.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Algebra',
                'year' => 2020,
                'question_text' => 'Find the remainder when x³ − 2x + 1 is divided by (x − 1).',
                'options' => ['0', '1', '2', '−1'],
                'correct_answer' => 0,
                'explanation' => 'Remainder theorem: substitute x = 1 → 1 − 2 + 1 = 0.',
                'difficulty' => 'hard',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Algebra',
                'year' => 2023,
                'question_text' => 'Express in partial fractions: (3x + 1) / [(x − 1)(x + 2)].',
                'options' => ['4/(x−1) − 1/(x+2)', '2/(x−1) + 1/(x+2)', '1/(x−1) + 2/(x+2)', '3/(x−1) − 2/(x+2)'],
                'correct_answer' => 0,
                'explanation' => 'Let 3x+1 = A(x+2)+B(x−1). x=1: 4=3A→A=4/3... working gives A=4/3, B=−1/3 → simplified 4/(x−1)−1/(x+2).',
                'difficulty' => 'hard',
            ],

            // ── Geometry ─────────────────────────────────────────────────────
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Geometry',
                'year' => 2022,
                'question_text' => 'Find the area of a triangle with base 10 cm and height 6 cm.',
                'options' => ['30 cm²', '60 cm²', '15 cm²', '45 cm²'],
                'correct_answer' => 0,
                'explanation' => 'Area = ½ × base × height = ½ × 10 × 6 = 30 cm².',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Geometry',
                'year' => 2023,
                'question_text' => 'The angles of a triangle are in the ratio 1:2:3. Find the smallest angle.',
                'options' => ['30°', '60°', '90°', '45°'],
                'correct_answer' => 0,
                'explanation' => 'Sum = 180°. 1+2+3=6 parts. Smallest = (1/6)×180 = 30°.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Geometry',
                'year' => 2021,
                'question_text' => 'A circle has radius 7 cm. Find its circumference. (Take π = 22/7)',
                'options' => ['44 cm', '22 cm', '154 cm', '88 cm'],
                'correct_answer' => 0,
                'explanation' => 'C = 2πr = 2 × (22/7) × 7 = 44 cm.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Geometry',
                'year' => 2022,
                'question_text' => 'Find the volume of a cylinder with radius 3 cm and height 10 cm. (Take π ≈ 3.14)',
                'options' => ['282.6 cm³', '94.2 cm³', '376.8 cm³', '188.4 cm³'],
                'correct_answer' => 0,
                'explanation' => 'V = πr²h = 3.14 × 9 × 10 = 282.6 cm³.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Geometry',
                'year' => 2020,
                'question_text' => 'In a right-angled triangle, the two legs are 3 cm and 4 cm. Find the hypotenuse.',
                'options' => ['5 cm', '7 cm', '6 cm', '8 cm'],
                'correct_answer' => 0,
                'explanation' => 'Pythagoras: c² = 3² + 4² = 9 + 16 = 25, c = 5 cm.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Geometry',
                'year' => 2019,
                'question_text' => 'What is the sum of interior angles of a hexagon?',
                'options' => ['720°', '540°', '1080°', '360°'],
                'correct_answer' => 0,
                'explanation' => 'Sum = (n − 2) × 180 = (6 − 2) × 180 = 720°.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Geometry',
                'year' => 2018,
                'question_text' => 'Two parallel lines are cut by a transversal. If one co-interior angle is 65°, find the other.',
                'options' => ['115°', '65°', '125°', '25°'],
                'correct_answer' => 0,
                'explanation' => 'Co-interior angles sum to 180°. 180 − 65 = 115°.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Geometry',
                'year' => 2021,
                'question_text' => 'Find the area of a sector with radius 6 cm and angle 60°. (Take π = 22/7)',
                'options' => ['18.86 cm²', '113.1 cm²', '37.71 cm²', '56.57 cm²'],
                'correct_answer' => 0,
                'explanation' => 'Area = (θ/360)πr² = (60/360)×(22/7)×36 = (1/6)×(792/7) ≈ 18.86 cm².',
                'difficulty' => 'hard',
            ],

            // ── Statistics ───────────────────────────────────────────────────
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Statistics',
                'year' => 2022,
                'question_text' => 'Find the mean of: 4, 7, 10, 3, 6.',
                'options' => ['6', '5', '7', '8'],
                'correct_answer' => 0,
                'explanation' => 'Mean = (4+7+10+3+6)/5 = 30/5 = 6.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Statistics',
                'year' => 2023,
                'question_text' => 'Find the median of: 12, 7, 3, 9, 5, 14, 8.',
                'options' => ['8', '7', '9', '12'],
                'correct_answer' => 0,
                'explanation' => 'Sorted: 3, 5, 7, 8, 9, 12, 14. Middle value (4th) = 8.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Statistics',
                'year' => 2021,
                'question_text' => 'The mode of 2, 3, 5, 2, 7, 5, 2 is:',
                'options' => ['2', '3', '5', '7'],
                'correct_answer' => 0,
                'explanation' => '2 appears 3 times (more than any other value), so mode = 2.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Statistics',
                'year' => 2020,
                'question_text' => 'What is the range of: 15, 3, 28, 11, 6?',
                'options' => ['25', '28', '15', '22'],
                'correct_answer' => 0,
                'explanation' => 'Range = max − min = 28 − 3 = 25.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Statistics',
                'year' => 2019,
                'question_text' => 'A bag contains 4 red and 6 blue balls. A ball is drawn at random. What is the probability it is red?',
                'options' => ['2/5', '3/5', '3/10', '1/4'],
                'correct_answer' => 0,
                'explanation' => 'P(red) = 4/(4+6) = 4/10 = 2/5.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Statistics',
                'year' => 2022,
                'question_text' => 'The standard deviation of 2, 4, 4, 4, 5, 5, 7, 9 is:',
                'options' => ['2', '4', '5', '3'],
                'correct_answer' => 0,
                'explanation' => 'Mean = 5. Variance = [(9+1+1+1+0+0+4+16)/8] = 32/8 = 4. SD = √4 = 2.',
                'difficulty' => 'hard',
            ],

            // ── Trigonometry ───────────────────────────────────────────────
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Trigonometry',
                'year' => 2024,
                'question_text' => 'If sin θ = 1/2 and θ is acute, find θ.',
                'options' => ['30°', '45°', '60°', '90°'],
                'correct_answer' => 0,
                'explanation' => 'For an acute angle, sin θ = 1/2 at θ = 30°.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Trigonometry',
                'year' => 2023,
                'question_text' => 'If tan θ = 3/4 and θ is acute, what is cos θ?',
                'options' => ['4/5', '3/5', '5/4', '7/5'],
                'correct_answer' => 0,
                'explanation' => 'Using a 3-4-5 triangle, opposite = 3, adjacent = 4, hypotenuse = 5, so cos θ = 4/5.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Trigonometry',
                'year' => 2022,
                'question_text' => 'Find the value of cos 60°.',
                'options' => ['1/2', '1', '0', 'sqrt(3)/2'],
                'correct_answer' => 0,
                'explanation' => 'From standard trigonometric values, cos 60° = 1/2.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Trigonometry',
                'year' => 2021,
                'question_text' => 'A ladder 10 m long leans against a wall. If the foot of the ladder is 6 m from the wall, how high does it reach?',
                'options' => ['8 m', '4 m', '6 m', '12 m'],
                'correct_answer' => 0,
                'explanation' => 'Using Pythagoras: height^2 = 10^2 - 6^2 = 100 - 36 = 64, so height = 8 m.',
                'difficulty' => 'medium',
            ],

            // ── Coordinate Geometry ────────────────────────────────────────
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Coordinate Geometry',
                'year' => 2024,
                'question_text' => 'Find the midpoint of the line joining (2, 3) and (8, 9).',
                'options' => ['(5, 6)', '(4, 5)', '(10, 12)', '(3, 4)'],
                'correct_answer' => 0,
                'explanation' => 'Midpoint = ((2 + 8)/2, (3 + 9)/2) = (5, 6).',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Coordinate Geometry',
                'year' => 2022,
                'question_text' => 'Find the gradient of the line passing through (1, 2) and (5, 10).',
                'options' => ['2', '1/2', '4', '8'],
                'correct_answer' => 0,
                'explanation' => 'Gradient = (10 - 2) / (5 - 1) = 8 / 4 = 2.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Coordinate Geometry',
                'year' => 2020,
                'question_text' => 'What is the distance between points (0, 0) and (3, 4)?',
                'options' => ['5', '7', '4', '3'],
                'correct_answer' => 0,
                'explanation' => 'Distance = sqrt((3 - 0)^2 + (4 - 0)^2) = sqrt(9 + 16) = 5.',
                'difficulty' => 'easy',
            ],

            // ── Sequences and Number Patterns ──────────────────────────────
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Sequences and Series',
                'year' => 2024,
                'question_text' => 'Find the next term in the arithmetic progression 4, 7, 10, 13, ...',
                'options' => ['16', '15', '17', '18'],
                'correct_answer' => 0,
                'explanation' => 'The common difference is 3, so the next term is 13 + 3 = 16.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Sequences and Series',
                'year' => 2023,
                'question_text' => 'The 5th term of the sequence 2, 4, 8, 16, ... is:',
                'options' => ['32', '24', '40', '64'],
                'correct_answer' => 0,
                'explanation' => 'Each term is multiplied by 2, so the sequence is geometric. The 5th term is 32.',
                'difficulty' => 'easy',
            ],

            // ── Mensuration and Measurement ────────────────────────────────
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Mensuration',
                'year' => 2024,
                'question_text' => 'Find the area of a circle with radius 14 cm. (Take pi = 22/7)',
                'options' => ['616 cm²', '308 cm²', '44 cm²', '154 cm²'],
                'correct_answer' => 0,
                'explanation' => 'Area = pi r^2 = (22/7) x 14^2 = (22/7) x 196 = 616 cm^2.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Mensuration',
                'year' => 2021,
                'question_text' => 'A cuboid has length 5 cm, width 4 cm and height 3 cm. Find its volume.',
                'options' => ['60 cm³', '12 cm³', '20 cm³', '30 cm³'],
                'correct_answer' => 0,
                'explanation' => 'Volume = length x width x height = 5 x 4 x 3 = 60 cm^3.',
                'difficulty' => 'easy',
            ],

            // ── Probability ─────────────────────────────────────────────────
            [
                'exam_board' => 'WAEC',
                'subject' => 'Mathematics',
                'topic' => 'Probability',
                'year' => 2023,
                'question_text' => 'A fair die is thrown once. What is the probability of getting an even number?',
                'options' => ['1/2', '1/3', '2/3', '1/6'],
                'correct_answer' => 0,
                'explanation' => 'The even numbers are 2, 4 and 6. So probability = 3/6 = 1/2.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Mathematics',
                'topic' => 'Probability',
                'year' => 2022,
                'question_text' => 'A box contains 5 red balls and 3 green balls. If one ball is picked at random, what is the probability that it is green?',
                'options' => ['3/8', '5/8', '1/3', '2/5'],
                'correct_answer' => 0,
                'explanation' => 'Total balls = 8, green balls = 3, so probability = 3/8.',
                'difficulty' => 'medium',
            ],
        ];

        foreach ($questions as $q) {
            Question::create($this->shuffleOptions($q));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ENGLISH LANGUAGE
    // ─────────────────────────────────────────────────────────────────────────
    private function seedEnglish(): void
    {
        $questions = [
            // ── Comprehension ─────────────────────────────────────────────────
            [
                'exam_board' => 'WAEC',
                'subject' => 'English',
                'topic' => 'Comprehension',
                'year' => 2022,
                'question_text' => 'Which literary device is used in "The wind whispered secrets through the trees"?',
                'options' => ['Personification', 'Simile', 'Hyperbole', 'Alliteration'],
                'correct_answer' => 0,
                'explanation' => 'The wind is given the human ability to whisper — this is personification.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'English',
                'topic' => 'Comprehension',
                'year' => 2023,
                'question_text' => '"As brave as a lion" is an example of:',
                'options' => ['Simile', 'Metaphor', 'Personification', 'Irony'],
                'correct_answer' => 0,
                'explanation' => 'A simile makes a comparison using "as … as" or "like".',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'English',
                'topic' => 'Comprehension',
                'year' => 2021,
                'question_text' => 'Identify the theme most commonly explored in Chinua Achebe\'s "Things Fall Apart".',
                'options' => ['Clash of cultures and colonialism', 'Love and romance', 'Technology and modernity', 'Electoral politics'],
                'correct_answer' => 0,
                'explanation' => 'The novel centres on the collision between Igbo traditions and British colonialism.',
                'difficulty' => 'medium',
            ],

            // ── Grammar ───────────────────────────────────────────────────────
            [
                'exam_board' => 'WAEC',
                'subject' => 'English',
                'topic' => 'Grammar',
                'year' => 2022,
                'question_text' => 'Choose the correct sentence:',
                'options' => ['Neither John nor his brothers are present.', 'Neither John nor his brothers is present.', 'Neither John or his brothers are present.', 'Neither John or his brothers is present.'],
                'correct_answer' => 0,
                'explanation' => 'With "neither … nor", the verb agrees with the subject closest to it: "brothers" (plural) → "are".',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'English',
                'topic' => 'Grammar',
                'year' => 2023,
                'question_text' => 'The word "running" in "Running is my hobby" functions as a:',
                'options' => ['Gerund', 'Participle', 'Infinitive', 'Adverb'],
                'correct_answer' => 0,
                'explanation' => 'A gerund is a verb ending in -ing used as a noun. Here "running" is the subject.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'English',
                'topic' => 'Grammar',
                'year' => 2020,
                'question_text' => 'Select the word that is correctly spelt:',
                'options' => ['Conscientious', 'Concientious', 'Consciencious', 'Conscienscious'],
                'correct_answer' => 0,
                'explanation' => '"Conscientious" is the only correctly spelt option.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'English',
                'topic' => 'Grammar',
                'year' => 2022,
                'question_text' => 'In the sentence "She gave him a book", the underlined indirect object is:',
                'options' => ['him', 'She', 'gave', 'book'],
                'correct_answer' => 0,
                'explanation' => 'The indirect object receives the direct object (book). "Him" is the indirect object.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'English',
                'topic' => 'Grammar',
                'year' => 2019,
                'question_text' => 'Which sentence uses the subjunctive mood correctly?',
                'options' => ['I wish he were here.', 'I wish he was here.', 'I wish he is here.', 'I wish he will be here.'],
                'correct_answer' => 0,
                'explanation' => 'The subjunctive uses "were" (not "was") for contrary-to-fact conditions.',
                'difficulty' => 'hard',
            ],

            // ── Vocabulary ────────────────────────────────────────────────────
            [
                'exam_board' => 'WAEC',
                'subject' => 'English',
                'topic' => 'Vocabulary',
                'year' => 2022,
                'question_text' => 'What is the synonym of "ubiquitous"?',
                'options' => ['Omnipresent', 'Rare', 'Invisible', 'Unique'],
                'correct_answer' => 0,
                'explanation' => '"Ubiquitous" means present everywhere — "omnipresent" is its synonym.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'English',
                'topic' => 'Vocabulary',
                'year' => 2021,
                'question_text' => 'Choose the antonym of "belligerent":',
                'options' => ['Peaceful', 'Aggressive', 'Hostile', 'Warlike'],
                'correct_answer' => 0,
                'explanation' => '"Belligerent" means aggressive/war-like; its antonym is "peaceful".',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'English',
                'topic' => 'Vocabulary',
                'year' => 2023,
                'question_text' => 'The idiom "bite the bullet" means:',
                'options' => ['Endure a painful situation stoically', 'Eat something hard', 'Shoot a gun', 'Be very hungry'],
                'correct_answer' => 0,
                'explanation' => 'To "bite the bullet" means to endure an unavoidable painful situation with courage.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'English',
                'topic' => 'Vocabulary',
                'year' => 2020,
                'question_text' => 'What does the prefix "mal-" mean in words like "malnutrition"?',
                'options' => ['Bad or badly', 'Good', 'Above', 'Below'],
                'correct_answer' => 0,
                'explanation' => 'The prefix "mal-" comes from Latin meaning bad or badly.',
                'difficulty' => 'easy',
            ],
        ];

        foreach ($questions as $q) {
            Question::create($this->shuffleOptions($q));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // BIOLOGY
    // ─────────────────────────────────────────────────────────────────────────
    private function seedBiology(): void
    {
        $questions = [
            // ── Cell Biology ──────────────────────────────────────────────────
            [
                'exam_board' => 'WAEC',
                'subject' => 'Biology',
                'topic' => 'Cell Biology',
                'year' => 2022,
                'question_text' => 'Which organelle is known as the "powerhouse of the cell"?',
                'options' => ['Mitochondria', 'Nucleus', 'Ribosome', 'Endoplasmic Reticulum'],
                'correct_answer' => 0,
                'explanation' => 'Mitochondria produce ATP through cellular respiration, earning the nickname "powerhouse of the cell".',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Biology',
                'topic' => 'Cell Biology',
                'year' => 2023,
                'question_text' => 'The process by which water moves across a semi-permeable membrane from a region of low solute concentration to high is called:',
                'options' => ['Osmosis', 'Diffusion', 'Active transport', 'Endocytosis'],
                'correct_answer' => 0,
                'explanation' => 'Osmosis is the specific movement of water through a semi-permeable membrane down its concentration gradient.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Biology',
                'topic' => 'Cell Biology',
                'year' => 2021,
                'question_text' => 'Which structure is found in plant cells but NOT in animal cells?',
                'options' => ['Cell wall', 'Mitochondria', 'Nucleus', 'Ribosome'],
                'correct_answer' => 0,
                'explanation' => 'Plant cells have a rigid cell wall made of cellulose; animal cells do not.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Biology',
                'topic' => 'Cell Biology',
                'year' => 2022,
                'question_text' => 'The site of protein synthesis in a cell is the:',
                'options' => ['Ribosome', 'Golgi apparatus', 'Lysosome', 'Vacuole'],
                'correct_answer' => 0,
                'explanation' => 'Ribosomes translate mRNA into proteins — they are the site of protein synthesis.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Biology',
                'topic' => 'Cell Biology',
                'year' => 2020,
                'question_text' => 'Chromosomes are made of:',
                'options' => ['DNA and protein', 'RNA only', 'Lipids and carbohydrates', 'Protein only'],
                'correct_answer' => 0,
                'explanation' => 'Chromosomes consist of DNA wrapped around histone proteins.',
                'difficulty' => 'medium',
            ],

            // ── Ecology ───────────────────────────────────────────────────────
            [
                'exam_board' => 'WAEC',
                'subject' => 'Biology',
                'topic' => 'Ecology',
                'year' => 2022,
                'question_text' => 'Organisms that produce their own food using sunlight are called:',
                'options' => ['Autotrophs', 'Heterotrophs', 'Decomposers', 'Parasites'],
                'correct_answer' => 0,
                'explanation' => 'Autotrophs (producers) make food by photosynthesis using sunlight, CO₂, and water.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Biology',
                'topic' => 'Ecology',
                'year' => 2023,
                'question_text' => 'A food chain always begins with:',
                'options' => ['A producer', 'A primary consumer', 'A secondary consumer', 'A decomposer'],
                'correct_answer' => 0,
                'explanation' => 'Food chains start with a producer (plant) that captures solar energy.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Biology',
                'topic' => 'Ecology',
                'year' => 2021,
                'question_text' => 'Which of the following is a biotic component of an ecosystem?',
                'options' => ['Trees', 'Water', 'Sunlight', 'Soil minerals'],
                'correct_answer' => 0,
                'explanation' => 'Biotic components are living organisms. Trees are living; water, sunlight, and soil minerals are abiotic.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Biology',
                'topic' => 'Ecology',
                'year' => 2020,
                'question_text' => 'The nitrogen cycle involves bacteria that convert nitrates back into nitrogen gas. This process is:',
                'options' => ['Denitrification', 'Nitrification', 'Nitrogen fixation', 'Ammonification'],
                'correct_answer' => 0,
                'explanation' => 'Denitrification is the conversion of nitrates into atmospheric nitrogen (N₂) by denitrifying bacteria.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Biology',
                'topic' => 'Ecology',
                'year' => 2019,
                'question_text' => 'The relationship where both organisms benefit is called:',
                'options' => ['Mutualism', 'Parasitism', 'Commensalism', 'Predation'],
                'correct_answer' => 0,
                'explanation' => 'In mutualism, both organisms involved benefit from the interaction (e.g., bees and flowers).',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Biology',
                'topic' => 'Ecology',
                'year' => 2022,
                'question_text' => 'Which level of organisation is studied in ecology?',
                'options' => ['Population, community, and ecosystem', 'Organelle and cell', 'Tissue and organ', 'Atom and molecule'],
                'correct_answer' => 0,
                'explanation' => 'Ecology studies biological organisation at the levels of population, community, and ecosystem.',
                'difficulty' => 'medium',
            ],

            // ── Genetics ──────────────────────────────────────────────────────
            [
                'exam_board' => 'WAEC',
                'subject' => 'Biology',
                'topic' => 'Genetics',
                'year' => 2022,
                'question_text' => 'In a monohybrid cross between Tt × Tt, the expected ratio of tall to short offspring is:',
                'options' => ['3:1', '1:1', '1:3', '1:2:1'],
                'correct_answer' => 0,
                'explanation' => 'Tt × Tt gives TT, Tt, Tt, tt → 3 tall : 1 short.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Biology',
                'topic' => 'Genetics',
                'year' => 2023,
                'question_text' => 'Which of the following is a sex-linked trait?',
                'options' => ['Colour blindness', 'Blood group', 'Height', 'Skin colour'],
                'correct_answer' => 0,
                'explanation' => 'Colour blindness is carried on the X chromosome and is therefore sex-linked.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Biology',
                'topic' => 'Genetics',
                'year' => 2021,
                'question_text' => 'The physical appearance of an organism is its:',
                'options' => ['Phenotype', 'Genotype', 'Allele', 'Locus'],
                'correct_answer' => 0,
                'explanation' => 'Phenotype refers to the observable traits; genotype is the genetic makeup.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Biology',
                'topic' => 'Genetics',
                'year' => 2022,
                'question_text' => 'Mendel\'s law of independent assortment states that:',
                'options' => ['Genes for different traits are inherited independently of each other', 'Each offspring inherits one allele from each parent', 'Dominant alleles always mask recessive ones', 'All traits are inherited together'],
                'correct_answer' => 0,
                'explanation' => 'Independent assortment: alleles of different genes segregate independently during gamete formation.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Biology',
                'topic' => 'Genetics',
                'year' => 2019,
                'question_text' => 'A mutation that changes one amino acid in a protein is called a:',
                'options' => ['Point mutation', 'Frameshift mutation', 'Chromosomal mutation', 'Gene deletion'],
                'correct_answer' => 0,
                'explanation' => 'A point mutation is a single nucleotide change that may alter one amino acid in the resulting protein.',
                'difficulty' => 'hard',
            ],

            // ── Human Physiology ──────────────────────────────────────────────
            [
                'exam_board' => 'WAEC',
                'subject' => 'Biology',
                'topic' => 'Human Physiology',
                'year' => 2022,
                'question_text' => 'Which blood component is responsible for carrying oxygen?',
                'options' => ['Red blood cells (erythrocytes)', 'White blood cells (leukocytes)', 'Platelets', 'Plasma'],
                'correct_answer' => 0,
                'explanation' => 'Red blood cells contain haemoglobin, which binds and transports oxygen.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Biology',
                'topic' => 'Human Physiology',
                'year' => 2023,
                'question_text' => 'The process by which glucose is broken down to release energy is called:',
                'options' => ['Cellular respiration', 'Photosynthesis', 'Digestion', 'Excretion'],
                'correct_answer' => 0,
                'explanation' => 'Cellular respiration converts glucose + oxygen into ATP, CO₂, and water.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Biology',
                'topic' => 'Human Physiology',
                'year' => 2021,
                'question_text' => 'Which organ produces bile?',
                'options' => ['Liver', 'Pancreas', 'Stomach', 'Gallbladder'],
                'correct_answer' => 0,
                'explanation' => 'The liver produces bile; the gallbladder stores and releases it into the small intestine.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Biology',
                'topic' => 'Human Physiology',
                'year' => 2020,
                'question_text' => 'What is the function of the nephron in the kidney?',
                'options' => ['Filtration and reabsorption of blood to form urine', 'Producing digestive enzymes', 'Regulating heart rate', 'Producing hormones'],
                'correct_answer' => 0,
                'explanation' => 'Nephrons are the functional units of the kidney that filter blood and selectively reabsorb substances to produce urine.',
                'difficulty' => 'medium',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Biology',
                'topic' => 'Human Physiology',
                'year' => 2019,
                'question_text' => 'The part of the brain responsible for balance and coordination is the:',
                'options' => ['Cerebellum', 'Cerebrum', 'Medulla oblongata', 'Hypothalamus'],
                'correct_answer' => 0,
                'explanation' => 'The cerebellum controls balance, coordination, and fine motor control.',
                'difficulty' => 'medium',
            ],
        ];

        foreach ($questions as $q) {
            Question::create($this->shuffleOptions($q));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PHYSICS
    // ─────────────────────────────────────────────────────────────────────────
    private function seedPhysics(): void
    {
        $questions = [
            [
                'exam_board' => 'WAEC',
                'subject' => 'Physics',
                'topic' => 'Mechanics',
                'year' => 2022,
                'question_text' => 'Which of the following is a vector quantity?',
                'options' => ['Velocity', 'Speed', 'Mass', 'Temperature'],
                'correct_answer' => 0,
                'explanation' => 'Velocity has both magnitude and direction, making it a vector quantity.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Physics',
                'topic' => 'Mechanics',
                'year' => 2023,
                'question_text' => 'A car accelerates from rest to 20 m/s in 5 seconds. Find its acceleration.',
                'options' => ['4 m/s²', '100 m/s²', '15 m/s²', '25 m/s²'],
                'correct_answer' => 0,
                'explanation' => 'Acceleration = (Change in velocity) / time = (20 - 0) / 5 = 4 m/s².',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'WAEC',
                'subject' => 'Physics',
                'topic' => 'Waves',
                'year' => 2021,
                'question_text' => 'The distance between two successive crests of a wave is called:',
                'options' => ['Wavelength', 'Amplitude', 'Frequency', 'Period'],
                'correct_answer' => 0,
                'explanation' => 'Wavelength is the distance between consecutive corresponding points of the same phase, such as crests.',
                'difficulty' => 'easy',
            ],
        ];

        foreach ($questions as $q) {
            Question::create($this->shuffleOptions($q));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CHEMISTRY
    // ─────────────────────────────────────────────────────────────────────────
    private function seedChemistry(): void
    {
        $questions = [
            [
                'exam_board' => 'WAEC',
                'subject' => 'Chemistry',
                'topic' => 'Atomic Structure',
                'year' => 2022,
                'question_text' => 'Which subatomic particle has a negative charge?',
                'options' => ['Electron', 'Proton', 'Neutron', 'Nucleus'],
                'correct_answer' => 0,
                'explanation' => 'Electrons are negatively charged, protons are positive, and neutrons are neutral.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Chemistry',
                'topic' => 'Chemical Bonding',
                'year' => 2023,
                'question_text' => 'An ionic bond is formed by:',
                'options' => ['Transfer of electrons', 'Sharing of electrons', 'Sea of electrons', 'Van der Waals forces'],
                'correct_answer' => 0,
                'explanation' => 'Ionic bonds involve the transfer of electrons from a metal to a non-metal.',
                'difficulty' => 'easy',
            ],
        ];

        foreach ($questions as $q) {
            Question::create($this->shuffleOptions($q));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // ECONOMICS
    // ─────────────────────────────────────────────────────────────────────────
    private function seedEconomics(): void
    {
        $questions = [
            [
                'exam_board' => 'WAEC',
                'subject' => 'Economics',
                'topic' => 'Basic Concepts',
                'year' => 2022,
                'question_text' => 'Opportunity cost is defined as:',
                'options' => ['The alternative forgone', 'The monetary cost of a good', 'The total cost of production', 'The implicit cost'],
                'correct_answer' => 0,
                'explanation' => 'Opportunity cost represents the next best alternative forgone when making a choice.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Economics',
                'topic' => 'Demand and Supply',
                'year' => 2021,
                'question_text' => 'The law of demand states that:',
                'options' => ['As price increases, quantity demanded decreases', 'As price increases, quantity demanded increases', 'Price and quantity demanded are directly proportional', 'Demand creates its own supply'],
                'correct_answer' => 0,
                'explanation' => 'The law of demand dictates an inverse relationship between price and quantity demanded.',
                'difficulty' => 'medium',
            ],
        ];

        foreach ($questions as $q) {
            Question::create($this->shuffleOptions($q));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GOVERNMENT
    // ─────────────────────────────────────────────────────────────────────────
    private function seedGovernment(): void
    {
        $questions = [
            [
                'exam_board' => 'WAEC',
                'subject' => 'Government',
                'topic' => 'Basic Concepts',
                'year' => 2020,
                'question_text' => 'A system of government where power is vested in a single individual is called:',
                'options' => ['Autocracy', 'Democracy', 'Oligarchy', 'Theocracy'],
                'correct_answer' => 0,
                'explanation' => 'Autocracy is a system of government by one person with absolute power.',
                'difficulty' => 'easy',
            ],
            [
                'exam_board' => 'JAMB',
                'subject' => 'Government',
                'topic' => 'Constitution',
                'year' => 2022,
                'question_text' => 'The fundamental laws and principles that govern a state are contained in the:',
                'options' => ['Constitution', 'Manifesto', 'Decree', 'Hansard'],
                'correct_answer' => 0,
                'explanation' => 'A constitution is the aggregate of fundamental principles or established precedents that constitute the legal basis of a polity.',
                'difficulty' => 'easy',
            ],
        ];

        foreach ($questions as $q) {
            Question::create($this->shuffleOptions($q));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // LITERATURE
    // ─────────────────────────────────────────────────────────────────────────
    private function seedLiterature(): void
    {
        $questions = [
            [
                'exam_board' => 'WAEC',
                'subject' => 'Literature',
                'topic' => 'Literary Appreciation',
                'year' => 2021,
                'question_text' => 'A literary work that ends unhappily, often with the downfall of the main character, is a:',
                'options' => ['Tragedy', 'Comedy', 'Farce', 'Satire'],
                'correct_answer' => 0,
                'explanation' => 'A tragedy is a genre of drama based on human suffering and, mainly, the terrible or sorrowful events that befall a main character.',
                'difficulty' => 'easy',
            ],
        ];

        foreach ($questions as $q) {
            Question::create($this->shuffleOptions($q));
        }
    }
}

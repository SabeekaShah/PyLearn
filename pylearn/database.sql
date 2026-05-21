-- PyLearn Advanced Database Schema
CREATE DATABASE IF NOT EXISTS pylearn CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE pylearn;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT 'default.png',
    role ENUM('student','admin') DEFAULT 'student',
    xp INT DEFAULT 0,
    level INT DEFAULT 1,
    streak INT DEFAULT 0,
    last_login DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    slug VARCHAR(150) UNIQUE NOT NULL,
    category VARCHAR(60) NOT NULL,
    difficulty ENUM('beginner','intermediate','advanced') DEFAULT 'beginner',
    content LONGTEXT NOT NULL,
    xp_reward INT DEFAULT 10,
    order_num INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS quizzes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT,
    question TEXT NOT NULL,
    option_a VARCHAR(255),
    option_b VARCHAR(255),
    option_c VARCHAR(255),
    option_d VARCHAR(255),
    correct_answer CHAR(1) NOT NULL,
    explanation TEXT,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    lesson_id INT,
    completed TINYINT(1) DEFAULT 0,
    quiz_score INT DEFAULT 0,
    completed_at TIMESTAMP NULL,
    UNIQUE KEY unique_progress (user_id, lesson_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS code_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    lesson_id INT,
    code TEXT NOT NULL,
    output TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    description VARCHAR(255),
    icon VARCHAR(10) NOT NULL,
    condition_type VARCHAR(50),
    condition_value INT
);

CREATE TABLE IF NOT EXISTS user_badges (
    user_id INT,
    badge_id INT,
    earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, badge_id)
);

CREATE TABLE IF NOT EXISTS leaderboard (
    user_id INT PRIMARY KEY,
    total_xp INT DEFAULT 0,
    lessons_done INT DEFAULT 0,
    badges_count INT DEFAULT 0
);

-- Seed: Admin user (password: admin123)
INSERT IGNORE INTO users (username, email, password, role, xp, level) VALUES
('admin', 'admin@pylearn.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 500, 5),
('alice', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 320, 4),
('bob', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 150, 2);

-- Seed: Badges
INSERT IGNORE INTO badges (id, name, description, icon, condition_type, condition_value) VALUES
(1, 'First Step', 'Complete your first lesson', '🎯', 'lessons', 1),
(2, 'Quiz Master', 'Score 100% on any quiz', '🏆', 'perfect_quiz', 1),
(3, 'Streak 3', '3-day learning streak', '🔥', 'streak', 3),
(4, 'Python Novice', 'Complete 5 lessons', '🐍', 'lessons', 5),
(5, 'XP Hunter', 'Earn 200 XP', '⚡', 'xp', 200),
(6, 'Code Wizard', 'Submit 10 code snippets', '🧙', 'submissions', 10);

-- Seed: Lessons
INSERT IGNORE INTO lessons (id, title, slug, category, difficulty, content, xp_reward, order_num) VALUES
(1, 'Introduction to Python', 'intro-python', 'Basics', 'beginner',
'<h2>Welcome to Python!</h2><p>Python is a versatile, beginner-friendly language created by Guido van Rossum in 1991.</p>
<h3>Why Python?</h3><ul><li>Simple, readable syntax</li><li>Huge community & libraries</li><li>Used in AI, web, automation</li></ul>
<h3>Your First Program</h3><pre><code class="language-python">print("Hello, World!")\nprint("Welcome to PyLearn!")</code></pre>
<p>The <code>print()</code> function outputs text to the screen. Every Python journey starts here!</p>
<div class="tip-box">💡 <strong>Tip:</strong> Python uses indentation instead of curly braces!</div>', 15, 1),

(2, 'Variables & Data Types', 'variables-types', 'Basics', 'beginner',
'<h2>Variables & Data Types</h2><p>Variables store data values. Python is dynamically typed.</p>
<h3>Basic Types</h3><pre><code class="language-python">name = "Python"      # str\nversion = 3.11       # float\nyear = 1991          # int\nis_cool = True       # bool\n\nprint(type(name))    # &lt;class str&gt;</code></pre>
<h3>Type Conversion</h3><pre><code class="language-python">age = "25"\nage_int = int(age)   # Convert string to int\nprint(age_int + 5)   # 30</code></pre>
<div class="tip-box">💡 Use <code>type()</code> to check a variable type anytime!</div>', 20, 2),

(3, 'Control Flow: if/else', 'control-flow', 'Basics', 'beginner',
'<h2>Control Flow</h2><p>Control the logic of your program with conditions.</p>
<pre><code class="language-python">score = 85\n\nif score >= 90:\n    print("Grade: A")\nelif score >= 80:\n    print("Grade: B")\nelse:\n    print("Grade: C or below")</code></pre>
<h3>Comparison Operators</h3><ul><li><code>==</code> Equal</li><li><code>!=</code> Not equal</li><li><code>&gt;=</code> Greater or equal</li><li><code>and</code>, <code>or</code>, <code>not</code></li></ul>
<div class="tip-box">💡 Indentation (4 spaces) defines the block in Python!</div>', 20, 3),

(4, 'Loops: for & while', 'loops', 'Basics', 'beginner',
'<h2>Loops</h2><p>Loops repeat code until a condition is met.</p>
<h3>For Loop</h3><pre><code class="language-python">fruits = ["apple","banana","cherry"]\nfor fruit in fruits:\n    print(f"I like {fruit}")</code></pre>
<h3>While Loop</h3><pre><code class="language-python">count = 0\nwhile count &lt; 5:\n    print(count)\n    count += 1</code></pre>
<h3>range()</h3><pre><code class="language-python">for i in range(1, 6):\n    print(i)   # 1 2 3 4 5</code></pre>
<div class="tip-box">💡 Use <code>break</code> to exit a loop, <code>continue</code> to skip an iteration!</div>', 20, 4),

(5, 'Functions', 'functions', 'Intermediate', 'intermediate',
'<h2>Functions</h2><p>Functions are reusable blocks of code defined with <code>def</code>.</p>
<pre><code class="language-python">def greet(name, greeting="Hello"):\n    return f"{greeting}, {name}!"\n\nprint(greet("Alice"))          # Hello, Alice!\nprint(greet("Bob","Hi"))      # Hi, Bob!</code></pre>
<h3>Lambda Functions</h3><pre><code class="language-python">square = lambda x: x ** 2\nprint(square(5))   # 25\n\nnums = [3,1,4,1,5]\nnums.sort(key=lambda x: -x)\nprint(nums)  # [5,4,3,1,1]</code></pre>
<div class="tip-box">💡 Default arguments must come after required arguments!</div>', 25, 5),

(6, 'Lists & Tuples', 'lists-tuples', 'Intermediate', 'intermediate',
'<h2>Lists & Tuples</h2>
<h3>Lists (mutable)</h3><pre><code class="language-python">nums = [1, 2, 3, 4, 5]\nnums.append(6)       # [1,2,3,4,5,6]\nnums.remove(3)       # [1,2,4,5,6]\nprint(nums[0:3])     # [1,2,4]</code></pre>
<h3>Tuples (immutable)</h3><pre><code class="language-python">coords = (10.5, 20.3)\nx, y = coords        # unpacking\nprint(x, y)          # 10.5 20.3</code></pre>
<h3>List Comprehension</h3><pre><code class="language-python">squares = [x**2 for x in range(10) if x % 2 == 0]\nprint(squares)  # [0,4,16,36,64]</code></pre>', 25, 6),

(7, 'Dictionaries & Sets', 'dicts-sets', 'Intermediate', 'intermediate',
'<h2>Dictionaries & Sets</h2>
<h3>Dictionaries</h3><pre><code class="language-python">student = {"name": "Alice", "age": 20, "gpa": 3.8}\nprint(student["name"])          # Alice\nstudent["year"] = 2             # add key\ndel student["gpa"]              # remove key\nfor k, v in student.items():\n    print(f"{k}: {v}")</code></pre>
<h3>Sets</h3><pre><code class="language-python">a = {1, 2, 3, 4}\nb = {3, 4, 5, 6}\nprint(a & b)   # {3, 4} intersection\nprint(a | b)   # {1,2,3,4,5,6} union\nprint(a - b)   # {1, 2} difference</code></pre>', 25, 7),

(8, 'OOP: Classes & Objects', 'oop-classes', 'Advanced', 'advanced',
'<h2>Object-Oriented Programming</h2>
<pre><code class="language-python">class Animal:\n    def __init__(self, name, sound):\n        self.name = name\n        self.sound = sound\n\n    def speak(self):\n        return f"{self.name} says {self.sound}!"\n\nclass Dog(Animal):\n    def __init__(self, name):\n        super().__init__(name, "Woof")\n\n    def fetch(self, item):\n        return f"{self.name} fetches the {item}!"\n\ndog = Dog("Rex")\nprint(dog.speak())       # Rex says Woof!\nprint(dog.fetch("ball")) # Rex fetches the ball!</code></pre>
<div class="tip-box">💡 Inheritance lets a class reuse code from a parent class!</div>', 30, 8);

-- Seed: Quizzes
INSERT IGNORE INTO quizzes (lesson_id, question, option_a, option_b, option_c, option_d, correct_answer, explanation) VALUES
(1, 'Which function is used to display output in Python?', 'echo()', 'print()', 'console.log()', 'display()', 'B', 'print() is Python''s built-in output function.'),
(1, 'Who created Python?', 'James Gosling', 'Brendan Eich', 'Guido van Rossum', 'Dennis Ritchie', 'C', 'Guido van Rossum created Python in 1991.'),
(2, 'What type is the value True in Python?', 'str', 'int', 'bool', 'float', 'C', 'True and False are boolean values in Python.'),
(2, 'How do you convert "25" to integer?', 'str("25")', 'int("25")', 'float("25")', 'num("25")', 'B', 'int() converts a string to integer.'),
(3, 'What keyword starts a conditional in Python?', 'when', 'if', 'check', 'cond', 'B', 'if is the keyword for conditionals in Python.'),
(4, 'What does range(3) produce?', '1,2,3', '0,1,2,3', '0,1,2', '1,2', 'C', 'range(3) yields 0, 1, 2 — three values starting from 0.'),
(5, 'How do you define a function?', 'function f():', 'def f():', 'fun f():', 'func f():', 'B', 'def is the keyword to define functions in Python.'),
(6, 'Which is mutable?', 'Tuple', 'String', 'List', 'Int', 'C', 'Lists are mutable (can be changed); tuples are not.'),
(7, 'What symbol denotes a dictionary in Python?', '[]', '()', '{}', '<>', 'C', 'Curly braces {} are used for dictionaries and sets.'),
(8, 'What method initializes a class?', '__start__', '__init__', '__new__', '__begin__', 'B', '__init__ is the constructor method for Python classes.');

-- Seed leaderboard
INSERT IGNORE INTO leaderboard (user_id, total_xp, lessons_done, badges_count)
SELECT id, xp, 0, 0 FROM users;

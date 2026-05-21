// PyLearn - Main JS

// Toggle mobile menu
function toggleMenu() {
    document.getElementById('mobileMenu').classList.toggle('open');
}

// Toast notification
function showToast(msg, type = 'success') {
    let toast = document.getElementById('toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'toast';
        toast.className = 'toast';
        document.body.appendChild(toast);
    }
    toast.innerHTML = `<span>${type === 'success' ? '✅' : '❌'}</span> ${msg}`;
    toast.className = `toast toast-${type} show`;
    setTimeout(() => toast.classList.remove('show'), 3500);
}

// Animate numbers on dashboard
function animateCount(el) {
    const target = parseInt(el.dataset.target);
    let current = 0;
    const step = Math.ceil(target / 40);
    const timer = setInterval(() => {
        current = Math.min(current + step, target);
        el.textContent = current;
        if (current >= target) clearInterval(timer);
    }, 30);
}
document.querySelectorAll('[data-target]').forEach(animateCount);

// Progress bar animation
document.querySelectorAll('.level-bar-fill, .progress-bar-fill').forEach(bar => {
    const w = bar.dataset.width || bar.style.width;
    bar.style.width = '0';
    setTimeout(() => bar.style.width = w, 100);
});

// ============ CODE PLAYGROUND ============
const exampleSnippets = {
    hello: `# Hello World\nprint("Hello, World!")\nprint("Welcome to PyLearn!")`,
    fibonacci: `# Fibonacci sequence\ndef fibonacci(n):\n    a, b = 0, 1\n    result = []\n    for _ in range(n):\n        result.append(a)\n        a, b = b, a + b\n    return result\n\nprint(fibonacci(10))`,
    factorial: `# Factorial with recursion\ndef factorial(n):\n    if n <= 1:\n        return 1\n    return n * factorial(n - 1)\n\nfor i in range(1, 11):\n    print(f"{i}! = {factorial(i)}")`,
    listcomp: `# List comprehension examples\nsquares = [x**2 for x in range(1, 11)]\nprint("Squares:", squares)\n\nevens = [x for x in range(20) if x % 2 == 0]\nprint("Evens:", evens)\n\nwords = ["hello", "world", "python"]\nupper = [w.upper() for w in words]\nprint("Upper:", upper)`,
    classex: `# OOP Example\nclass Student:\n    def __init__(self, name, grade):\n        self.name = name\n        self.grade = grade\n        self.courses = []\n\n    def enroll(self, course):\n        self.courses.append(course)\n        print(f"{self.name} enrolled in {course}")\n\n    def __str__(self):\n        return f"Student({self.name}, Grade: {self.grade})"\n\ns = Student("Alice", "A")\ns.enroll("Python 101")\ns.enroll("Data Structures")\nprint(s)\nprint("Courses:", s.courses)`,
    dict: `# Dictionary operations\nstudent = {\n    "name": "Bob",\n    "age": 20,\n    "grades": {"Math": 95, "Python": 98, "English": 87}\n}\n\nprint("Name:", student["name"])\nprint("Python grade:", student["grades"]["Python"])\n\navg = sum(student["grades"].values()) / len(student["grades"])\nprint(f"Average grade: {avg:.1f}")\n\nfor subject, grade in student["grades"].items():\n    print(f"  {subject}: {grade}")`
};

function loadSnippet(key) {
    const editor = document.getElementById('code-editor');
    if (editor && exampleSnippets[key]) {
        editor.value = exampleSnippets[key];
    }
}

// Simple Python interpreter simulation (client-side for demo)
// Note: In production, this would call a backend execution API
function runCode() {
    const code = document.getElementById('code-editor')?.value || '';
    const output = document.getElementById('output-box');
    if (!output) return;

    output.textContent = 'Running...';

    // Send to backend
    fetch('/pylearn/run_code.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code })
    })
    .then(r => r.json())
    .then(data => {
        output.textContent = data.output || data.error || 'No output';
        if (data.saved) showToast('Code saved!');
    })
    .catch(() => {
        // Fallback: basic simulation for demo
        output.textContent = simulatePython(code);
    });
}

// Basic client-side Python simulator for fallback
function simulatePython(code) {
    let out = '';
    const lines = code.split('\n');
    const vars = {};
    const prints = [];

    lines.forEach(line => {
        const trimmed = line.trim();
        if (trimmed.startsWith('#') || trimmed === '') return;

        // Handle print statements
        const printMatch = trimmed.match(/^print\s*\((.+)\)$/);
        if (printMatch) {
            let expr = printMatch[1];
            // Replace f-strings roughly
            expr = expr.replace(/f"([^"]*)"/g, (m, s) => `"${s}"`);
            expr = expr.replace(/f'([^']*)'/g, (m, s) => `'${s}'`);
            // Replace known vars
            Object.entries(vars).forEach(([k, v]) => {
                expr = expr.replace(new RegExp('\\b' + k + '\\b', 'g'), JSON.stringify(v));
            });
            // Eval safely
            try {
                // Replace Python-specific syntax
                expr = expr.replace(/True/g,'true').replace(/False/g,'false').replace(/None/g,'null');
                const result = Function('"use strict"; return (' + expr + ')')();
                prints.push(Array.isArray(result) ? JSON.stringify(result).replace(/,/g,', ') : String(result));
            } catch {
                prints.push('[expression]');
            }
        }
        // Basic assignments
        const assignMatch = trimmed.match(/^(\w+)\s*=\s*(.+)$/);
        if (assignMatch && !trimmed.includes('def ') && !trimmed.includes('if ') && !trimmed.includes('for ')) {
            try {
                let val = assignMatch[2];
                val = val.replace(/True/g,'true').replace(/False/g,'false').replace(/None/g,'null');
                Object.entries(vars).forEach(([k, v]) => {
                    val = val.replace(new RegExp('\\b' + k + '\\b', 'g'), JSON.stringify(v));
                });
                vars[assignMatch[1]] = Function('"use strict"; return (' + val + ')')();
            } catch(e) {}
        }
    });

    return prints.length ? prints.join('\n') : '(Code simulated — install Python backend for full execution)';
}

// Tab key in textarea
document.addEventListener('DOMContentLoaded', () => {
    const editor = document.getElementById('code-editor');
    if (editor) {
        editor.addEventListener('keydown', e => {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = editor.selectionStart;
                const end = editor.selectionEnd;
                editor.value = editor.value.substring(0, start) + '    ' + editor.value.substring(end);
                editor.selectionStart = editor.selectionEnd = start + 4;
            }
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                runCode();
            }
        });
    }

    // Keyboard shortcut hint
    const runBtn = document.getElementById('run-btn');
    if (runBtn) {
        runBtn.title = 'Run code (Ctrl+Enter)';
    }
});

// Quiz functionality
function submitQuiz(lessonId) {
    const form = document.getElementById('quiz-form');
    if (!form) return;

    const questions = form.querySelectorAll('.quiz-q');
    let correct = 0;
    let total = questions.length;
    let answered = 0;

    questions.forEach(q => {
        const selected = q.querySelector('input[type=radio]:checked');
        if (selected) answered++;
    });

    if (answered < total) {
        showToast('Please answer all questions!', 'error');
        return;
    }

    // Send to backend
    const formData = new FormData(form);
    formData.append('lesson_id', lessonId);

    fetch('/pylearn/submit_quiz.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        showQuizResults(data);
    })
    .catch(() => showToast('Error submitting quiz', 'error'));
}

function showQuizResults(data) {
    const form = document.getElementById('quiz-form');
    if (!form) return;

    // Show correct/wrong on each option
    data.results.forEach(r => {
        const qEl = document.getElementById('q-' + r.quiz_id);
        if (!qEl) return;
        qEl.querySelectorAll('.quiz-opt').forEach(opt => {
            const val = opt.dataset.value;
            if (val === r.correct) opt.classList.add('correct');
            else if (val === r.selected && val !== r.correct) opt.classList.add('wrong');
            opt.querySelector('input') && (opt.querySelector('input').disabled = true);
        });
        // Show explanation
        const exp = qEl.querySelector('.quiz-explanation');
        if (exp) exp.style.display = 'block';
    });

    // Show score
    const scoreEl = document.getElementById('quiz-score');
    if (scoreEl) {
        const pct = Math.round((data.score / data.total) * 100);
        scoreEl.innerHTML = `
            <div class="quiz-score">
                ${data.score}/${data.total} correct (${pct}%)
                ${pct === 100 ? '🏆 Perfect!' : pct >= 70 ? '✅ Passed!' : '📚 Keep practicing!'}
                <br><small style="color:var(--text-muted)">+${data.xp_earned} XP earned</small>
            </div>`;
        scoreEl.style.display = 'block';
    }

    document.getElementById('submit-quiz-btn')?.setAttribute('disabled', true);
    if (data.xp_earned > 0) showToast(`+${data.xp_earned} XP earned! 🎉`);
    if (data.badge) showToast(`🏆 Badge earned: ${data.badge}!`);
    if (data.level_up) showToast(`⬆️ Level Up! You're now level ${data.new_level}!`);
}

// Confirm delete
function confirmDelete(msg, url) {
    if (confirm(msg || 'Are you sure?')) window.location.href = url;
}

<?php
require_once 'includes/config.php';
requireLogin();
$pageTitle = 'Code Playground';
$user = currentUser($pdo);

// Recent submissions
$recent = $pdo->prepare("SELECT * FROM code_submissions WHERE user_id=? ORDER BY submitted_at DESC LIMIT 5");
$recent->execute([$user['id']]);
$submissions = $recent->fetchAll();
?>
<?php include 'includes/header.php'; ?>

<div class="playground">
    <h1>💻 Code Playground</h1>
    <p style="color:var(--text-muted);margin-bottom:1.5rem;">Write and experiment with Python code. Press <kbd style="background:var(--card);padding:.1rem .4rem;border-radius:4px;border:1px solid var(--border);">Ctrl+Enter</kbd> to run.</p>

    <!-- Example Snippets -->
    <div style="margin-bottom:1rem;">
        <span style="color:var(--text-muted);font-size:.85rem;margin-right:.5rem;">Load example:</span>
        <div class="example-snippets">
            <button class="snippet-btn" onclick="loadSnippet('hello')">👋 Hello World</button>
            <button class="snippet-btn" onclick="loadSnippet('fibonacci')">🐇 Fibonacci</button>
            <button class="snippet-btn" onclick="loadSnippet('factorial')">🔢 Factorial</button>
            <button class="snippet-btn" onclick="loadSnippet('listcomp')">📋 List Comprehension</button>
            <button class="snippet-btn" onclick="loadSnippet('classex')">🏫 Classes</button>
            <button class="snippet-btn" onclick="loadSnippet('dict')">📖 Dictionary</button>
        </div>
    </div>

    <div class="editor-wrap">
        <!-- Editor -->
        <div class="editor-panel">
            <div class="panel-header">
                <span>🐍 Python Editor</span>
                <span style="font-size:.75rem;background:rgba(79,70,229,.2);padding:.15rem .5rem;border-radius:10px;">Python 3</span>
            </div>
            <textarea id="code-editor" spellcheck="false" placeholder="# Write your Python code here...
print('Hello, World!')"># Write your Python code here
print("Hello, World!")
print("Welcome to PyLearn Playground!")</textarea>
        </div>

        <!-- Output -->
        <div class="editor-panel">
            <div class="panel-header">
                <span>📤 Output</span>
                <button onclick="document.getElementById('output-box').textContent=''" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:.8rem;">Clear</button>
            </div>
            <div id="output-box">Click "Run Code" to see output here...</div>
        </div>
    </div>

    <div class="run-controls">
        <button id="run-btn" class="btn-primary" onclick="runCode()">▶ Run Code</button>
        <button class="btn-secondary" onclick="saveCode()">💾 Save Snippet</button>
        <button class="btn-secondary" onclick="clearEditor()">🗑 Clear</button>
        <span style="color:var(--text-muted);font-size:.82rem;margin-left:.5rem;">Ctrl+Enter to run</span>
    </div>

    <!-- Recent Submissions -->
    <?php if ($submissions): ?>
    <div class="mt-4">
        <h3 style="margin-bottom:1rem;">📜 Recent Snippets</h3>
        <div style="display:flex;flex-direction:column;gap:.75rem;">
        <?php foreach ($submissions as $s): ?>
        <div class="card" style="cursor:pointer;" onclick="loadSavedCode(<?= htmlspecialchars(json_encode($s['code'])) ?>)">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <code style="font-size:.82rem;color:var(--accent);"><?= htmlspecialchars(substr($s['code'],0,80)) ?>...</code>
                <span style="font-size:.75rem;color:var(--text-muted);"><?= date('M j, H:i', strtotime($s['submitted_at'])) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function clearEditor() {
    document.getElementById('code-editor').value = '';
    document.getElementById('output-box').textContent = '';
}

function loadSavedCode(code) {
    document.getElementById('code-editor').value = code;
    showToast('Code loaded from history!');
}

function saveCode() {
    const code = document.getElementById('code-editor').value.trim();
    if (!code) { showToast('Nothing to save!', 'error'); return; }
    fetch('/pylearn/run_code.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({code, save_only: true})
    }).then(r=>r.json()).then(d=>{
        showToast('Snippet saved! 💾');
    }).catch(()=>showToast('Saved locally!'));
}
</script>
<?php include 'includes/footer.php'; ?>

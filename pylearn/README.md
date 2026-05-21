# 🐍 PyLearn — Interactive Python Learning Platform

A full-featured Python learning platform built with **PHP, MySQL, JavaScript, HTML & CSS**.

---

## 🚀 Features

| Feature | Details |
|---|---|
| 🔐 Auth | Register, Login, Logout with secure password hashing |
| 📚 Lessons | 8 lessons from Beginner to Advanced with syntax-highlighted code |
| 🧠 Quizzes | Interactive multiple-choice quizzes with instant feedback & explanations |
| 💻 Code Playground | Write & run Python code with 6 preloaded examples |
| ⚡ XP System | Earn XP for completing lessons and quizzes |
| 📈 Levels | Level up every 100 XP (Level 1–∞) |
| 🔥 Streaks | Daily login streaks tracked automatically |
| 🏆 Leaderboard | Real-time ranking with podium for top 3 |
| 🎖️ Badges | 6 earnable badges (First Step, Quiz Master, Streak 3, etc.) |
| 📊 Dashboard | Personal stats, progress overview, quick navigation |
| 👤 Profile | Edit email/password, view all badges & completed lessons |
| 🛡️ Admin Panel | Manage lessons, quizzes, and users |
| 🎨 Dark Theme | Modern dark UI with gradient accents |
| 📱 Responsive | Mobile-friendly layout |

---

## ⚙️ Installation

### Requirements
- PHP 7.4+ (8.0+ recommended)
- MySQL 5.7+ or MariaDB 10+
- Apache / Nginx with PHP support
- Python 3 (optional — for live code execution)

### Steps

1. **Copy the `pylearn/` folder** to your web server root (e.g. `htdocs/pylearn` or `www/pylearn`)

2. **Create the database:**
   ```bash
   mysql -u root -p < database.sql
   ```
   Or import `database.sql` via phpMyAdmin.

3. **Edit database credentials** in `includes/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'pylearn');
   ```

4. **Visit** `http://localhost/pylearn`

---

## 🔑 Demo Accounts

| Username | Password | Role |
|---|---|---|
| `admin` | `password` | Admin |
| `alice` | `password` | Student |
| `bob` | `password` | Student |

---

## 📁 Project Structure

```
pylearn/
├── index.php          ← Landing page
├── register.php       ← Registration
├── login.php          ← Login
├── logout.php         ← Logout
├── dashboard.php      ← User dashboard
├── lessons.php        ← Lesson listing with filter/search
├── lesson.php         ← Individual lesson + quiz
├── playground.php     ← Code editor
├── run_code.php       ← Code execution API
├── submit_quiz.php    ← Quiz submission API
├── leaderboard.php    ← Global rankings
├── profile.php        ← User profile & badges
├── database.sql       ← Full DB schema + seed data
├── admin/
│   └── index.php      ← Admin panel
├── includes/
│   ├── config.php     ← DB + helpers
│   ├── header.php     ← Nav bar
│   └── footer.php     ← Footer + scripts
├── css/
│   └── style.css      ← Complete stylesheet
└── js/
    └── main.js        ← Quiz, editor, animations
```

---

## 🐍 Python Execution

The playground detects if Python 3 is installed on the server:
- **If found:** executes code via `python3` subprocess with 5-second timeout
- **If not found:** uses a basic JS simulator for print statements

For full execution, install Python 3 on your server.

---

## 🎓 Built For Presentation

This project demonstrates:
- MVC-style separation (includes, pages, API endpoints)
- Secure PHP with PDO prepared statements
- Interactive UI with vanilla JS (no frameworks needed)
- MySQL relational database design
- Gamification mechanics (XP, levels, badges, streaks)

<?php
declare(strict_types=1);
require __DIR__ . '/api/bootstrap.php';
$fitUser = current_user();
if (!$fitUser) { header('Location: login.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#101b19">
  <title>Your dashboard · FitTrack</title>
  <meta name="csrf-token" content="<?=h(csrf_token())?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <?php $fitUser = current_user(); ?>
  <div class="app-shell">
    <aside class="sidebar" id="sidebar">
      <a class="brand" href="#"><span class="brand-mark">f</span><span>fittrack<span class="brand-dot">.</span></span></a>
      <div class="side-label">WORKSPACE</div>
      <nav class="nav">
        <a class="nav-link active" href="#overview" data-view="overview"><span class="nav-icon">◫</span> Overview</a>
        <a class="nav-link" href="#workouts" data-view="workouts"><span class="nav-icon">⌁</span> Workouts</a>
        <a class="nav-link" href="#progress" data-view="progress"><span class="nav-icon">↗</span> Progress</a>
        <a class="nav-link" href="#goals" data-view="goals"><span class="nav-icon">◎</span> Weekly goals</a>
      </nav>
      <div class="sidebar-bottom">
        <div class="side-label">YOUR SPACE</div>
        <div class="profile-card">
          <div class="avatar"><?=h(strtoupper(substr($fitUser['name'], 0, 1)))?></div>
          <div><strong><?=h($fitUser['name'])?></strong><span>Personal account</span></div>
        </div>
        <div class="sidebar-note"><span class="sparkle">✦</span><p>Small steps. Strong habits. A better you.</p></div>
      </div>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <button class="mobile-menu" id="menuToggle" aria-label="Open menu">☰</button>
        <div class="breadcrumb">Workspace <span>/</span> <strong id="pageName">Overview</strong></div>
        <div class="top-actions">
          <span class="today-label" id="todayLabel"></span>
          <button class="icon-button" id="quickAdd" title="Add workout">＋</button>
          <div class="account-menu"><div class="avatar small-avatar"><?=h(strtoupper(substr($fitUser['name'], 0, 1)))?></div><div class="account-details"><strong><?=h($fitUser['name'])?></strong><small><?=h($fitUser['email'])?></small></div><form method="post" action="logout.php"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><button class="logout-button" type="submit">Log out <span>↗</span></button></form></div>
        </div>
      </header>

      <section class="view active-view" id="overviewView">
        <div class="welcome-row">
          <div><div class="eyebrow">YOUR FITNESS, IN FOCUS</div><h1>Good things take <em>consistency.</em></h1><p class="subheading">A little progress each day adds up to big results.</p></div>
          <button class="primary-button" id="addWorkoutTop">＋ Log a workout</button>
        </div>

        <div class="motivation-banner">
          <div class="motivation-icon">✦</div>
          <div><span class="banner-label">A NOTE FOR YOU</span><h3 id="motivationText">You don't have to be extreme. Just consistent.</h3><p id="motivationSub">Every session is a vote for the person you're becoming.</p></div>
          <div class="banner-decoration">✳</div>
        </div>

        <div class="section-heading"><div><span class="eyebrow">AT A GLANCE</span><h2>Your activity</h2></div><span class="period-pill">This week <span>⌄</span></span></div>
        <div class="stats-grid">
          <article class="stat-card"><div class="stat-top"><span>Workouts</span><span class="stat-icon mint">⌁</span></div><div class="stat-value" id="statWorkouts">0</div><div class="stat-foot"><span class="positive">↗ Keep showing up</span><span>this week</span></div></article>
          <article class="stat-card"><div class="stat-top"><span>Active time</span><span class="stat-icon peach">◷</span></div><div class="stat-value"><span id="statMinutes">0</span><small>min</small></div><div class="stat-foot"><span>Time invested</span><span>this week</span></div></article>
          <article class="stat-card"><div class="stat-top"><span>Calories burned</span><span class="stat-icon lilac">♨</span></div><div class="stat-value" id="statCalories">0</div><div class="stat-foot"><span>Energy in motion</span><span>this week</span></div></article>
          <article class="stat-card"><div class="stat-top"><span>Goal progress</span><span class="stat-icon yellow">◎</span></div><div class="stat-value"><span id="statGoal">0</span><small>%</small></div><div class="stat-foot"><span class="positive">Weekly target</span><span id="goalFoot">0 / 4 sessions</span></div></article>
        </div>

        <div class="content-grid">
          <article class="panel chart-panel"><div class="panel-heading"><div><span class="eyebrow">YOUR RHYTHM</span><h3>Weekly activity</h3></div><span class="chart-legend"><i></i> Minutes</span></div><div class="chart-wrap"><canvas id="weeklyChart" height="220"></canvas></div></article>
          <article class="panel goal-panel"><div class="panel-heading"><div><span class="eyebrow">STAY ON TRACK</span><h3>Weekly target</h3></div><span class="target-tag">This week</span></div><div class="goal-ring-wrap"><div class="goal-ring" id="goalRing"><div><strong id="ringPercent">0%</strong><span>completed</span></div></div></div><div class="goal-summary"><strong id="goalSummary">0 of 4 workouts</strong><span id="goalRemaining">4 more to reach your goal</span></div><button class="text-button" data-go="goals">Manage weekly goal <span>→</span></button></article>
        </div>

        <div class="section-heading recent-heading"><div><span class="eyebrow">THE LATEST</span><h2>Recent workouts</h2></div><button class="text-button" data-go="workouts">View all <span>→</span></button></div>
        <div class="panel recent-panel"><div class="workout-list" id="recentList"><div class="empty-state">Your sessions will appear here once you log your first workout.</div></div></div>
      </section>

      <section class="view" id="workoutsView">
        <div class="welcome-row"><div><div class="eyebrow">SHOW UP FOR YOURSELF</div><h1>Your workouts<span class="brand-dot">.</span></h1><p class="subheading">Every session counts. Keep your momentum going.</p></div><button class="primary-button" id="addWorkoutPage">＋ Add workout</button></div>
        <div class="filter-row"><div class="filter-tabs"><button class="filter-tab active" data-filter="all">All sessions</button><button class="filter-tab" data-filter="week">This week</button></div><span class="muted" id="workoutCount">0 sessions</span></div>
        <div class="panel"><div id="allWorkouts" class="workout-list"><div class="empty-state">No workouts yet. Log your first session to get started.</div></div></div>
      </section>

      <section class="view" id="progressView">
        <div class="welcome-row"><div><div class="eyebrow">THE BIGGER PICTURE</div><h1>Progress, not perfection<span class="brand-dot">.</span></h1><p class="subheading">Look how far you've come. Your effort, made visible.</p></div></div>
        <div class="stats-grid progress-stats">
          <article class="stat-card"><div class="stat-top"><span>Total sessions</span><span class="stat-icon mint">⌁</span></div><div class="stat-value" id="totalSessions">0</div><div class="stat-foot"><span>All time</span></div></article>
          <article class="stat-card"><div class="stat-top"><span>Total minutes</span><span class="stat-icon peach">◷</span></div><div class="stat-value" id="totalMinutes">0</div><div class="stat-foot"><span>Time invested</span></div></article>
          <article class="stat-card"><div class="stat-top"><span>Total calories</span><span class="stat-icon lilac">♨</span></div><div class="stat-value" id="totalCalories">0</div><div class="stat-foot"><span>Estimated burn</span></div></article>
        </div>
        <div class="content-grid progress-grid">
          <article class="panel chart-panel"><div class="panel-heading"><div><span class="eyebrow">CONSISTENCY</span><h3>Workout frequency</h3></div></div><div class="chart-wrap"><canvas id="frequencyChart" height="230"></canvas></div></article>
          <article class="panel chart-panel"><div class="panel-heading"><div><span class="eyebrow">EFFORT OVER TIME</span><h3>Calories by day</h3></div></div><div class="chart-wrap"><canvas id="calorieChart" height="230"></canvas></div></article>
        </div>
        <article class="panel insight-panel"><div class="insight-symbol">✧</div><div><span class="eyebrow">YOUR PERSONAL INSIGHT</span><h3 id="insightTitle">Your journey starts with one session.</h3><p id="insightText">Log a workout and FitTrack will turn your activity into meaningful insights.</p></div></article>
      </section>

      <section class="view" id="goalsView">
        <div class="welcome-row"><div><div class="eyebrow">MAKE IT MEAN SOMETHING</div><h1>Goals that move you<span class="brand-dot">.</span></h1><p class="subheading">Set a weekly intention, then make it happen.</p></div></div>
        <div class="goal-layout">
          <article class="panel goal-editor"><span class="eyebrow">YOUR WEEKLY COMMITMENT</span><h2>Choose your target</h2><p class="muted">A realistic goal is a goal you can build on.</p><form id="goalForm"><label for="weeklyTarget">Workouts per week</label><div class="target-input-row"><button type="button" class="stepper" id="goalMinus">−</button><input type="number" id="weeklyTarget" min="1" max="14" value="4"><button type="button" class="stepper" id="goalPlus">＋</button></div><button class="primary-button full-button" type="submit">Save weekly goal</button></form><div class="goal-tip"><span>✦</span><p>Consistency beats intensity. Pick a number that fits your life, not someone else's.</p></div></article>
          <article class="panel goal-progress-card"><span class="eyebrow">CURRENT WEEK</span><h2>Your progress</h2><div class="large-progress" id="largeProgress">0%</div><div class="progress-track"><span id="progressBar"></span></div><div class="goal-summary left"><strong id="goalDetail">0 of 4 workouts completed</strong><span id="goalDetailSub">You're building your rhythm.</span></div><div class="milestone-list"><div><span class="milestone-dot"></span><span>First workout</span><strong id="firstMilestone">Not yet</strong></div><div><span class="milestone-dot"></span><span>Halfway there</span><strong id="halfMilestone">Not yet</strong></div><div><span class="milestone-dot"></span><span>Weekly goal</span><strong id="finishMilestone">Not yet</strong></div></div></article>
        </div>
      </section>
      <footer class="footer"><span>FITTRACK <b>·</b> YOUR JOURNEY, YOUR PACE</span><span>Made for progress.</span></footer>
    </main>
  </div>

  <div class="modal-backdrop" id="workoutModal" aria-hidden="true">
    <div class="modal">
      <div class="modal-header"><div><span class="eyebrow">MAKE IT COUNT</span><h2>Log a workout</h2></div><button class="close-button" id="closeModal" aria-label="Close">×</button></div>
      <form id="workoutForm">
        <div class="form-grid">
          <div class="field full"><label for="workoutName">Workout name</label><input id="workoutName" type="text" placeholder="e.g. Morning strength session" required maxlength="60"></div>
          <div class="field"><label for="workoutType">Workout type</label><select id="workoutType"><option>Strength</option><option>Cardio</option><option>Running</option><option>Cycling</option><option>Yoga</option><option>Swimming</option><option>HIIT</option><option>Walking</option><option>Other</option></select></div>
          <div class="field"><label for="workoutDate">Date</label><input id="workoutDate" type="date" required></div>
          <div class="field"><label for="workoutDuration">Duration (minutes)</label><input id="workoutDuration" type="number" min="1" max="600" placeholder="45" required></div>
          <div class="field"><label for="workoutCalories">Calories burned (kcal)</label><input id="workoutCalories" type="number" min="0" max="10000" placeholder="320" required></div>
          <div class="field"><label for="workoutIntensity">Intensity</label><select id="workoutIntensity"><option>Moderate</option><option>Light</option><option>High</option><option>Very high</option></select></div>
          <div class="field"><label for="workoutPressure">Perceived effort (1–10)</label><input id="workoutPressure" type="number" min="1" max="10" value="6"></div>
          <div class="field full"><label for="workoutNotes">Notes <span class="optional">OPTIONAL</span></label><textarea id="workoutNotes" rows="3" placeholder="How did it feel? Anything you want to remember?"></textarea></div>
        </div>
        <div class="form-error" id="formError"></div>
        <button class="primary-button full-button" type="submit">Save workout <span>→</span></button>
      </form>
    </div>
  </div>
  <div class="toast" id="toast"></div>
  <script src="assets/app.js"></script>
</body>
</html>
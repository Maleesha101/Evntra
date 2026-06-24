<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth-guard.php';
$pdo = require __DIR__ . '/../config/db.php';
$currentUser = current_user();
$pageTitle = 'Browse Competitions | Evntra';
$pageScripts = [
    'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js',
    '/assets/js/search-filter.js',
    '/assets/js/calendar.js',
];

$initial = competition_search_query([
    'search' => '',
    'category' => '',
    'status' => 'all',
    'venue' => 'all',
    'date_from' => '',
    'date_to' => '',
    'sort' => 'newest',
    'page' => 1,
    'per_page' => 10,
    'user_id' => $currentUser['id'] ?? 0,
]);

include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <div class="section-head">
        <div>
            <h1>Browse competitions</h1>
            <p>Search, filter, bookmark, or switch to the live calendar view.</p>
        </div>
        <div class="tabs">
            <button class="tab-button active" type="button" data-view-toggle="grid">Grid View</button>
            <button class="tab-button" type="button" data-view-toggle="calendar">Calendar View</button>
        </div>
    </div>
</section>

<section class="filter-layout">
    <aside class="filter-sidebar panel" style="display:flex; flex-direction:column; gap:1.25rem;">
        <div style="display:flex; align-items:center; gap:0.5rem; border-bottom:1px solid var(--border); padding-bottom:0.75rem; margin-bottom:0.25rem;">
            <span class="material-symbols-outlined text-primary" style="color:var(--accent-primary);">tune</span>
            <h2 style="font-size:1.25rem; font-weight:700; margin:0; font-family:'Space Grotesk',sans-serif;">Refine Search</h2>
        </div>
        <div class="form-group">
            <label for="search">Search</label>
            <input id="search" type="search" placeholder="Search events, tags..." data-search-input>
        </div>
        <div class="form-group">
            <label>Category</label>
            <div style="display:flex; flex-direction:column; gap:0.5rem; margin-top:0.25rem;">
                <?php foreach (array_keys(category_colors()) as $category): ?>
                    <label class="small-text" style="display:flex; align-items:center; gap:0.5rem; cursor:pointer;">
                        <input type="checkbox" value="<?= e($category) ?>" data-category-filter style="width:1.15rem; height:1.15rem; border-radius:4px; cursor:pointer; margin:0;"> 
                        <?= e($category) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="form-group">
            <label for="sort">Sort by</label>
            <select id="sort" data-sort-select>
                <option value="newest">Newest</option>
                <option value="oldest">Oldest</option>
                <option value="soonest">Soonest ending</option>
                <option value="popular">Most popular</option>
            </select>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select id="status" data-status-filter>
                <option value="all">All</option>
                <option value="open">Open</option>
                <option value="upcoming">Upcoming</option>
                <option value="ongoing">Ongoing</option>
            </select>
        </div>
        <div class="form-group">
            <label for="venue">Venue type</label>
            <select id="venue" data-venue-filter>
                <option value="all">All</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
            </select>
        </div>
        <div class="form-group">
            <label for="date_from">From Date</label>
            <input type="date" id="date_from" data-date-from>
        </div>
        <div class="form-group">
            <label for="date_to">To Date</label>
            <input type="date" id="date_to" data-date-to>
        </div>
        <button class="btn btn-outline" type="button" data-reset-filters style="width:100%; justify-content:center; margin-top:0.5rem;">Reset Filters</button>
    </aside>

    <div style="flex:1;">
        <!-- Active Filter Chips -->
        <div style="display:none; flex-wrap:wrap; gap:0.5rem; margin-bottom:1.5rem; align-items:center;" data-active-chips></div>
        
        <div data-view-panel="grid">
            <div class="grid grid-3" data-competition-grid>
                <?php foreach ($initial['items'] as $competition): ?>
                    <!-- Items rendered dynamically via JS -->
                <?php endforeach; ?>
            </div>
            <div class="form-actions" style="justify-content:center;margin-top:1.25rem;" data-pagination></div>
        </div>

        <div class="hidden" data-view-panel="calendar">
            <div class="panel calendar-card">
                <div id="competition-calendar"></div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>

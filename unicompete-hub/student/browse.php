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
    <aside class="filter-sidebar panel">
        <div class="form-group">
            <label for="search">Search</label>
            <input id="search" type="search" placeholder="Search competitions" data-search-input>
        </div>
        <div class="form-group">
            <label>Category</label>
            <?php foreach (array_keys(category_colors()) as $category): ?>
                <label class="small-text"><input type="checkbox" value="<?= e($category) ?>" data-category-filter> <?= e($category) ?></label>
            <?php endforeach; ?>
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
        <div class="grid grid-2">
            <div class="form-group">
                <label for="date_from">From</label>
                <input type="date" id="date_from" data-date-from>
            </div>
            <div class="form-group">
                <label for="date_to">To</label>
                <input type="date" id="date_to" data-date-to>
            </div>
        </div>
    </aside>

    <div>
        <div data-view-panel="grid">
            <div class="grid grid-3" data-competition-grid>
                <?php foreach ($initial['items'] as $competition): ?>
                    <article class="card competition-card">
                        <a href="<?= e($competition['url']) ?>" class="card-media">
                            <img src="<?= e($competition['banner_image'] ?: '/assets/img/logo.svg') ?>" alt="<?= e($competition['title']) ?>">
                        </a>
                        <div class="card-body">
                            <div class="card-meta" style="justify-content:space-between;align-items:center;">
                                <span class="badge" style="background:<?= e($competition['category_color']) ?>;"><?= e($competition['category']) ?></span>
                                <span class="badge" style="background:rgba(255,255,255,0.08);"><?= e($competition['registration_status']) ?></span>
                            </div>
                            <h3 class="card-title"><a href="<?= e($competition['url']) ?>"><?= e($competition['title']) ?></a></h3>
                            <p><?= e(substr($competition['description'], 0, 120)) ?><?= strlen($competition['description']) > 120 ? '...' : '' ?></p>
                            <div class="card-meta">
                                <span><?= e(date('M d', strtotime($competition['event_start']))) ?></span>
                                <span><?= e($competition['venue']) ?></span>
                            </div>
                            <div class="form-actions" style="margin-top:1rem;">
                                <button class="btn btn-outline" type="button" data-bookmark-toggle data-competition-id="<?= (int) $competition['id'] ?>"><?= $competition['is_bookmarked'] ? 'Bookmarked' : 'Bookmark' ?></button>
                                <a class="btn btn-primary" href="<?= e($competition['url']) ?>">View Details</a>
                            </div>
                        </div>
                    </article>
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

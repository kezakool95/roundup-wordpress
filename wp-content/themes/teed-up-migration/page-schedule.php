<?php
/*
 Template Name: Master Schedule
 */

get_header();

// Fetch current user availability
$user_id = get_current_user_id();
$schedule = get_field('weekly_schedule', 'user_' . $user_id);
?>

<div class="schedule-app container">
    <header class="page-header">
        <h1>Master Schedule</h1>
        <p>Manage your availability and coordinate with friends.</p>
    </header>

    <div class="schedule-grid-wrapper">
        <div class="grid-header">
            <div class="col-time">Time</div>
            <?php foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day): ?>
            <div class="col-day">
                <?php echo $day; ?>
            </div>
            <?php
endforeach; ?>
        </div>

        <div class="grid-body">
            <!-- Render rows for hours 06:00 to 18:00 -->
            <?php for ($h = 6; $h <= 18; $h++):
    $time_label = sprintf('%02d:00', $h);
?>
            <div class="grid-row">
                <div class="cell-time">
                    <?php echo $time_label; ?>
                </div>
                <?php foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day):
        $is_available = false;
        if ($schedule) {
            foreach ($schedule as $slot) {
                if ($slot['day'] === $day && $slot['start_time'] <= $time_label && $slot['end_time'] > $time_label) {
                    $is_available = true;
                    break;
                }
            }
        }
?>
                <div class="cell-slot <?php echo $is_available ? 'available' : ''; ?>">
                    <!-- Interactive slot would go here -->
                </div>
                <?php
    endforeach; ?>
            </div>
            <?php
endfor; ?>
        </div>
    </div>

    <div class="schedule-actions">
        <a href="/wp-admin/profile.php" class="btn-primary">Edit My Availability</a>
    </div>
</div>

<style>
    .schedule-app {
        padding: 2rem 0;
    }

    .schedule-grid-wrapper {
        overflow-x: auto;
        background: white;
        border-radius: 12px;
        border: 1px solid #eee;
        margin: 2rem 0;
    }

    .grid-header {
        display: grid;
        grid-template-columns: 60px repeat(7, 1fr);
        background: #f9f9f9;
        border-bottom: 1px solid #eee;
    }

    .col-day {
        padding: 1rem;
        text-align: center;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .col-time {
        padding: 1rem;
        color: #999;
    }

    .grid-row {
        display: grid;
        grid-template-columns: 60px repeat(7, 1fr);
        border-bottom: 1px solid #eee;
    }

    .cell-time {
        padding: 0.5rem;
        color: #999;
        font-size: 0.8rem;
        text-align: center;
        border-right: 1px solid #eee;
    }

    .cell-slot {
        border-right: 1px solid #eee;
        height: 50px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .cell-slot:hover {
        background: #f0fdf4;
    }

    .cell-slot.available {
        background: var(--wp--preset--color--accent);
        opacity: 0.2;
    }
</style>

<?php get_footer(); ?>
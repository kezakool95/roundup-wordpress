<?php
/*
 Template Name: Master Schedule
 */

get_header();

// Fetch current user availability
$user_id = get_current_user_id();
$schedule = get_field('weekly_schedule', 'user_' . $user_id);

// Normalize times to HH:MM for internal JS consistency
if ($schedule && is_array($schedule)) {
    foreach ($schedule as &$slot) {
        if (!empty($slot['start_time'])) {
            $slot['start_time'] = date('H:i', strtotime($slot['start_time']));
        }
        if (!empty($slot['end_time'])) {
            $slot['end_time'] = date('H:i', strtotime($slot['end_time']));
        }
    }
}
?>

<div class="schedule-app container" x-data="scheduleApp()">
    <header class="page-header flex-between">
        <div>
            <h1>Master Schedule</h1>
            <p>Tap slots to toggle your availability for the week.</p>
        </div>
        <div class="actions">
            <button class="btn-primary" @click="saveSchedule()" :disabled="saving || !hasChanges">
                <span x-show="!saving">Save Changes</span>
                <span x-show="saving">Saving...</span>
            </button>
        </div>
    </header>

    <div class="schedule-grid-wrapper shadow-lg">
        <div class="grid-header">
            <div class="col-time">Time</div>
            <?php foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day): ?>
            <div class="col-day">
                <?php echo $day; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="grid-body">
            <?php for ($h = 6; $h <= 18; $h++):
                for ($m = 0; $m < 60; $m += 30):
                    $time_label = sprintf('%02d:%02d', $h, $m);
            ?>
            <div class="grid-row">
                <div class="cell-time">
                    <?php echo $time_label; ?>
                </div>
                <?php foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day): ?>
                <div class="cell-slot" 
                     :class="{'available': isAvailable('<?php echo $day; ?>', '<?php echo $time_label; ?>')}"
                     @click="toggleSlot('<?php echo $day; ?>', '<?php echo $time_label; ?>')">
                </div>
                <?php endforeach; ?>
            </div>
            <?php 
                endfor;
            endfor; ?>
        </div>
    </div>
</div>

<script>
function scheduleApp() {
    return {
        schedule: <?php echo json_encode($schedule ?: []); ?>,
        saving: false,
        hasChanges: false,
        
        isAvailable(day, time) {
            return this.schedule.some(slot => {
                const start = slot.start_time.substring(0, 5);
                const end = slot.end_time.substring(0, 5);
                return slot.day === day && start <= time && end > time;
            });
        },

        toggleSlot(day, time) {
            this.hasChanges = true;
            const existingIndex = this.schedule.findIndex(slot => {
                const start = slot.start_time.substring(0, 5);
                const end = slot.end_time.substring(0, 5);
                return slot.day === day && start <= time && end > time;
            });

            if (existingIndex > -1) {
                // Remove this 30m slot from their schedule
                // Complex case: if it was part of a larger block, we might need to split it
                const slot = this.schedule[existingIndex];
                const start = slot.start_time.substring(0, 5);
                const end = slot.end_time.substring(0, 5);
                
                this.schedule.splice(existingIndex, 1);
                
                // If it was more than 30 mins, add back the other parts
                if (start < time) {
                    this.schedule.push({ day, start_time: start, end_time: time });
                }
                
                const nextTime = this.getNextTime(time);
                if (end > nextTime) {
                    this.schedule.push({ day, start_time: nextTime, end_time: end });
                }
            } else {
                // Add this 30m slot
                const nextTime = this.getNextTime(time);
                this.schedule.push({
                    day: day,
                    start_time: time,
                    end_time: nextTime
                });
                this.mergeSlots();
            }
        },

        getNextTime(time) {
            let [h, m] = time.split(':').map(Number);
            m += 30;
            if (m >= 60) { h++; m = 0; }
            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
        },

        mergeSlots() {
            // Sort by day and then start time
            const dayOrder = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            this.schedule.sort((a, b) => {
                if (a.day !== b.day) return dayOrder.indexOf(a.day) - dayOrder.indexOf(b.day);
                return a.start_time.localeCompare(b.start_time);
            });

            // Merge contiguous slots
            for (let i = 0; i < this.schedule.length - 1; i++) {
                const curr = this.schedule[i];
                const next = this.schedule[i+1];
                if (curr.day === next.day && curr.end_time >= next.start_time) {
                    curr.end_time = next.end_time > curr.end_time ? next.end_time : curr.end_time;
                    this.schedule.splice(i+1, 1);
                    i--;
                }
            }
        },

        saveSchedule() {
            this.saving = true;
            fetch('/wp-json/teedup/v1/availability/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify({ schedule: this.schedule })
            })
            .then(res => res.json())
            .then(data => {
                this.saving = false;
                if (data.success) {
                    this.hasChanges = false;
                    alert('Schedule updated!');
                } else {
                    alert('Error: ' + data.message);
                }
            });
        }
    }
}
</script>

<style>
    .schedule-app {
        padding: 2rem 0;
    }
    
    .flex-between {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .schedule-grid-wrapper {
        overflow-x: auto;
        background: white;
        border-radius: 24px;
        border: 1px solid rgba(0,0,0,0.05);
        margin: 1rem 0;
    }

    .grid-header {
        display: grid;
        grid-template-columns: 80px repeat(7, 1fr);
        background: #f8fafc;
        border-bottom: 2px solid #f1f5f9;
    }

    .col-day {
        padding: 1.25rem;
        text-align: center;
        font-weight: 800;
        font-size: 0.85rem;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .col-time {
        padding: 1.25rem;
        color: #94a3b8;
        font-weight: 700;
        text-align: center;
    }

    .grid-row {
        display: grid;
        grid-template-columns: 80px repeat(7, 1fr);
        border-bottom: 1px solid #f1f5f9;
    }

    .cell-time {
        padding: 0.75rem;
        color: #94a3b8;
        font-size: 0.75rem;
        font-weight: 700;
        text-align: center;
        background: #f8fafc;
        border-right: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cell-slot {
        border-right: 1px solid #f1f5f9;
        height: 40px;
        cursor: pointer;
        transition: all 0.1s;
    }

    .cell-slot:hover {
        background: #f1f5f9;
    }

    .cell-slot.available {
        background: var(--wp--preset--color--primary);
        position: relative;
    }
    
    .cell-slot.available::after {
        content: '';
        position: absolute;
        inset: 4px;
        background: rgba(255,255,255,0.2);
        border-radius: 4px;
    }

    .cell-slot.available:hover {
        filter: brightness(0.9);
    }
</style>

<?php get_footer(); ?>
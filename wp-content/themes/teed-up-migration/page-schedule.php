<?php
/*
 Template Name: Master Schedule
 */

get_header();

// Fetch current user availability
$user_id = get_current_user_id();
$weekly_schedule = get_field('weekly_schedule', 'user_' . $user_id) ?: [];
$date_overrides = get_field('date_overrides', 'user_' . $user_id) ?: [];

// Normalize times for internal JS consistency
$normalize = function(&$schedule) {
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
};

$normalize($weekly_schedule);
foreach ($date_overrides as &$override) {
    $normalize($override['slots']);
}
?>

<div class="schedule-app container" x-data="scheduleApp()">
    <header class="page-header flex-between">
        <div>
            <h1>Master Schedule</h1>
            <p>Set your recurring availability or overrides for specific weeks.</p>
        </div>
        <div class="actions flex gap-1">
            <button class="btn-primary" @click="saveSchedule()" :disabled="saving || !hasChanges">
                <span x-show="!saving">Save Changes</span>
                <span x-show="saving">Saving...</span>
            </button>
        </div>
    </header>

    <div class="schedule-controls glass-card p-1 mb-2">
        <div class="flex gap-2 items-center">
            <div class="mode-toggle">
                <label class="toggle-label">
                    <input type="radio" value="recurring" x-model="mode">
                    <span>Recurring Default</span>
                </label>
                <label class="toggle-label">
                    <input type="radio" value="week" x-model="mode">
                    <span>Specific Week Override</span>
                </label>
            </div>

            <div x-show="mode === 'week'" class="date-selector flex items-center gap-1 slide-right">
                <label>Week of:</label>
                <input type="date" x-model="selectedWeekStart" @change="loadWeekOverrides()" class="input-small">
                <button x-show="hasAnyWeekOverrides" class="btn-text text-danger small" @click="clearWeekOverrides()">Reset Week to Default</button>
            </div>
        </div>
    </div>

    <div class="schedule-grid-wrapper shadow-lg" :class="{'mode-override': mode === 'week'}">
        <div class="grid-header">
            <div class="col-time">Time</div>
            <template x-for="(day, index) in days" :key="day">
                <div class="col-day" :class="{'active-date': mode === 'week'}">
                    <span x-text="day"></span>
                    <template x-if="mode === 'week'">
                        <div class="date-label" x-text="getDateForDay(index)"></div>
                    </template>
                </div>
            </template>
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
                
                <template x-for="day in days" :key="day">
                    <div class="cell-slot" 
                         :class="{'available': isAvailable(day, '<?php echo $time_label; ?>'), 'is-override': isDayOverridden(day)}"
                         @click="toggleSlot(day, '<?php echo $time_label; ?>')">
                    </div>
                </template>
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
        mode: 'recurring', 
        selectedWeekStart: '',
        days: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        
        weeklySchedule: <?php echo json_encode($weekly_schedule); ?>,
        dateOverrides: <?php echo json_encode($date_overrides); ?>,
        
        currentSchedule: [], 
        saving: false,
        hasChanges: false,

        init() {
            // Set default week start to current Monday
            const now = new Date();
            const day = now.getDay();
            const diff = now.getDate() - day + (day === 0 ? -6 : 1); 
            const monday = new Date(now.setDate(diff));
            this.selectedWeekStart = monday.toISOString().slice(0, 10);

            this.currentSchedule = JSON.parse(JSON.stringify(this.weeklySchedule));
            this.$watch('mode', value => this.updateView());
        },

        getDateForDay(index) {
            if (!this.selectedWeekStart) return '';
            const d = new Date(this.selectedWeekStart);
            d.setDate(d.getDate() + index);
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        },

        getDateStringForDay(dayName) {
            const index = this.days.indexOf(dayName);
            const d = new Date(this.selectedWeekStart);
            d.setDate(d.getDate() + index);
            return d.toISOString().slice(0, 10);
        },

        updateView() {
            this.hasChanges = false;
            if (this.mode === 'recurring') {
                this.currentSchedule = JSON.parse(JSON.stringify(this.weeklySchedule));
            } else {
                this.loadWeekOverrides();
            }
        },

        loadWeekOverrides() {
            // For each day in the week, check if there's an override
            // We store currentSchedule as a flat list of slots, but in 'week' mode 
            // it will contain a mix of overridden slots and defaults for display
            this.currentSchedule = [];
            
            this.days.forEach(day => {
                const dateStr = this.getDateStringForDay(day);
                const override = this.dateOverrides.find(o => o.date === dateStr);
                if (override) {
                    // Add overrides with temporary day tag for display
                    override.slots.forEach(s => {
                        this.currentSchedule.push({...s, day: day, _isOverride: true, _date: dateStr});
                    });
                } else {
                    // Fallback to weekly schedule
                    this.weeklySchedule.filter(s => s.day === day).forEach(s => {
                        this.currentSchedule.push({...s, day: day, _isOverride: false, _date: dateStr});
                    });
                }
            });
            this.hasChanges = false;
        },

        isDayOverridden(day) {
            if (this.mode !== 'week') return false;
            const dateStr = this.getDateStringForDay(day);
            return this.dateOverrides.some(o => o.date === dateStr);
        },

        get hasAnyWeekOverrides() {
             return this.days.some(day => this.isDayOverridden(day));
        },

        clearWeekOverrides() {
            if (confirm('Reset this entire week to default?')) {
                this.days.forEach(day => {
                    const dateStr = this.getDateStringForDay(day);
                    const idx = this.dateOverrides.findIndex(o => o.date === dateStr);
                    if (idx > -1) this.dateOverrides.splice(idx, 1);
                });
                this.loadWeekOverrides();
                this.hasChanges = true;
            }
        },

        isAvailable(day, time) {
            return this.currentSchedule.some(slot => {
                const start = slot.start_time.substring(0, 5);
                const end = slot.end_time.substring(0, 5);
                return slot.day === day && start <= time && end > time;
            });
        },

        toggleSlot(day, time) {
            this.hasChanges = true;
            const dateStr = this.mode === 'week' ? this.getDateStringForDay(day) : null;

            const existingIndex = this.currentSchedule.findIndex(slot => {
                const start = slot.start_time.substring(0, 5);
                const end = slot.end_time.substring(0, 5);
                return slot.day === day && start <= time && end > time;
            });

            if (existingIndex > -1) {
                const slot = this.currentSchedule[existingIndex];
                const start = slot.start_time.substring(0, 5);
                const end = slot.end_time.substring(0, 5);
                
                this.currentSchedule.splice(existingIndex, 1);
                
                if (start < time) {
                    this.currentSchedule.push({ day, start_time: start, end_time: time, _isOverride: !!dateStr, _date: dateStr });
                }
                
                const nextTime = this.getNextTime(time);
                if (end > nextTime) {
                    this.currentSchedule.push({ day, start_time: nextTime, end_time: end, _isOverride: !!dateStr, _date: dateStr });
                }
            } else {
                const nextTime = this.getNextTime(time);
                this.currentSchedule.push({
                    day: day,
                    start_time: time,
                    end_time: nextTime,
                    _isOverride: !!dateStr,
                    _date: dateStr
                });
                this.mergeSlots();
            }

            // If we're in week mode, the day we just touched is now definitely an override
            if (this.mode === 'week') {
                this.currentSchedule.filter(s => s.day === day).forEach(s => s._isOverride = true);
            }
        },

        getNextTime(time) {
            let [h, m] = time.split(':').map(Number);
            m += 30;
            if (m >= 60) { h++; m = 0; }
            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0');
        },

        mergeSlots() {
            this.currentSchedule.sort((a, b) => {
                if (a.day !== b.day) return this.days.indexOf(a.day) - this.days.indexOf(b.day);
                return a.start_time.localeCompare(b.start_time);
            });

            for (let i = 0; i < this.currentSchedule.length - 1; i++) {
                const curr = this.currentSchedule[i];
                const next = this.currentSchedule[i+1];
                if (curr.day === next.day && curr.end_time >= next.start_time) {
                    curr.end_time = next.end_time > curr.end_time ? next.end_time : curr.end_time;
                    this.currentSchedule.splice(i+1, 1);
                    i--;
                }
            }
        },

        async saveSchedule() {
            this.saving = true;
            
            if (this.mode === 'recurring') {
                await this.performSave(this.currentSchedule, null);
                this.weeklySchedule = JSON.parse(JSON.stringify(this.currentSchedule));
            } else {
                // In week mode, we need to save each day that is marked as an override
                const daysToSave = this.days.filter(day => {
                    return this.currentSchedule.some(s => s.day === day && s._isOverride);
                });

                for (const day of daysToSave) {
                    const dateStr = this.getDateStringForDay(day);
                    const daySlots = this.currentSchedule.filter(s => s.day === day);
                    await this.performSave(daySlots, dateStr);
                    
                    // Update local dateOverrides
                    const idx = this.dateOverrides.findIndex(o => o.date === dateStr);
                    if (idx > -1) {
                        this.dateOverrides[idx].slots = JSON.parse(JSON.stringify(daySlots));
                    } else {
                        this.dateOverrides.push({ date: dateStr, slots: JSON.parse(JSON.stringify(daySlots)) });
                    }
                }
            }
            
            this.saving = false;
            this.hasChanges = false;
            alert('Schedule updated!');
        },

        performSave(schedule, date) {
            return fetch('/wp-json/teedup/v1/availability/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                body: JSON.stringify({ schedule, date })
            }).then(res => res.json());
        }
    }
}
</script>

<style>
    .schedule-app { padding: 2rem 0; }
    .flex-between { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
    .flex { display: flex; }
    .items-center { align-items: center; }
    .gap-1 { gap: 0.5rem; }
    .gap-2 { gap: 1rem; }
    .mb-2 { margin-bottom: 1.5rem; }
    .p-1 { padding: 1rem; }
    
    .mode-toggle {
        display: flex;
        background: #f1f5f9;
        padding: 0.25rem;
        border-radius: 12px;
    }
    
    .toggle-label {
        padding: 0.5rem 1rem;
        cursor: pointer;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    
    .toggle-label input { display: none; }
    .toggle-label:has(input:checked) {
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        color: var(--wp--preset--color--primary);
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
        padding: 1rem 0.5rem;
        text-align: center;
        font-weight: 800;
        font-size: 0.85rem;
        color: #475569;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        line-height: 1.2;
    }
    
    .date-label {
        font-size: 0.7rem;
        font-weight: 500;
        color: #94a3b8;
        margin-top: 0.25rem;
    }

    .col-time { padding: 1.25rem; color: #94a3b8; font-weight: 700; text-align: center; }

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

    .cell-slot { border-right: 1px solid #f1f5f9; height: 40px; cursor: pointer; transition: all 0.1s; position: relative; }
    .cell-slot:hover { background: #f1f5f9; }
    .cell-slot.available { background: var(--wp--preset--color--primary); }
    .cell-slot.available::after { content: ''; position: absolute; inset: 4px; background: rgba(255,255,255,0.2); border-radius: 4px; }
    
    .is-override {
        background-color: rgba(var(--wp--preset--color--primary-rgb), 0.05);
    }

    .btn-text { background: none; border: none; cursor: pointer; font-size: 0.85rem; font-weight: 600; }
    .text-danger { color: #ef4444; }
    .input-small { padding: 0.4rem; border: 1px solid #ddd; border-radius: 8px; font-size: 0.85rem; }
    .mode-override .grid-header { background: #f0fdf4; }
</style>

<?php get_footer(); ?>

<?php get_footer(); ?>
<?php
/*
 Template Name: Round Creator
 */

get_header();
?>

<div class="round-creator-app container" x-data="roundCreator()">
    <!-- Header -->
    <header class="creator-header center mt-2">
        <h1 x-text="isRetrospective ? 'Log Past Round' : 'Create New Round'"></h1>
        <div class="step-indicator mb-2">
            <span x-text="'Step ' + step + ' of 4'"></span>
            <div class="progress-bar">
                <div class="progress-fill" :style="'width: ' + (step/4)*100 + '%'"></div>
            </div>
        </div>
    </header>

    <!-- Step 1: Mode Selection -->
    <div x-show="step === 1" class="step-content slide-up">
        <h2 class="center mb-2">What would you like to do?</h2>
        
        <div class="mode-cards-grid">
            <!-- Log Past Round -->
            <button class="mode-action-card glass-card hover-lift" @click="isRetrospective = true; isPractice = false; holes = 18; initScores(); step = 2">
                <div class="mode-card-icon">🏆</div>
                <div class="card-detail">
                    <h3>Log Past Round</h3>
                    <p>Enter scores for a round you've already completed.</p>
                </div>
            </button>

            <!-- Track Practice -->
            <button class="mode-action-card glass-card hover-lift" @click="isRetrospective = true; isPractice = true; holes = 18; initScores(); step = 2">
                <div class="mode-card-icon">🎯</div>
                <div class="card-detail">
                    <h3>Track Practice</h3>
                    <p>Log a session that won't affect your handicap index.</p>
                </div>
            </button>

            <!-- Book with Friends -->
            <button class="mode-action-card glass-card hover-lift" @click="isRetrospective = false; isPractice = false; holes = 18; initScores(); step = 2">
                <div class="mode-card-icon">👥</div>
                <div class="card-detail">
                    <h3>Book with Friends</h3>
                    <p>Coordinate a future tee time based on group availability.</p>
                </div>
            </button>
        </div>
    </div>

    <!-- Step 2: Date & Course & Friends -->
    <div x-show="step === 2" class="step-content glass-card slide-up">
        <h2 class="mb-2" x-text="isPractice ? 'Practice Details' : (isRetrospective ? 'Round Details' : 'Coordinate Tee Time')"></h2>

        <div class="form-group mb-2">
            <label>Length of Play</label>
            <div class="hole-selection grid-2">
                <button class="btn-secondary" :class="{'active': holes === 9}" @click="holes = 9; if(!isRetrospective) refreshSlots()">9 Holes</button>
                <button class="btn-secondary" :class="{'active': holes === 18}" @click="holes = 18; if(!isRetrospective) refreshSlots()">18 Holes</button>
            </div>
        </div>

        <div class="form-group mb-2">
            <label>Select Course</label>
            <select x-model="selectedCourseId" class="input-large" @change="loadCourseDetails()">
                <option value="">Choose a course...</option>
                <template x-for="course in courses" :key="course.id">
                    <option :value="course.id" x-text="course.title.rendered"></option>
                </template>
            </select>
        </div>

        <template x-if="!isRetrospective">
            <div class="availability-coordination">
                <div class="form-group mb-2">
                    <label>Play with Friends <span class="text-muted">(Optional)</span></label>
                    <div class="friends-mini-selection">
                        <template x-for="friend in friendList" :key="friend.id">
                            <label class="friend-chip" :class="{'active': selectedFriendIds.includes(friend.id)}">
                                <input type="checkbox" :value="friend.id" x-model="selectedFriendIds" @change="refreshSlots()">
                                <img :src="friend.avatar" class="chip-avatar">
                                <span x-text="friend.name"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <div class="form-group mb-2">
                    <label>Select Date & Time</label>
                    <input type="datetime-local" x-model="selectedDate" class="input-large" @change="selectedSlot = null">
                    
                    <div class="mt-2">
                         <label class="text-muted small">Suggested Times (based on availability)</label>
                         <div class="slot-picker-grid" x-show="!loadingSlots && availableSlots.length > 0">
                            <template x-for="slot in availableSlots" :key="slot.day + slot.time">
                                <button class="slot-btn" :class="{'active': selectedSlot === slot}" @click="selectSlot(slot)">
                                    <span class="slot-day" x-text="slot.day"></span>
                                    <span class="slot-time" x-text="slot.time"></span>
                                </button>
                            </template>
                        </div>
                        <p x-show="!loadingSlots && availableSlots.length === 0" class="text-muted small">No common free slots found. Please choose a custom time above.</p>
                         <div x-show="loadingSlots" class="center p-1"><div class="spinner"></div></div>
                    </div>
                </div>

                <div class="selected-time-summary glass-card p-1 mb-2" x-show="selectedSlot">
                    <span>Selected from suggestions: </span>
                    <strong x-text="selectedSlot ? selectedSlot.label : ''"></strong>
                </div>
            </div>
        </template>

        <template x-if="isRetrospective">
            <div class="form-group mb-2">
                <label>Date & Time Played</label>
                <input type="datetime-local" x-model="selectedDate" class="input-large">
            </div>
        </template>

        <div class="grid-2 mt-2">
            <button class="btn-secondary" @click="step = 1">Back</button>
            
            <template x-if="isRetrospective">
                <button class="btn-primary" :disabled="!selectedCourseId || !selectedDate" @click="step = 3">
                    Next: Enter Scorecard
                </button>
            </template>
            
            <template x-if="!isRetrospective">
                <button class="btn-primary" 
                        :disabled="!selectedCourseId || (!selectedSlot && !showManualTime)" 
                        @click="submitBooking()"
                        x-text="submitting ? 'Sending...' : 'Send Invitations'">
                </button>
            </template>
        </div>
    </div>

    <!-- Step 3: Scorecard -->
    <div x-show="step === 3" class="step-content glass-card slide-up">
        <h2>Enter Scores</h2>
        <p class="text-muted text-center mb-2" x-text="selectedCourseName"></p>

        <!-- Front 9 -->
        <div class="nine-section">
            <h3 class="nine-title">Front 9</h3>
            <div class="scorecard-grid">
                <template x-for="i in 9" :key="i">
                    <div class="hole-input">
                        <label x-text="'Hole ' + i"></label>
                        <span class="par-label" x-text="'Par ' + (coursePars[i-1] || 4)"></span>
                        <input type="number" x-model.number="scores[i-1]" min="1" max="15"
                            :class="getScoreClass(scores[i-1], coursePars[i-1])">
                    </div>
                </template>
            </div>
            <div class="nine-total">
                Front 9: <strong x-text="front9Total"></strong>
            </div>
        </div>

        <!-- Back 9 (if 18 holes) -->
        <template x-if="holes === 18">
            <div class="nine-section">
                <h3 class="nine-title">Back 9</h3>
                <div class="scorecard-grid">
                    <template x-for="i in 9" :key="i + 9">
                        <div class="hole-input">
                            <label x-text="'Hole ' + (i + 9)"></label>
                            <span class="par-label" x-text="'Par ' + (coursePars[i+8] || 4)"></span>
                            <input type="number" x-model.number="scores[i+8]" min="1" max="15"
                                :class="getScoreClass(scores[i+8], coursePars[i+8])">
                        </div>
                    </template>
                </div>
                <div class="nine-total">
                    Back 9: <strong x-text="back9Total"></strong>
                </div>
            </div>
        </template>

        <div class="mt-2 text-center">
            <div class="total-score-box" :class="getTotalClass()">
                <span>Total Score:</span>
                <strong x-text="totalScore || '-'"></strong>
                <span class="vs-par" x-text="vsParText"></span>
            </div>
        </div>

        <div class="grid-2 mt-2">
            <button class="btn-secondary" @click="step = 2">Back</button>
            <button class="btn-primary" @click="submitRound()" :disabled="submitting || !isComplete">
                <span x-show="!submitting" x-text="isPractice ? 'Log Practice' : 'Submit Round'"></span>
                <span x-show="submitting">Saving...</span>
            </button>
        </div>
    </div>

    <!-- Step 4: Success -->
    <div x-show="step === 4" class="step-content glass-card text-center slide-up">
        <div class="success-icon" x-text="isPractice ? '🎯' : '🏆'"></div>
        <h2 x-text="isPractice ? 'Practice Logged!' : 'Round Recorded!'"></h2>
        <p x-show="!isPractice">Your handicap has been updated to: <strong x-text="newHandicap"></strong></p>
        <p x-show="isPractice">Practice sessions help you track your progress without affecting your handicap.</p>

        <div class="round-summary glass-card mt-2">
            <div class="summary-row">
                <span>Course</span>
                <strong x-text="selectedCourseName"></strong>
            </div>
            <div class="summary-row">
                <span>Total Score</span>
                <strong x-text="totalScore"></strong>
            </div>
            <div class="summary-row">
                <span>vs Par</span>
                <strong x-text="vsParText"></strong>
            </div>
        </div>

        <div class="mt-2 grid-2">
            <a href="<?php echo site_url('/dashboard'); ?>" class="btn-secondary">Dashboard</a>
            <a href="<?php echo site_url('/stats'); ?>" class="btn-primary">View Stats</a>
        </div>
    </div>
</div>

<script>
    function roundCreator() {
        return {
            step: <?php echo isset($_GET['round_id']) ? '3' : '1'; ?>,
            holes: 18,
            isRetrospective: <?php echo (isset($_GET['round_id']) || (isset($_GET['intent']) && $_GET['intent'] === 'log')) ? 'true' : 'false'; ?>,
            existingRoundId: <?php echo isset($_GET['round_id']) ? intval($_GET['round_id']) : '0'; ?>,
            isPractice: false,
            selectedDate: new Date().toISOString().slice(0, 16),
            selectedCourseId: '',
            selectedCourseName: '',
            courses: [],
            coursePars: [],
            courseTotalPar: 72,
            scores: Array(18).fill(null),
            submitting: false,
            newHandicap: 'NH',

            friendList: [],
            selectedFriendIds: [],
            availableSlots: [],
            loadingSlots: false,
            selectedSlot: null,
            showManualTime: false,

            get totalScore() {
                return this.scores.slice(0, this.holes).reduce((a, b) => a + (b || 0), 0);
            },

            get front9Total() {
                return this.scores.slice(0, 9).reduce((a, b) => a + (b || 0), 0);
            },

            get back9Total() {
                return this.scores.slice(9, 18).reduce((a, b) => a + (b || 0), 0);
            },

            get vsParText() {
                const par = this.holes === 9 ? this.courseTotalPar / 2 : this.courseTotalPar;
                const diff = this.totalScore - par;
                if (diff === 0) return 'E';
                return diff > 0 ? '+' + diff : diff.toString();
            },

            get isComplete() {
                return this.scores.slice(0, this.holes).every(s => s && s > 0);
            },

            initScores() {
                this.scores = Array(this.holes).fill(null);
                if (!this.isRetrospective) this.refreshSlots();
            },

            init() {
                fetch('/wp-json/wp/v2/course?per_page=100')
                    .then(res => res.json())
                    .then(data => this.courses = data);

                fetch('/wp-json/teedup/v1/friends/list', {
                    headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' }
                })
                    .then(res => res.json())
                    .then(data => {
                        this.friendList = data.friends || [];
                        
                        // Auto-fill from existing round if provided
                        if (this.existingRoundId) {
                            fetch(`/wp-json/wp/v2/round/${this.existingRoundId}`, {
                                headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.id) {
                                    this.selectedCourseId = data.acf.course;
                                    this.holes = parseInt(data.acf.holes_played) || 18;
                                    this.selectedDate = data.acf.date;
                                    this.loadCourseDetails();
                                }
                            });
                        }

                        // Handle pre-selection from Quick Book
                        const params = new URLSearchParams(window.location.search);
                        const partnerId = params.get('partner');
                        const intent = params.get('intent');
                        
                        if (partnerId) {
                            this.selectedFriendIds = [parseInt(partnerId)];
                        }
                        if (intent === 'book') {
                            this.isRetrospective = false;
                        } else if (intent === 'log') {
                            this.isRetrospective = true;
                        }
                    });
            },

            refreshSlots() {
                if (this.isRetrospective) return;
                this.loadingSlots = true;
                this.selectedSlot = null;

                const duration = this.holes === 18 ? 300 : 150; // 5h vs 2.5h
                
                fetch('/wp-json/teedup/v1/availability/check', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    body: JSON.stringify({
                        user_ids: this.selectedFriendIds,
                        duration_mins: duration
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        this.availableSlots = data.options || [];
                        this.loadingSlots = false;
                    });
            },

            selectSlot(slot) {
                this.selectedSlot = slot;
                // Convert Next [Day] to actual date
                const today = new Date();
                const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                const targetDay = days.indexOf(slot.day);
                let currentDay = today.getDay();
                let daysUntil = (targetDay + 7 - currentDay) % 7;
                if (daysUntil === 0) daysUntil = 7; // Next occurrence
                
                const targetDate = new Date(today);
                targetDate.setDate(today.getDate() + daysUntil);
                
                // Format: YYYY-MM-DDTHH:MM
                this.selectedDate = targetDate.toISOString().slice(0, 10) + 'T' + slot.time;
            },

            getNearestDateForSlot(slot) {
                const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                const targetDay = days.indexOf(slot.day);
                const now = new Date();
                const result = new Date();
                result.setHours(parseInt(slot.time.split(':')[0]), parseInt(slot.time.split(':')[1]), 0, 0);
                
                let dayDiff = targetDay - now.getDay();
                if (dayDiff < 0) dayDiff += 7;
                result.setDate(now.getDate() + dayDiff);
                
                return result.toISOString().slice(0, 16);
            },

            loadCourseDetails() {
                const course = this.courses.find(c => c.id == this.selectedCourseId);
                if (course) {
                    this.selectedCourseName = course.title.rendered;
                    this.coursePars = Array(18).fill(4);
                    this.courseTotalPar = 72;
                }
            },

            getScoreClass(score, par) {
                if (!score) return '';
                const diff = score - (par || 4);
                if (diff <= -2) return 'score-eagle';
                if (diff === -1) return 'score-birdie';
                if (diff === 0) return 'score-par';
                if (diff === 1) return 'score-bogey';
                return 'score-double';
            },

            getTotalClass() {
                const par = this.holes === 9 ? this.courseTotalPar / 2 : this.courseTotalPar;
                const diff = this.totalScore - par;
                if (diff < 0) return 'under-par';
                if (diff === 0) return 'even-par';
                return 'over-par';
            },

            submitRound() {
                this.submitting = true;

                const payload = {
                    round_id: this.existingRoundId,
                    course_id: parseInt(this.selectedCourseId),
                    date: this.selectedDate,
                    holes_played: this.holes,
                    scores: this.scores.slice(0, this.holes),
                    is_practice: this.isPractice,
                    partners: this.selectedFriendIds
                };

                fetch('/wp-json/teedup/v1/submit-round', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    body: JSON.stringify(payload)
                })
                    .then(res => res.json())
                    .then(data => {
                        this.submitting = false;
                        if (data.success) {
                            this.newHandicap = data.handicap;
                            this.step = 4;
                        } else {
                            alert(data.message || 'Error saving round.');
                        }
                    });
            },

            submitBooking() {
                if (!this.selectedDate && !this.selectedSlot) {
                    alert('Please select a time.');
                    return;
                }
                this.submitting = true;

                const payload = {
                    course_id: parseInt(this.selectedCourseId),
                    date: this.selectedDate,
                    holes_played: this.holes,
                    invited_friends: this.selectedFriendIds
                };

                fetch('/wp-json/teedup/v1/booking/create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    body: JSON.stringify(payload)
                })
                    .then(res => res.json())
                    .then(data => {
                        this.submitting = false;
                        if (data.success) {
                            alert('Booking request sent!');
                            window.location.href = '<?php echo site_url('/dashboard'); ?>';
                        } else {
                            alert(data.message || 'Error creating booking.');
                        }
                    });
            }
        }
    }
</script>

<style>
    .round-creator-app {
        max-width: 800px;
        padding-bottom: 4rem;
    }

    .progress-bar {
        height: 6px;
        background: #eee;
        border-radius: 3px;
        margin-top: 0.5rem;
    }

    .progress-fill {
        height: 100%;
        background: var(--primary);
        border-radius: 3px;
        transition: width 0.3s;
    }

    /* Mode Action Cards (Scoped) */
    .mode-cards-grid {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .mode-action-card {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.5rem;
        text-align: left;
        width: 100%;
        background: white;
        border: 1px solid rgba(0,0,0,0.05);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .mode-action-card:hover {
        border-color: var(--primary);
        background: #f0fdf4;
    }

    .mode-card-icon {
        font-size: 2.5rem;
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        border-radius: 16px;
        transition: transform 0.3s;
    }

    .mode-action-card:hover .mode-card-icon {
        transform: scale(1.1);
        background: white;
    }

    .card-detail h3 {
        margin: 0 0 0.25rem;
        font-weight: 800;
        color: #1e293b;
    }

    .card-detail p {
        margin: 0;
        color: #64748b;
        font-size: 0.9rem;
    }

    .hole-selection {
        gap: 1rem;
    }

    .btn-secondary.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .nine-section {
        margin: 1.5rem 0;
        padding: 1rem;
        background: rgba(0, 0, 0, 0.02);
        border-radius: 16px;
    }

    .nine-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    .nine-total {
        text-align: right;
        font-size: 1.1rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(0, 0, 0, 0.1);
    }

    .scorecard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
        gap: 0.75rem;
    }

    .hole-input {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .hole-input label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 0.2rem;
    }

    .par-label {
        font-size: 0.65rem;
        color: #999;
        margin-bottom: 0.25rem;
    }

    .hole-input input {
        width: 100%;
        text-align: center;
        padding: 0.5rem;
        border-radius: 8px;
        border: 2px solid #ddd;
        font-weight: 600;
        transition: all 0.2s;
    }

    .hole-input input:focus {
        border-color: var(--primary);
        outline: none;
    }

    /* Score colors */
    .score-eagle {
        background: #ffd700;
        border-color: #ffd700 !important;
    }

    .score-birdie {
        background: #e8f5e9;
        border-color: #4caf50 !important;
    }

    .score-par {
        background: white;
        border-color: #4caf50 !important;
    }

    .score-bogey {
        background: #fff3e0;
        border-color: #ff9800 !important;
    }

    .score-double {
        background: #ffebee;
        border-color: #f44336 !important;
    }

    .total-score-box {
        font-size: 1.5rem;
        display: inline-block;
        padding: 1rem 2rem;
        border-radius: 12px;
        transition: all 0.3s;
    }

    .total-score-box.under-par {
        background: linear-gradient(135deg, #4caf50, #2e7d32);
        color: white;
    }

    .total-score-box.even-par {
        background: var(--primary);
        color: white;
    }

    .total-score-box.over-par {
        background: linear-gradient(135deg, #ff9800, #e65100);
        color: white;
    }

    .total-score-box strong {
        font-size: 2.5rem;
        margin: 0 1rem;
    }

    .vs-par {
        font-size: 1rem;
        opacity: 0.9;
    }

    .success-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }

    .round-summary {
        max-width: 300px;
        margin: 0 auto;
        padding: 1.5rem;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }

    .summary-row:last-child {
        border-bottom: none;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
    }

    /* Coordinator Styles */
    .friends-mini-selection {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 0.5rem;
    }

    .friend-chip {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.4rem 0.8rem;
        background: white;
        border: 1px solid #eee;
        border-radius: 50px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .friend-chip.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .friend-chip input {
        display: none;
    }

    .chip-avatar {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        object-fit: cover;
    }

    .slot-picker-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 0.75rem;
        max-height: 300px;
        overflow-y: auto;
        padding: 0.5rem;
        background: rgba(0,0,0,0.02);
        border-radius: 12px;
    }

    .slot-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 0.75rem;
        background: white;
        border: 1px solid #eee;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .slot-btn:hover {
        border-color: var(--primary);
        background: #f0fdf4;
    }

    .slot-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .slot-day {
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 700;
        opacity: 0.8;
    }

    .slot-time {
        font-size: 1.1rem;
        font-weight: 800;
    }

    .spinner {
        width: 30px;
        height: 30px;
        border: 3px solid #eee;
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto;
    }

    @keyframes spin { to { transform: rotate(360deg); } }

    .selected-time-summary {
        background: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #a5d6a7;
        text-align: center;
        border-radius: 12px;
    }
</style>

<?php get_footer(); ?>
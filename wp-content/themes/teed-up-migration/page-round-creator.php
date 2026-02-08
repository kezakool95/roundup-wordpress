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

    <!-- Step 1: Mode & Holes -->
    <div x-show="step === 1" class="step-content glass-card slide-up">
        <h2>Round Setup</h2>
        <div class="form-group mb-2">
            <label class="checkbox-label">
                <input type="checkbox" x-model="isRetrospective">
                <span>I've already played this round (Retrospective)</span>
            </label>
        </div>
        <div class="form-group mb-2">
            <label class="checkbox-label">
                <input type="checkbox" x-model="isPractice">
                <span>🎯 This is a practice round (won't affect handicap)</span>
            </label>
        </div>

        <div class="hole-selection grid-2">
            <button class="btn-secondary large" :class="{'active': holes === 9}"
                @click="holes = 9; initScores(); step = 2">
                9 Holes
            </button>
            <button class="btn-secondary large" :class="{'active': holes === 18}"
                @click="holes = 18; initScores(); step = 2">
                18 Holes
            </button>
        </div>
    </div>

    <!-- Step 2: Date & Course -->
    <div x-show="step === 2" class="step-content glass-card slide-up">
        <h2 x-text="isRetrospective ? 'When & Where?' : 'Set Availability'"></h2>

        <div class="form-group mb-2">
            <label>Date & Time</label>
            <input type="datetime-local" x-model="selectedDate" class="input-large">
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

        <div class="grid-2 mt-2">
            <button class="btn-secondary" @click="step = 1">Back</button>
            <button class="btn-primary" :disabled="!selectedCourseId || !selectedDate" @click="step = 3">
                Next: Enter Scorecard
            </button>
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
            step: 1,
            holes: 18,
            isRetrospective: true,
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
            },

            init() {
                fetch('/wp-json/wp/v2/course?per_page=100')
                    .then(res => res.json())
                    .then(data => this.courses = data);
            },

            loadCourseDetails() {
                const course = this.courses.find(c => c.id == this.selectedCourseId);
                if (course) {
                    this.selectedCourseName = course.title.rendered;
                    // Load pars from ACF (if available via REST)
                    // For now, default to par 4s
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
                    course_id: parseInt(this.selectedCourseId),
                    date: this.selectedDate,
                    holes_played: this.holes,
                    scores: this.scores.slice(0, this.holes),
                    is_practice: this.isPractice
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
                            alert(data.message || 'Error saving round. Please try again.');
                        }
                    })
                    .catch(err => {
                        this.submitting = false;
                        alert('Network error. Please try again.');
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

    .checkbox-label input {
        width: 20px;
        height: 20px;
    }
</style>

<?php get_footer(); ?>
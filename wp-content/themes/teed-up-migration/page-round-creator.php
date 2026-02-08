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

        <div class="hole-selection grid-2">
            <button class="btn-secondary large" :class="{'active': holes === 9}" @click="holes = 9; step = 2">
                9 Holes
            </button>
            <button class="btn-secondary large" :class="{'active': holes === 18}" @click="holes = 18; step = 2">
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
            <select x-model="selectedCourseId" class="input-large">
                <option value="">Choose a course...</option>
                <template x-for="course in courses" :key="course.id">
                    <option :value="course.id" x-text="course.title.rendered"></option>
                </template>
            </select>
        </div>

        <button class="btn-primary full-width" :disabled="!selectedCourseId || !selectedDate" @click="step = 3">
            Next: Enter Scorecard
        </button>
    </div>

    <!-- Step 3: Scorecard -->
    <div x-show="step === 3" class="step-content glass-card slide-up">
        <h2>Enter Scores</h2>
        <div class="scorecard-grid">
            <template x-for="i in Array.from({length: holes}, (_, i) => i + 1)" :key="i">
                <div class="hole-input">
                    <label x-text="'Hole ' + i"></label>
                    <input type="number" x-model.number="scores[i-1]" min="1" max="15">
                </div>
            </template>
        </div>

        <div class="mt-2 text-center">
            <div class="total-score-box">
                <span>Total Score:</span>
                <strong x-text="totalScore || '-'"></strong>
            </div>
        </div>

        <div class="grid-2 mt-2">
            <button class="btn-secondary" @click="step = 2">Back</button>
            <button class="btn-primary" @click="submitRound()" :disabled="submitting">
                <span x-show="!submitting">Submit Round</span>
                <span x-show="submitting">Saving...</span>
            </button>
        </div>
    </div>

    <!-- Step 4: Success -->
    <div x-show="step === 4" class="step-content glass-card text-center slide-up">
        <div class="success-icon">🏆</div>
        <h2>Round Recorded!</h2>
        <p>Your stats and handicap have been updated.</p>
        <a href="<?php echo site_url('/dashboard'); ?>" class="btn-primary mt-2">Back to Dashboard</a>
    </div>
</div>

<script>
    function roundCreator() {
        return {
            step: 1,
            holes: 18,
            isRetrospective: true,
            selectedDate: new Date().toISOString().slice(0, 16),
            selectedCourseId: '',
            courses: [],
            scores: Array(18).fill(null),
            submitting: false,

            get totalScore() {
                return this.scores.reduce((a, b) => a + (b || 0), 0);
            },

            init() {
                fetch('/wp-json/wp/v2/course?per_page=100')
                    .then(res => res.json())
                    .then(data => this.courses = data);
            },

            submitRound() {
                this.submitting = true;

                const payload = {
                    title: 'Round at ' + this.courses.find(c => c.id == this.selectedCourseId).title.rendered,
                    status: 'publish',
                    acf: {
                        course: this.selectedCourseId,
                        date: this.selectedDate,
                        score: this.totalScore,
                        holes_played: this.holes
                    }
                };

                // Use the WP REST API Nonce if available, though for simplicity in Alpha we assume logged in context works with standard cookie auth if enabled in WP
                fetch('/wp-json/wp/v2/round', {
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
                        if (data.id) {
                            this.step = 4;
                        } else {
                            alert('Error saving round. Please try again.');
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

    .hole-selection {
        gap: 1rem;
    }

    .btn-secondary.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .scorecard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
        gap: 0.75rem;
        margin-top: 1.5rem;
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
        margin-bottom: 0.25rem;
    }

    .hole-input input {
        width: 100%;
        text-align: center;
        padding: 0.5rem;
        border-radius: 8px;
        border: 1px solid #ddd;
    }

    .total-score-box {
        font-size: 1.5rem;
        background: var(--primary);
        color: white;
        display: inline-block;
        padding: 1rem 2rem;
        border-radius: 12px;
    }

    .total-score-box strong {
        font-size: 2.5rem;
        margin-left: 1rem;
    }

    .success-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
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
<?php
/*
 Template Name: Friend Finder
 */

get_header();

$search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
?>

<div class="friends-container container" x-data="friendsApp()">
    <header class="page-header center mt-2 mb-2">
        <h1>Golf Partners</h1>
        <p class="text-muted">Connect with other golfers and track each other's progress</p>
    </header>

    <!-- Tab Navigation -->
    <div class="tab-nav mb-2">
        <button class="tab-btn" :class="{'active': activeTab === 'friends'}" @click="activeTab = 'friends'">
            👥 My Friends <span x-show="friends.length" class="badge" x-text="friends.length"></span>
        </button>
        <button class="tab-btn" :class="{'active': activeTab === 'requests'}" @click="activeTab = 'requests'">
            📩 Requests <span x-show="pendingReceived.length" class="badge alert"
                x-text="pendingReceived.length"></span>
        </button>
        <button class="tab-btn" :class="{'active': activeTab === 'availability'}" @click="activeTab = 'availability'">
            📅 Availability
        </button>
        <button class="tab-btn" :class="{'active': activeTab === 'search'}" @click="activeTab = 'search'">
            🔍 Find Golfers
        </button>
    </div>

    <!-- Toast Notification -->
    <div x-show="toast.show" x-transition class="toast" :class="toast.type" x-text="toast.message"></div>

    <!-- My Friends Tab -->
    <div x-show="activeTab === 'friends'" x-transition>
        <template x-if="loading">
            <div class="loading-state glass-card text-center">
                <div class="spinner"></div>
                <p>Loading friends...</p>
            </div>
        </template>

        <template x-if="!loading && friends.length === 0">
            <div class="empty-state glass-card text-center">
                <div class="empty-icon">🤝</div>
                <h3>No friends yet</h3>
                <p class="text-muted">Search for golfers and send friend requests!</p>
                <button @click="activeTab = 'search'" class="btn-primary mt-1">Find Golfers</button>
            </div>
        </template>

        <div class="friends-grid grid-2">
            <template x-for="friend in friends" :key="friend.id">
                <div class="friend-card glass-card slide-up">
                    <img :src="friend.avatar" :alt="friend.name" class="friend-avatar">
                    <div class="friend-info">
                        <h3 x-text="friend.name"></h3>
                        <p class="text-muted">Handicap: <strong x-text="friend.handicap"></strong></p>
                        <p class="text-muted">Handicap: <strong x-text="friend.handicap"></strong></p>
                        <div class="friend-actions">
                            <button @click="openHistory(friend)" class="btn-secondary small">History</button>
                            <a :href="'/leaderboards/?type=friends'" class="btn-secondary small">Compare</a>
                            <button @click="removeFriend(friend.id)" class="btn-danger small">Remove</button>
                        </div>
                        <div class="mt-1">
                            <button @click="quickBook(friend.id)" class="btn-primary small full-width">Book Next Round</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Friend Requests Tab -->
    <div x-show="activeTab === 'requests'" x-transition>
        <!-- Received Requests -->
        <h3 class="section-title" x-show="pendingReceived.length">📥 Received Requests</h3>
        <div class="friends-grid grid-2 mb-2">
            <template x-for="request in pendingReceived" :key="request.id">
                <div class="friend-card glass-card slide-up request-card">
                    <img :src="request.avatar" :alt="request.name" class="friend-avatar">
                    <div class="friend-info">
                        <h3 x-text="request.name"></h3>
                        <p class="text-muted">Handicap: <strong x-text="request.handicap"></strong></p>
                        <div class="friend-actions">
                            <button @click="respondRequest(request.id, 'accept')"
                                class="btn-primary small">Accept</button>
                            <button @click="respondRequest(request.id, 'decline')"
                                class="btn-secondary small">Decline</button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Sent Requests -->
        <h3 class="section-title" x-show="pendingSent.length">📤 Sent Requests</h3>
        <div class="friends-grid grid-2 mb-2">
            <template x-for="request in pendingSent" :key="request.id">
                <div class="friend-card glass-card slide-up pending-card">
                    <img :src="request.avatar" :alt="request.name" class="friend-avatar">
                    <div class="friend-info">
                        <h3 x-text="request.name"></h3>
                        <p class="text-muted">Handicap: <strong x-text="request.handicap"></strong></p>
                        <span class="pending-badge">⏳ Pending</span>
                    </div>
                </div>
            </template>
        </div>

        <template x-if="pendingReceived.length === 0 && pendingSent.length === 0">
            <div class="empty-state glass-card text-center">
                <div class="empty-icon">📭</div>
                <h3>No pending requests</h3>
                <p class="text-muted">You're all caught up!</p>
            </div>
        </template>
    </div>

    <!-- Availability Tab -->
    <div x-show="activeTab === 'availability'" x-transition>
        <template x-if="loadingAvailability">
            <div class="loading-state glass-card text-center">
                <div class="spinner"></div>
                <p>Checking everyone's schedules...</p>
            </div>
        </template>

        <template x-if="!loadingAvailability && friendAvailability.length === 0">
            <div class="empty-state glass-card text-center">
                <div class="empty-icon">📅</div>
                <h3>No availability data</h3>
                <p class="text-muted">Connect with friends to see when they are playing!</p>
            </div>
        </template>

        <div class="availability-dashboard">
            <template x-for="friend in friendAvailability" :key="friend.id">
                <div class="availability-row glass-card slide-up mb-1">
                    <div class="user-brief">
                        <img :src="friend.avatar" class="mini-avatar">
                        <h4 x-text="friend.name"></h4>
                    </div>
                    
                    <div class="day-slots">
                        <div class="day-group">
                            <span class="day-label">Today</span>
                            <div class="slot-chips">
                                <template x-for="slot in friend.availability.today">
                                    <span class="slot-chip-v2" x-text="slot"></span>
                                </template>
                                <template x-if="friend.availability.today.length === 0">
                                    <span class="no-slots text-muted">Unavailable</span>
                                </template>
                            </div>
                        </div>
                        <div class="day-group">
                            <span class="day-label">Tomorrow</span>
                            <div class="slot-chips">
                                <template x-for="slot in friend.availability.tomorrow">
                                    <span class="slot-chip-v2" x-text="slot"></span>
                                </template>
                                <template x-if="friend.availability.tomorrow.length === 0">
                                    <span class="no-slots text-muted">Unavailable</span>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="row-actions">
                        <button @click="quickBook(friend.id)" class="btn-primary small">Book Round</button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Search Tab -->
    <div x-show="activeTab === 'search'" x-transition>
        <div class="search-bar glass-card mb-2">
            <input type="text" x-model="searchQuery" placeholder="Search by name..." @keyup.enter="searchUsers()"
                class="search-input">
            <button @click="searchUsers()" class="btn-primary" :disabled="searching">
                <span x-show="!searching">Search</span>
                <span x-show="searching">...</span>
            </button>
        </div>

        <template x-if="searchResults.length > 0">
            <div class="friends-grid grid-2">
                <template x-for="user in searchResults" :key="user.id">
                    <div class="friend-card glass-card slide-up">
                        <img :src="user.avatar" :alt="user.name" class="friend-avatar">
                        <div class="friend-info">
                            <h3 x-text="user.name"></h3>
                            <p class="text-muted">Handicap: <strong x-text="user.handicap"></strong></p>
                            <template x-if="user.status === 'none'">
                                <button @click="sendRequest(user.id)" class="btn-primary small full-width">Add
                                    Friend</button>
                            </template>
                            <template x-if="user.status === 'pending'">
                                <span class="pending-badge">⏳ Request Sent</span>
                            </template>
                            <template x-if="user.status === 'friend'">
                                <span class="friend-badge">✅ Friends</span>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="searched && searchResults.length === 0">
            <div class="empty-state glass-card text-center">
                <div class="empty-icon">🔍</div>
                <h3>No golfers found</h3>
                <p class="text-muted">Try a different search term</p>
            </div>
        </template>
    </div>

    <!-- Friend History Modal -->
    <div class="modal-wrapper" x-show="showHistoryModal" style="display: none;">
        <div class="modal-backdrop" @click="showHistoryModal = false" x-transition.opacity></div>
        <div class="modal-dialog glass-card" x-transition:enter="transition ease-out duration-300">
            <div class="modal-header">
                <h3>History with <span x-text="selectedFriend?.name"></span></h3>
                <button @click="showHistoryModal = false" class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <template x-if="loadingHistory">
                    <div class="text-center p-2"><div class="spinner"></div></div>
                </template>
                <template x-if="!loadingHistory && friendHistory.length === 0">
                    <div class="text-center p-2">
                        <p class="text-muted">No rounds played together yet.</p>
                        <button @click="quickBook(selectedFriend.id)" class="btn-primary mt-1">Book First Round</button>
                    </div>
                </template>
                <div class="history-list" x-show="!loadingHistory && friendHistory.length > 0">
                    <template x-for="round in friendHistory" :key="round.id">
                        <div class="history-item flex-between p-1 border-bottom">
                            <div>
                                <strong x-text="round.course"></strong>
                                <div class="text-muted small" x-text="round.date"></div>
                            </div>
                            <div class="flex gap-1 align-center">
                                <span class="badge" x-text="round.score || '-'"></span>
                                <a :href="'/log-round?round_id=' + round.id" class="btn-secondary small">View</a>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="mt-2 text-center" x-show="!loadingHistory">
                    <button @click="quickBook(selectedFriend.id)" class="btn-primary full-width">Book Next Round</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function friendsApp() {
        return {
            activeTab: 'friends',
            loading: true,
            searching: false,
            searched: false,
            searchQuery: '',
            friends: [],
            pendingReceived: [],
            pendingSent: [],
            searchResults: [],
            friendAvailability: [],
            loadingAvailability: false,
            searchResults: [],
            friendAvailability: [],
            loadingAvailability: false,
            toast: { show: false, message: '', type: 'success' },
            showHistoryModal: false,
            selectedFriend: null,
            friendHistory: [],
            loadingHistory: false,

            init() {
                this.loadFriends();
                this.loadAvailability();
            },

            loadAvailability() {
                this.loadingAvailability = true;
                fetch('/wp-json/teedup/v1/friends/availability', {
                    headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' }
                })
                .then(res => res.json())
                .then(data => {
                    this.friendAvailability = data.friends || [];
                    this.loadingAvailability = false;
                });
            },

            quickBook(friendId) {
                // Redirect to round creator with friend pre-selected
                window.location.href = `/log-round?partner=${friendId}&intent=book`;
            },

            showToast(message, type = 'success') {
                this.toast = { show: true, message, type };
                setTimeout(() => this.toast.show = false, 3000);
            },

            loadFriends() {
                this.loading = true;
                fetch('/wp-json/teedup/v1/friends/list', {
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
                })
                    .then(res => res.json())
                    .then(data => {
                        this.friends = data.friends || [];
                        this.pendingReceived = data.pending_received || [];
                        this.pendingSent = data.pending_sent || [];
                        this.loading = false;
                    });
            },

            searchUsers() {
                if (!this.searchQuery.trim()) return;
                this.searching = true;
                this.searched = true;

                // Using WordPress user search via a custom approach
                // For simplicity, we'll use the REST API with a filter
                fetch('/wp-json/wp/v2/users?search=' + encodeURIComponent(this.searchQuery) + '&per_page=20', {
                    headers: {
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                }
                })
                    .then(res => res.json())
                    .then(data => {
                        const currentUserId = <?php echo get_current_user_id(); ?>;
                        const friendIds = this.friends.map(f => f.id);
                        const sentIds = this.pendingSent.map(f => f.id);

                        this.searchResults = data
                            .filter(u => u.id !== currentUserId)
                            .map(u => ({
                                id: u.id,
                                name: u.name,
                                avatar: u.avatar_urls?.['96'] || '',
                                handicap: 'NH', // Would need additional meta
                                status: friendIds.includes(u.id) ? 'friend' :
                                    sentIds.includes(u.id) ? 'pending' : 'none'
                            }));
                        this.searching = false;
                    });
            },

            sendRequest(userId) {
                fetch('/wp-json/teedup/v1/friends/request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                    body: JSON.stringify({ friend_id: userId })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.showToast('Friend request sent!');
                            // Update UI
                            const user = this.searchResults.find(u => u.id === userId);
                            if (user) user.status = 'pending';
                            this.loadFriends();
                        } else {
                            this.showToast(data.message || 'Error sending request', 'error');
                        }
                    });
            },

            respondRequest(requesterId, action) {
                fetch('/wp-json/teedup/v1/friends/respond', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                },
                    body: JSON.stringify({ requester_id: requesterId, action })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.showToast(data.message);
                            this.loadFriends();
                        }
                    });
            },

            removeFriend(friendId) {
                if (!confirm('Remove this friend?')) return;

                fetch('/wp-json/teedup/v1/friends/remove', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                    },
                    body: JSON.stringify({ friend_id: friendId })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.showToast('Friend removed');
                            this.loadFriends();
                        }
                    });
            },

            openHistory(friend) {
                this.selectedFriend = friend;
                this.showHistoryModal = true;
                this.loadingHistory = true;
                this.friendHistory = [];

                fetch(`/wp-json/teedup/v1/friends/history?friend_id=${friend.id}`, {
                    headers: { 'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>' }
                })
                .then(res => res.json())
                .then(data => {
                    this.friendHistory = data.history || [];
                    this.loadingHistory = false;
                });
            }
        }
    }
</script>

<style>
    .friends-container {
        max-width: 800px;
        padding-bottom: 4rem;
    }

    .tab-nav {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .tab-btn {
        padding: 0.75rem 1.5rem;
        border: 2px solid #ddd;
        background: white;
        border-radius: 25px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s;
        position: relative;
    }

    .tab-btn:hover {
        border-color: var(--primary);
    }

    .tab-btn.active {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .badge {
        display: inline-block;
        background: rgba(255, 255, 255, 0.3);
        padding: 0.1rem 0.5rem;
        border-radius: 10px;
        font-size: 0.75rem;
        margin-left: 0.3rem;
    }

    .badge.alert {
        background: #f44336;
        color: white;
    }

    .toast {
        position: fixed;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        padding: 1rem 2rem;
        border-radius: 12px;
        background: var(--primary);
        color: white;
        font-weight: 500;
        z-index: 9999;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .toast.error {
        background: #f44336;
    }

    .search-bar {
        display: flex;
        gap: 1rem;
        padding: 1rem;
    }

    .search-input {
        flex-grow: 1;
        padding: 0.75rem 1rem;
        border: 2px solid #ddd;
        border-radius: 12px;
        font-size: 1rem;
    }

    .search-input:focus {
        border-color: var(--primary);
        outline: none;
    }

    .friends-grid {
        gap: 1.5rem;
    }

    .friend-card {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding: 1.5rem;
    }

    .friend-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid var(--primary);
    }

    .friend-info {
        flex-grow: 1;
    }

    .friend-info h3 {
        margin-bottom: 0.2rem;
        font-size: 1.2rem;
    }

    .friend-info p {
        margin-bottom: 1rem;
        font-size: 0.9rem;
    }

    .friend-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-danger {
        background: transparent;
        border: 1px solid #f44336;
        color: #f44336;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        cursor: pointer;
    }

    .btn-danger:hover {
        background: #f44336;
        color: white;
    }

    .section-title {
        margin: 1.5rem 0 1rem;
        font-size: 1.1rem;
        color: var(--text-muted);
    }

    .request-card {
        border-left: 4px solid #4caf50;
    }

    .pending-card {
        opacity: 0.7;
    }

    .pending-badge,
    .friend-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .pending-badge {
        background: #fff3e0;
        color: #ff9800;
    }

    .friend-badge {
        background: #e8f5e9;
        color: #4caf50;
    }

    .loading-state,
    .empty-state {
        padding: 3rem;
    }

    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #eee;
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

/* Availability Dashboard */
    .availability-row {
        display: grid;
        grid-template-columns: 150px 1fr 120px;
        align-items: center;
        padding: 1rem 1.5rem;
        gap: 1.5rem;
    }

    @media (max-width: 600px) {
        .availability-row {
            grid-template-columns: 1fr;
            text-align: center;
        }
        .user-brief { flex-direction: column; }
    }

    .user-brief {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .mini-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 2px solid var(--primary);
    }

    .day-slots {
        display: flex;
        gap: 2rem;
    }

    .day-group {
        flex: 1;
    }

    .day-label {
        display: block;
        font-size: 0.7rem;
        text-transform: uppercase;
        font-weight: 700;
        color: var(--text-muted);
        margin-bottom: 0.4rem;
    }

    .slot-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }

    .slot-chip-v2 {
        font-size: 0.75rem;
        background: #f0fdf4;
        color: #166534;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
        border: 1px solid #bbf7d0;
        font-weight: 600;
    }

    .no-slots {
        font-size: 0.8rem;
    }
</style>

<?php get_footer(); ?>
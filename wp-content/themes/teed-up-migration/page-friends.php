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
                        <div class="friend-actions">
                            <a :href="'/leaderboards/?type=friends'" class="btn-secondary small">Compare</a>
                            <button @click="removeFriend(friend.id)" class="btn-danger small">Remove</button>
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
            toast: { show: false, message: '', type: 'success' },

            init() {
                this.loadFriends();
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

    .empty-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }
</style>

<?php get_footer(); ?>
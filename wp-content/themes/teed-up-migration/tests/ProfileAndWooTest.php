<?php
use PHPUnit\Framework\TestCase;

class ProfileAndWooTest extends TestCase {
    
    public function setUp(): void {
        global $wp_mocks, $redirect_to;
        $wp_mocks = [
            'user_meta' => [],
            'options' => [],
            'posts' => [],
            'users' => []
        ];
        $redirect_to = '';
        $_POST = [];
    }

    public function test_profile_update_success() {
        global $redirect_to;
        
        $_POST['fullname'] = 'New Name';
        $_POST['email'] = 'new@example.com';
        $_POST['current_password'] = ''; 
        $_POST['new_password'] = '';
        $_POST['profile_nonce'] = 'valid_nonce';
        
        teed_up_handle_profile_update();

        $this->assertStringContainsString('profile_update=success', $redirect_to);
    }

    public function test_sync_membership_products() {
        teed_up_sync_membership_products();

        $scratch_product_id = get_option('teed_up_product_id_scratch');
        $this->assertGreaterThan(0, $scratch_product_id);
    }
}
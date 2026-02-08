<?php
use PHPUnit\Framework\TestCase;

class MembershipTest extends TestCase
{

    public function setUp(): void
    {
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

    public function test_get_membership_plans()
    {
        $plans = teed_up_get_membership_plans();
        $this->assertArrayHasKey('free', $plans);
    }

    public function test_get_user_plan_defaults_to_free()
    {
        $plan = teed_up_get_user_plan(1);
        $this->assertEquals('free', $plan);
    }

    public function test_get_user_plan_returns_saved_value()
    {
        update_user_meta(1, 'membership_plan', 'pro');
        $plan = teed_up_get_user_plan(1);
        $this->assertEquals('pro', $plan);
    }

    public function test_registration_handler_saves_correct_data()
    {
        global $redirect_to, $wp_mocks;

        $_POST['email'] = 'test@example.com';
        $_POST['password'] = 'password123';
        $_POST['fullname'] = 'Test User';
        $_POST['membership_plan'] = 'scratch';
        $_POST['registration_nonce'] = 'valid_nonce';

        teed_up_handle_registration();

        $this->assertStringContainsString('/dashboard?registration=success', $redirect_to);
    }

    public function test_registration_fails_missing_fields()
    {
        global $redirect_to;
        $_POST['email'] = 'test@example.com';
        $_POST['registration_nonce'] = 'valid_nonce';

        teed_up_handle_registration();

        $this->assertStringContainsString('registration=failed&reason=missing_fields', $redirect_to);
    }

    public function test_registration_fails_invalid_email()
   
    {
        global $redirect_to;
        $_POST['email'] = 'invalid';
        $_POST['password'] = 'pass';
        $_POST['registration_nonce'] = 'valid_nonce';

        teed_up_handle_registration();

        $this->assertStringContainsString('registration=failed&reason=invalid_email', $redirect_to);
    }
}
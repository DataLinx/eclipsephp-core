<?php

use Eclipse\Core\Models\Site;
use Eclipse\Core\Models\User;
use Eclipse\Core\Settings\UserSettings;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->set_up_common_user_and_tenant();
});

test('default user setting value is used when user has no settings yet', function () {
    // Get the default settings (where user_id and site_id are null)
    $defaultSettings = app(UserSettings::class);

    // Verify that the settings are loaded from the default values
    $this->assertEquals('', $defaultSettings->outgoing_email_address);
    $this->assertEquals('', $defaultSettings->outgoing_email_signature);

    // Verify that no user-specific settings exist in the database
    $userSettings = DB::table('user_site_settings')
        ->where('user_id', $this->user->id)
        ->where('site_id', Filament::getTenant()->id)
        ->get();

    $this->assertCount(0, $userSettings);
});

test('user settings are saved with correct user_id and site_id', function () {
    // Get the settings instance
    $settings = app(UserSettings::class);

    // Update the settings
    $settings->outgoing_email_address = 'test@example.com';
    $settings->outgoing_email_signature = '<p>Test Signature</p>';
    $settings->save();

    // Verify that the settings were saved with the correct user_id and site_id
    $savedSettings = DB::table('user_site_settings')
        ->where('user_id', $this->user->id)
        ->where('site_id', Filament::getTenant()->id)
        ->get();

    $this->assertCount(2, $savedSettings); // Two settings: email address and signature

    // Verify the values were saved correctly
    $emailSetting = $savedSettings->where('name', 'outgoing_email_address')->first();
    $signatureSetting = $savedSettings->where('name', 'outgoing_email_signature')->first();

    $this->assertNotNull($emailSetting);
    $this->assertNotNull($signatureSetting);
    $this->assertEquals(json_encode('test@example.com'), $emailSetting->payload);
    $this->assertEquals(json_encode('<p>Test Signature</p>'), $signatureSetting->payload);
});

test('user-specific settings are loaded instead of defaults', function () {
    // First, save user-specific settings
    $settings = app(UserSettings::class);
    $settings->outgoing_email_address = 'user@example.com';
    $settings->outgoing_email_signature = '<p>User Signature</p>';
    $settings->save();

    // Now, get a fresh instance of the settings
    $freshSettings = app(UserSettings::class);

    // Verify that the user-specific settings are loaded
    $this->assertEquals('user@example.com', $freshSettings->outgoing_email_address);
    $this->assertEquals('<p>User Signature</p>', $freshSettings->outgoing_email_signature);
});

test('site-specific settings are not used on other sites', function () {
    // Create a second site
    $secondSite = Site::factory()->create();

    // Save settings for the current site
    $settings = app(UserSettings::class);
    $settings->outgoing_email_address = 'site1@example.com';
    $settings->outgoing_email_signature = '<p>Site 1 Signature</p>';
    $settings->save();

    // Switch to the second site
    $originalSite = Filament::getTenant();
    Filament::setTenant($secondSite);

    // Get settings for the second site
    $secondSiteSettings = app(UserSettings::class);

    // Verify that the second site uses default settings, not the first site's settings
    $this->assertEquals('', $secondSiteSettings->outgoing_email_address);
    $this->assertEquals('', $secondSiteSettings->outgoing_email_signature);

    // Restore the original site
    Filament::setTenant($originalSite);

    // Test settings again for the first site
    $settings->refresh();
    $this->assertEquals('site1@example.com', $settings->outgoing_email_address);
    $this->assertEquals('<p>Site 1 Signature</p>', $settings->outgoing_email_signature);
});

test('user-specific settings are not used for other users', function () {
    // Save settings for the current user
    $settings = app(UserSettings::class);
    $settings->outgoing_email_address = 'user1@example.com';
    $settings->outgoing_email_signature = '<p>User 1 Signature</p>';
    $settings->save();

    // Create and switch to a second user
    $secondUser = User::factory()->create();
    $secondUser->sites()->attach(Filament::getTenant());

    Auth::login($secondUser);

    // Get settings for the second user
    $secondUserSettings = app(UserSettings::class);

    // Verify that the second user uses default settings, not the first user's settings
    $this->assertEquals('', $secondUserSettings->outgoing_email_address);
    $this->assertEquals('', $secondUserSettings->outgoing_email_signature);
});

test('forUser method fetches settings for a specific user', function () {
    // Save settings for the current user
    $settings = app(UserSettings::class);
    $settings->outgoing_email_address = 'user1@example.com';
    $settings->outgoing_email_signature = '<p>User 1 Signature</p>';
    $settings->save();

    // Create a second user
    $secondUser = User::factory()->create();
    $secondUser->sites()->attach(Filament::getTenant());

    // Save settings for the second user
    Auth::login($secondUser);
    $secondUserSettings = app(UserSettings::class);
    $secondUserSettings->outgoing_email_address = 'user2@example.com';
    $secondUserSettings->outgoing_email_signature = '<p>User 2 Signature</p>';
    $secondUserSettings->save();

    // Switch back to the first user
    Auth::login($this->user);

    // Use forUser to get settings for the second user while authenticated as the first user
    $fetchedSecondUserSettings = UserSettings::forUser($secondUser->id);

    // Verify that the fetched settings match the second user's settings
    $this->assertEquals('user2@example.com', $fetchedSecondUserSettings->outgoing_email_address);
    $this->assertEquals('<p>User 2 Signature</p>', $fetchedSecondUserSettings->outgoing_email_signature);

    // Verify that the current user's settings are still accessible
    $currentUserSettings = app(UserSettings::class);
    $this->assertEquals('user1@example.com', $currentUserSettings->outgoing_email_address);
    $this->assertEquals('<p>User 1 Signature</p>', $currentUserSettings->outgoing_email_signature);
});

test('forUser method works in non-user context', function () {
    // Set settings for the current user
    $userSettings = app(UserSettings::class);
    $userSettings->outgoing_email_address = 'user@example.com';
    $userSettings->outgoing_email_signature = '<p>User Signature</p>';
    $userSettings->save();

    // Log out and fetch the settings
    Auth::logout();
    $settings = $this->user->getSettings();
    $this->assertInstanceOf(UserSettings::class, $settings);

    // Assert values
    $this->assertEquals('user@example.com', $settings->outgoing_email_address);
    $this->assertEquals('<p>User Signature</p>', $settings->outgoing_email_signature);
});

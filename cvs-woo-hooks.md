
# Verify-Woo Hooks Documentation

This document outlines the various action and filter hooks provided by the Verify-Woo plugin, allowing developers to extend and customize its functionality.

---

## Action Hooks

Action hooks allow you to execute your custom code at specific points within the plugin's execution flow.

### `verify_woo_send_otp_sms`

Fires when an OTP (One-Time Password) code has been generated and is ready to be sent via SMS. This hook is crucial for integrating with custom SMS gateways.

* **Description:** Use this action to send the generated OTP using your preferred SMS service.
* **Parameters:**
    * `$phone` (string): The phone number to which the OTP should be sent.
    * `$otp_code` (int): The generated OTP code.
* **Example Usage:**

    ```php
    add_action( 'verify_woo_send_otp_sms', 'my_custom_sms_sender', 10, 2 );

    function my_custom_sms_sender( $phone, $otp_code ) {
        // Your custom SMS sending logic here.
        // For example, using a hypothetical SMS API:
        // $sms_api->send_message( $phone, "Your OTP is: " . $otp_code );
        error_log( "Sending OTP {$otp_code} to {$phone} via custom SMS gateway." );
    }
    ```

### `verify_woo_before_login_existing_user`

Fires right before an existing user is logged in via OTP verification.

* **Description:** This hook allows you to add custom logic or perform additional checks before an authenticated user is logged into their account.
* **Parameters:**
    * `$user` (WP_User): The `WP_User` object representing the user being logged in.
* **Example Usage:**

    ```php
    add_action( 'verify_woo_before_login_existing_user', 'my_custom_pre_login_actions', 10, 1 );

    function my_custom_pre_login_actions( $user ) {
        // Log the login attempt, update user meta, etc.
        error_log( 'User ' . $user->user_login . ' is about to be logged in via OTP.' );
    }
    ```

### `verify_woo_after_register_user`

Fires immediately after a new user is registered via OTP auto-registration.

* **Description:** This hook is useful for performing post-registration actions, such as sending welcome emails, setting additional user meta, or integrating with CRM systems.
* **Parameters:**
    * `$user_id` (int): The ID of the newly registered user.
    * `$phone` (string): The phone number used for registration.
* **Example Usage:**

    ```php
    add_action( 'verify_woo_after_register_user', 'my_custom_post_registration_actions', 10, 2 );

    function my_custom_post_registration_actions( $user_id, $phone ) {
        // Send a welcome email to the new user
        // wp_mail( get_user_by( 'id', $user_id )->user_email, 'Welcome!', 'Thank you for registering!' );

        // Add custom user meta
        update_user_meta( $user_id, 'signup_method', 'otp_registration' );

        error_log( "New user registered with ID: {$user_id} and phone: {$phone}" );
    }
    ```

### `verify_woo_tab_{$slug}_content`
Fires the content rendering action for a specific tab in the Verify Woo plugin admin interface.

* **Description:**
This action allows developers to hook into and output the content for a custom tab,
based on the dynamic `$slug` provided. Each tab should register its content output
via a callback hooked to this action.
* **Parameters:**
    * `$slug` (string): The slug of tab.
***Example Usage:**
```php
  add_action( 'verify_woo_tab_my-tab-content', function( $slug ) {
      echo '<p>My custom tab content goes here for slug: ' . esc_html( $slug ) . '</p>';
  }, 10, 1 );
```
---

## Filter Hooks

Filter hooks allow you to modify data before it is used by the plugin or returned by a function.

### `verify_woo_login_redirect_url`

Modifies the URL where users are redirected after a successful OTP login.

* **Description:** By default, users are redirected to the WooCommerce My Account page. You can use this filter to change the redirect destination.
* **Parameters:**
    * `$redirect_url` (string): The default WooCommerce My Account page URL.
* **Returns:** (string) The new redirect URL.
* **Example Usage:**

    ```php
    add_filter( 'verify_woo_login_redirect_url', 'my_custom_login_redirect', 10, 1 );

    function my_custom_login_redirect( $redirect_url ) {
        // Redirect to the shop page after login
        return wc_get_page_permalink( 'shop' );
    }
    ```

### `verify_woo_otp_rate_limit_seconds`

Filters the cooldown time in seconds between OTP requests for the same phone number.

* **Description:** Allows you to adjust the rate limiting duration to prevent abuse of the OTP request functionality. The default is 120 seconds (2 minutes).
* **Parameters:**
    * `$seconds` (int): The default cooldown time in seconds (default 120).
* **Returns:** (int) The modified cooldown time in seconds.
* **Example Usage:**

    ```php
    add_filter( 'verify_woo_otp_rate_limit_seconds', 'my_custom_otp_rate_limit', 10, 1 );

    function my_custom_otp_rate_limit( $seconds ) {
        // Set the rate limit to 60 seconds (1 minute)
        return 60;
    }
    ```

### `verify_woo_otp_expiration`

Changes how long OTP codes are valid (expiration time).

* **Description:** Determines the lifespan of a generated OTP. The default expiration is 5 minutes.
* **Parameters:**
    * `$expiration` (int): Default expiration time in seconds (5 minutes).
* **Returns:** (int) The new OTP expiration time in seconds.
* **Example Usage:**

    ```php
    add_filter( 'verify_woo_otp_expiration', 'my_custom_otp_expiration', 10, 1 );

    function my_custom_otp_expiration( $expiration ) {
        // Set OTP to expire after 10 minutes
        return 10 * MINUTE_IN_SECONDS;
    }
    ```

### `verify_woo_max_otp_attempts`

Set the maximum number of attempts allowed for OTP verification.

* **Description:** Controls how many times a user can incorrectly enter an OTP before they are required to request a new one. The default is 3 attempts.
* **Parameters:**
    * `$max_attempts` (int): Default maximum attempts (3).
* **Returns:** (int) The new maximum attempt limit.
* **Example Usage:**

    ```php
    add_filter( 'verify_woo_max_otp_attempts', 'my_custom_max_otp_attempts', 10, 1 );

    function my_custom_max_otp_attempts( $max_attempts ) {
        // Allow up to 5 attempts
        return 5;
    }
    ```

### `verify_woo_username_prefix`

Allows modifying the prefix used when creating a new username from a phone number during auto-registration.

* **Description:** When a new user is auto-registered based on their phone number, a username is generated using a prefix and the sanitized phone number. This filter allows you to customize that prefix. The default prefix is `'customer_'`.
* **Parameters:**
    * `$prefix` (string): The default prefix, e.g., `'customer_'`.
    * `$clean_phone` (string): The sanitized phone number without any non-numeric characters.
* **Returns:** (string) The modified prefix.
* **Example Usage:**

    ```php
    add_filter( 'verify_woo_username_prefix', 'my_custom_username_prefix', 10, 2 );

    function my_custom_username_prefix( $prefix, $clean_phone ) {
        // Use 'user_' as the prefix
        return 'user_';
    }
    ```

### `verify_woo_auto_register_enabled`

Control whether new users can be auto-registered via OTP.

* **Description:** This filter enables or disables the automatic registration of new users if their phone number doesn't correspond to an existing account. The default is `true`.
* **Parameters:**
    * `$enabled` (bool): `true` to allow auto-registration, `false` to disable.
    * `$phone` (string): The phone number attempting login.
* **Returns:** (bool) Modified flag to allow or deny auto-registration.
* **Example Usage:**

    ```php
    add_filter( 'verify_woo_auto_register_enabled', 'disable_auto_registration', 10, 2 );

    function disable_auto_registration( $enabled, $phone ) {
        // Disable auto-registration for all users
        return false;

        // Or, conditionally disable for certain phone numbers
        // if ( strpos( $phone, '123' ) === 0 ) {
        //     return false;
        // }
        // return $enabled;
    }
    ```

### `verify_woo_new_user_role`

Modify the role assigned to newly registered users during auto-registration.

* **Description:** The default role for new users created via OTP auto-registration is `'customer'`. You can change this to any valid WordPress user role.
* **Parameters:**
    * `$role` (string|array): Default role is `'customer'`.
    * `$phone` (string): The phone number used for registration.
* **Returns:** (string) The user role.
* **Example Usage:**

    ```php
    add_filter( 'verify_woo_new_user_role', 'assign_subscriber_role_to_new_users', 10, 2 );

    function assign_subscriber_role_to_new_users( $roles, $phone ) {
        // Assign the 'subscriber' role instead of 'customer'
        return 'subscriber';
    }
    ```

### `verify_woo_new_user_data`

Change the data array used to register new users during auto-registration.

* **Description:** This filter allows you to modify the array of user data that is passed to `wp_insert_user()` when a new user is auto-registered. You can add more user meta, set a specific email, or alter the generated username/password.
* **Parameters:**
    * `$user_data` (array): An array of user data, including:
        * `user_login` (string): The generated username.
        * `user_pass` (string): A randomly generated password.
        * `role` (string): The role of the new user.
    * `$phone` (string): The phone number used to register.
* **Returns:** (array) The modified user data array.
* **Example Usage:**

    ```php
    add_filter( 'verify_woo_new_user_data', 'add_custom_new_user_data', 10, 2 );

    function add_custom_new_user_data( $user_data, $phone ) {
        // Example: Set a default email based on the phone number (not recommended for production)
        $user_data['user_email'] = $phone . '@example.com';

        // Example: Add first and last name (if you have a way to derive it or it's optional)
        // $user_data['first_name'] = 'New';
        // $user_data['last_name']  = 'Customer';

        return $user_data;
    }
    ```

### `verify_woo_login_form_template_path`

Filters the path to the custom login form template.

* **Description:** This filter allows developers to completely override the path to the custom login form template used by Verify-Woo, enabling the use of a completely custom login form design.
* **Parameters:**
    * `$custom_template_path` (string): The full default path to the custom login form (`CVS_PLUGIN_DIR . '/public/partials/forms/verify-woo-form-1.php'`).
* **Returns:** (string) The full path to your custom login form template.
* **Example Usage:**

    ```php
    add_filter( 'verify_woo_login_form_template_path', 'my_custom_login_form_template', 10, 1 );

    function my_custom_login_form_template( $custom_template_path ) {
        // Use a template from your theme's directory
        return get_stylesheet_directory() . '/woocommerce/myaccount/custom-verify-woo-login-form.php';

        // Or from another plugin's directory
        // return MY_OTHER_PLUGIN_DIR . '/templates/otp-login-form.php';
    }
    ```


### `verify_woo_admin_settings_tabs`
Filters the list of available settings tabs in the VerifyWoo admin page.

* **Description:**
  This filter allows external plugins, themes, or custom code to inject additional
  tabs into the VerifyWoo settings UI. Each tab must have a unique slug (used as
  an identifier in the URL and file structure), and a human-readable label.
 * **Parameters:**
 * `$tabs` (array): An associative array of tab slugs and labels. Example: ['general' => 'General', 'advanced' => 'Advanced']
* **Example Usage:**
  ```php
  add_filter( 'verify_woo_admin_settings_tabs', function ( $tabs ) {
      $tabs['custom-tab'] = __( 'Custom Tab', 'your-textdomain' );
      return $tabs;
  } );
  ```
Make sure the content for the tab exists in:
  `CVS_PLUGIN_DIR/admin/partials/tabs/custom-tab/content.php`

  
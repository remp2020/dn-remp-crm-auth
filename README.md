# REMP CRM Auth plugin

The REMP CRM Auth plugin allows you to read basic user and subscription information about CRM user visiting your WP website. Also adds simple login form with handling.

## How to install

### From this repository

Go to the [releases](https://github.com/remp2020/dn-remp-crm-auth/releases) section of the repository and download the most recent release.

Then, from your WordPress administration panel, go to `Plugins > Add New` and click the `Upload Plugin` button at the top of the page.

## How to Use

From your WordPress administration panel go to `Plugins > Installed Plugins` and scroll down until you find `DN REMP CRM Auth` plugin. You will need to activate it first.

### Configuration

To configure the plugin, add `DN_REMP_HOST` constant definition into your `wp-config.php` file with the correct host of REMP installation. 

```php
define( 'DN_REMP_HOST', 'https://word.press/remp' );
```

#### Authorization token

CRM requires every call to be authorized by a user token. For each request presence of `Authorization: Bearer token` HTTP header is validated. The token found in the header needs to match the token assigned to the user. This plugin gets user token from cookie `n_token` which is set in login form after successful user login.

### Template functions

Plugin exposes 3 functions for usage in your theme or related plugin. 

#### `remp_get_user_token()`

##### *Return value:*

Returns *String* user token or `false` if user is not logged in.

#### `remp_get_user( $data = 'info' )`

##### *Params:*

| Name | Value | Required | Description |
| --- |---| --- | --- |
| data | *String* | no | wether to return basic user `info`, or user `subscriptions` |

##### *Return value:*

Returns *Array* containing data, `false` if user is not logged in or `null` if not correctly configured.

##### *Examples:*

```php
print_r( remp_get_user( 'info' ) );
```

Response:

```php
Array
(
    [body] => [
        [status] => ok
        [user] => Array
            (
                [id] => 14910
                [email] => michalrusina@gmail.com
                [first_name] => 
                [last_name] => 
            )
    ],
    ['error_msg'] => ''
)
```

```php
print_r( remp_get_user( 'subscriptions' ) );
```

Response:

```php
Array
(
    [body] => [
        [status] => ok
        [subscriptions] => Array
            (
                [0] => Array
                    (
                        [start_at] => Array
                            (
                                [date] => 2018-07-18 00:00:00
                                [timezone_type] => 3
                                [timezone] => Europe/Bratislava
                            )
    
                        [end_at] => Array
                            (
                                [date] => 2020-09-25 00:00:00
                                [timezone_type] => 3
                                [timezone] => Europe/Bratislava
                            )
    
                        [code] => discount_10_months_web_app_klub
                        [access] => Array
                            (
                                [0] => mobile
                            )
    
                    )
    
            )
    ],
    ['error_msg'] => ''
)
```

#### `remp_login_form( $echo = true )`

##### *Params:*

| Name | Value | Required | Description |
| --- |---| --- | --- |
| echo | *Bool* | no | wether to return or echo the form HTML |

##### *Return value:*

Returns *String* containing HTML form. The form HTML can be altered by `remp_login_form_html` filter hook.

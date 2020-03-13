# REMP CRM Auth plugin

The REMP CRM Auth plugin allows you to read basic user and subscription information about CRM user visiting your WP website. Also adds simple login form with handling.

## How to Install

### From this repository

Go to the [releases](https://github.com/remp2020/dn-remp-crm-auth/releases) section of the repository and download the most recent release.

Then, from your WordPress administration panel, go to `Plugins > Add New` and click the `Upload Plugin` button at the top of the page.

## How to Use

From your WordPress administration panel go to `Plugins > Installed Plugins` and scroll down until you find `DN REMP CRM Auth` plugin. You will need to activate it first.

### Configuration

#### Authorization token

Plugin requires every call to be authorized by a user token. Each request validates presence of `Authorization: Bearer token` header. The token found in the header needs to match the token assigned to the user.

To configure the plugin, add `DN_REMP_HOST` constant definition into your `wp-config.php` file with the coorect host of REMP installation. 

```php
define( 'DN_REMP_HOST', 'https://word.press/remp' );
```

### Template functions

Plugin exposes two functions.

#### `remp_get_user_token()`

##### *Return value:*

Returns *String* user token or `false` if user is not logged in.

#### `remp_get_user( $data = 'info' )`

##### *Params:*

| Name | Value | Required | Description |
| --- |---| --- | --- |
| data | *String* | no | Wether to return basic user `info`, or user `subscriptions` |

##### *Return value:*

Returns *Array* containing data, `false` if user is not logged in or `null` if not correctly configured.

##### *Example:*

```php
print_r( remp_get_user( 'info' ) );
```

Response:

```php
Array
(
    [status] => ok
    [user] => Array
        (
            [id] => 14910
            [email] => michalrusina@gmail.com
            [first_name] => 
            [last_name] => 
        )

)
```

```php
print_r( remp_get_user( 'subscriptions' ) );
```

Response:

```php
Array
(
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

)
```

#### `remp_login_form( $echo = true )`

##### *Params:*

| Name | Value | Required | Description |
| --- |---| --- | --- |
| echo | *Bool* | no | Wether to return or echo the form HTML |

##### *Return value:*

Returns *String* containing HTML form. The form HTML can be altered by filtering `remp_login_form_html`.

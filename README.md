
## Installation

You can install [`mobily-ws/api`](https://packagist.org/packages/mobily-ws/api)  via composer or by typing in command line  
```text
composer require mobily-ws/api
```

**OR**   include require in **composer.json** 
 
```json
{
"require": {
           "mobily-ws/api": "1.0.0"
       }
}
```

## Quickstart

### TO USE Api AFTER DOWNLOAD
 In order to use Mobily Api, you must take the following steps:
 1. Registration on the site through the following steps:
    
    1. Go to the following link: **[`mobily.ws`](http://www.mobily.ws/sms/index.php)**
    2. Go to page (Register now) at the top of the page
    3. Enter the information
    5. Enable sender name to send messages
 2. Download the Mobily Api and install it in your system
 3. Insert user information (ApiKEY or mobile and password) ,this information's is provided to you by the site
 In the function setInfo defines user information

### Mobily Api Portals
We provide many services that make it easy to use the api, and these are some our of the services: 
1. send sms
2. send sms using message template 
3. sending sms directly
4. sending status
5. Add mobile number as sender name
6. Activate mobile number as sender name
7. Check that the mobile number is activated as a sender's name
8. Possibility to change password
9. Retrieve password
10. Balance Inquiry
11. Delete sms
12. Add a text sender name
13. Activate the text sender name

## Services Example

### Send SMS message
You can  send SMS messages using the transmission gate to ensure the privacy of information and the speed of sending and ensure they arrive, and this portal provid the ability to sending messages to many numbers at once and without any effortless and tired, is the gate to send and receive messages using JSON technology And These an example of how to use the portal:
```php
<?php
require_once('MobilySms.php');
$sms = new MobilySms('user name','password','ApiKEY');
$result=$sms->sendMsg('This is Message','9662222222222,9662222222222,9662222222222','NEW SMS','17:30:00','12/30/2017',1,'deleteKey','curl');
?>

```

### Send SMS using message Template
This portal offers the ability to send SMS messages using a unified message template for different numbers. This portal allows you to add fixed message text and put symbols in it. This portal transmits the information of each number with the symbols in the message to form a message for each number. the operation :

```php
<?php
require_once('MobilySms.php');
$sms = new MobilySms('user name','password','ApiKEY');
$msg = "Welcome (1)، Your subscription date is up to (2)";
$msgKey = "(1),*,William,@,(2),*,12/10/2008***(1),*,jack,@,(2),*,10/10/2008";
$numbers='96622222222222,96622222222222';
$result=$sms->sendMsgWK($msg,$numbers,'aljauoni',$msgKey,'12:00:00','12/27/2017',0,'deleteKey','curl');

?>
```

### Balance Inquiry
You can inquire about mobily account balance through this portal by adding mobile number or Api KEY , this portal sends and returns JSON data and the following example shows how to use this portal :
```php
<?php
require_once('MobilySms.php');
$sms = new MobilySms('user name','password','ApiKEY');
$result=$sms->balance('curl');
?>
```

### Forget Password
You can retrieve the mobily account password through this portal by adding the mobile number or Api KEY to retrieve its password and the way to send the password either on the mobile number or on the email of the account, and this portal sends and returns data from JSON Type The following example shows how to use this portal:
```php
<?php
require_once('MobilySms.php');
// you must insert just user name or ApiKEY
$sms = new MobilySms('user name','password','ApiKEY');
// 1: that means send password to account mobile
$result=$sms->forgetPassword(1,'ar','curl');
// or you can use
// 2: that means send password to account email 
$result=$sms->forgetPassword(2,'ar','curl');

?>
```

### Change Password
You can change the password for mobily account through this portal by adding the mobile number or Api KEY to change its password and old and new password, and this portal sends and returns data of type JSON. , And as an example of the required data:
```php
<?php
require_once('MobilySms.php');
$sms = new MobilySms('user name','password','ApiKEY');
$result=$sms->changePassword('111','123','curl');

?>
```
## Documentation

The documentation for the **mobily.ws Api** is located **[`here`](http://www.mobily.ws/)**.

The PHP library documentation can be found **[`here`](http://www.mobily.ws/)**.

## Versions

`Mobily-api`'s versioning strategy can be found **[`here`](http://www.mobily.ws/)**.

## Prerequisites

* PHP >= 5.3
* The PHP JSON extension

# Getting help

If you need help installing or using the library, please contact Mobily.ws Support at **help@mobily.ws** first. mobily Support staff are well-versed in all of the mobily.ws Helper Libraries, and usually reply within 24 hours.

If you've instead found a bug in the library or would like new features added, go ahead and open issues or pull requests against this repo!
# api
# api
# api
# api
# api

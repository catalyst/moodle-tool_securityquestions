[![ci](https://github.com/catalyst/moodle-tool_securityquestions/actions/workflows/ci.yml/badge.svg?branch=MOODLE_401_STABLE)](https://github.com/catalyst/moodle-tool_securityquestions/actions/workflows/ci.yml?branch=MOODLE_401_STABLE)

# Security Questions 2FA

This plugin adds a framework for adding and enforcing security questions for users to perform a password reset. Currently it only operates on the Moodle Password reset page at `login/forgot_password`, however it is easily extensible to other forms.

* [Security Controls](#security-controls)
* [Setting Questions](#setting-questions)
* [Setting Question Responses](#setting-question-responses)
* [Resetting User Lockouts](#resetting-user-lockouts)
* [Installation](#installation)
* [Branches](#branches)
* [Templates](#templates)

Security Controls
-----------------

### Enable Plugin

This control is a hard kill switch for the plugin. By default it is unchecked, so the plugin can be properly setup and configured before use. Once setup is finished, enable this control, and the plugin will become active.

### Mandatory Security Questions

This control allows for security questions to be non-mandatory. This means that users will be able to navigate away from setting responses to questions when prompted after login. If users choose not to answer questions, they will not be prompted again in this session. They will be presented with the questions again on the next login. This control is designed for progressive rollouts of the plugin, where questions will be optional for a period of time, before being manually switched to mandatory.

### Respose Grace Period

This control is designed to work in tandem with the mandatory security questions control. The grace period is a period where questions will act as if they are not mandatory, and can be dismissed at the beginning of a session. The grace period is triggered at the first time a user is presented with the questions after questions are made mandatory. This is so users will not be blocked from submitting an assignment or entering an online exam, and can defer the question answering to another time. Set this control to 0 to disable the grace period.

### Minimum Number of Active Security Questions
This control is the minimum number of active security questions that must be active at one time. If there aren't enough questions active, users will not be prompted to set or answer questions during use of the moodle instance. This allows for diversity of questions and responses, as users do not have to answer all of the questions, in a typical installation. Defaults to 10.

### Minimum Number of User Answered Questions

This control is the minimum number of active questions that a user must answer, in order to be able to use the Moodle instance. If users do not meet the number of answered active questions, they will be prompted to answer additional questions, until they meet the minimum number of active questions. If a question is deprecated, and will no longer be used for authentication, users will be prompted to answer additional questions until the minimum is met. Defaults to 5.

### Questions Required for Verification

This control is the minimum number of questions that users must answer when they attempt to perform security related account actions, such as changing or recovering a password. Users must answer these questions correctly in the same session. E.g. users will be presented with *n* security questions that they have provided responses to. They must answer both correctly on the same page, and submit at the same time, to proceed to account security controls. If a user fails the security questions, they will be presented with another set of questions, until they succeed, or fail 3 times, at which point they will be unable to perform any account based actions that require higher security verification. Users most contact an Administrator to unlock their questions.

### Question Duration

When users are presented with questions that need to be answered for verification, they will be active for a certain period of time. This control specifies the duration of the active period. When a user first visits a page that requires verification, the questions will be generated, and presented to the user. This is the start point of the period. If users reload the page, or visit other pages that require verification, they will be presented the same question, until the end of the period. Once the period has passed, and a user visits a page that requires verification, they will be presented with fresh questions.

### Answer Attempts Before Lockout

This control is the number of attempts users will have at their security question verification. Each failed attempt will count up, and when this threshold is hit, the security question response check will always fail, and users will be informed to seek assistance from a system administrator. If a user gets both questions wrong on the page, it will only count as one failed attempt. When the user successfully answers their security questions, and a password reset is successfully performed, the counter will be set to 0. If a user is locked out, and an administrator performs a reset of the account lockout, the counter for failed attempts is reset to 0.

### Question File to Use

This control allows users to specify a path for a questions file to use. This path is based off the moodle home directory. See [Question Templates](templates) for more information.

Setting Questions
-----------------
This page allows an admin to set the security questions for use in the Moodle instance. Entering a question at the top, and clicking 'Add Question', will add this question to the active question pool. Questions by default start not deprecated, until the questions are manually deprecated by an Admin. The plugin will not be enabled until the number of active security questions is equal to or higher than the number set in the 'Minimum number of active security questions' security control.

To deprecate a question, click the deprecate question link in the action column of the question to be deprecated. This removes the question from the active questions pool, after which users won't be able to set new responses to this question, and the question won't be able to be used to authenticate. When a question has been deprecated, if any users are now under the amount of required questions answered, they will be prompted to set addition responses. Admins will be unable to deprecate a question if it puts the amount of active questions under the required amount. More questions must be added first to deprecate a question.

Questions that have been deprecated and have no-one actively using them may be deleted, using the delete link in the action column. This is not intended for questions that have been in use, but for questions that were entered into the pool incorrectly, or have a typo for example. Any question that has been responded to by a user may not be deleted to maintain history of questions in use.

Setting Question Responses
--------------------------
When a user first logs in after the plugin installation, if the plugin is active, with enough questions being set, users will be prompted to set responses to the security questions. They must set enough responses set to satisfy the security control 'Minimum number of User Answered Questions'. Once enough questions have been set, users may navigate away from the page, however if enough questions are not responded to, users will be directed back to the page to answer additional responses.

After initial responses are set, if users wish to add additional responses, or change current responses, they may do so by visiting User Preferences->Edit Security Question Responses, which will lead them back to the set_responses page.

Resetting User Lockouts
-----------------------
From the Site Administration Menu, navigate to Plugins->Security Questions->Reset Security Question Lockouts. This page allows site administrators to reset accounts that are locked out from resetting their password due to repeated failed security question verification. Administrators can click 'Clear Responses' next to a locked out users name in the table, to clear their responses if necessary, and then click 'Reset Lockout' and clear the lockout counter, so users will have a fresh set of attempts at answering the prompted questions. If a user has their responses cleared, the next time the user logs in, they will be prompted set new responses. They will be able to dismiss this prompt however, and will not be prompted to answer any questions on attempting to reset their password, so they will not be denied from accessing the service due to lack of password and security questions.

Installation
------------

### Installation

To install the plugin simply drop it into the /path/to/moodle/admin/tool/securityquestions directory. When moodle is accessed it will prompt for installation of the plugin. Press upgrade database now, and the plugin will be installed.

When the plugin is first installed, it will start disabled. To enable the plugin, setup steps must be performed. First, the admin settings must be configured to, with each [Security Control](#security-controls) set to a value. The defaults generally provide a sane baseline. After this, security questions must be set, for users to respond to. It is highly recommended to create **original questions**, and avoid common security questions such as "What is your mother's maiden name?".

Once enough questions have been set, the admin account will be prompted to set its security questions, which indicates that the plugin is active.

For more instructions on installation, visit [the Moodle Plugin Installation Guide](https://docs.moodle.org/37/en/Installing_plugins)

Branches
--------

| Branch             | Moodle version    | PHP Version |
| ------------------ | ----------------- | ----------- |
| MOODLE_401_STABLE  | Moodle 4.1+       | Php 7.4+    |
| master             | Moodle 3.8 - 4.1  | Php 7.1     |

The master branch will work natively with Moodle from version 3.8 onwards, but has soft support for earlier versions with backports for hooks. If a previous version is used, the commit from MDL-66173 can be backported to a previous installation, and the plugin will be functional.

```
git cherry-pick dc25b71d8bb7ad95aea4510666385c74669316ec
```

Another requirement is for MDL-60470 to be included in the Moodle installation, which was added in Moodle version 3.7. If this commit is not present, users will not be prompted to set their security questions when they login after plugin setup.
```
git cherry-pick bf9f255523e5f8feb7cb39067475389ba260ff4e
```

Templates
---------

### Config Templates

This plugin can make use of template files in order to force configs, and disallow anyone from being able to change the amount of questions required to perform password resets, as well as the amount of questions users are required to respond to. The template files are within the folder `/config_policies/` inside of the plugin directory. To use these templates, edit your config.php file in the main Moodle directory, and add the lines:

```php
require(__DIR__.'/admin/tool/securityquestions/config_policies/<TEMPLATE HERE>.php');
```

where <TEMPLATE HERE> is the name of template file to use, such as forced-on.php. To verify that a template has been applied, visit the main Admin Settings menu for the plugin. There will be a header notification displaying details about the template. This means that the template is active. Settings on the admin menu will not be able to be changed.

### Question Templates

This plugin can also make use of question template files. These files should be added into the `/question/` directory inside of the main plugin directory, but can live in any directory inside of the moodle directory. The questions should be a single questions per line, and the file should be a text file. There is an example file inside of the directory, to illustrate the required structure. **DO NOT** use this file as a question template file in a production environment.

When a question template file is loaded, all of questions in the file will be forced active, until the file is removed from the control on the admin settins menu. If the template is removed, all of the questions will remain active, and must be manually deprecated if any are to be removed. You can add additional questions on top of a template file, which will not be affected by the status of a template. If a template file is loaded that does not have enough questions to satisfy the Security Control "Minimum Number of Active Security Questions", more questions will have to be manually added to meet the minimum amount, before the plugin will become active.

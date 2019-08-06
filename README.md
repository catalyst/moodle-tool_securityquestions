<a href="https://travis-ci.org/Peterburnett/moodle-tool_securityquestions">
<img src="https://travis-ci.org/Peterburnett/moodle-tool_securityquestions.svg?branch=master">
</a>

# Security Questions 2FA

This plugin adds a framework for adding and enforcing security questions for users to perform certain account security actions, such as changing or recovering passwords.

* [Security Controls](#security-controls)
* [Setting Questions](#modify-security-questions)
* [Setting Responses](#setting-question-reponses)
* [Installation](#installation)

Security Controls
-----------------
**Minimum Number of Active Security Questions:** This control is the minimum number of active security questions that must be active at one time. If there aren't enough questions active, users will not be prompted to set or answer questions during use of the moodle instance. This allows for diversity of questions and responses, as users do not have to answer all of the questions, in a typical installation. Defaults to 10.

**Minimum Number of User Answered Questions:** This control is the minimum number of active questions that a user must answer, in order to be able to use the Moodle instance. If users do not meet the number of answered active questions, they will be prompted to answer additional questions, until they meet the minimum number of active questions. If a question is deprecated, and will no longer be used for authentication, users will be prompted to answer additional questions until the minimum is met. Defaults to 5.

**Questions Required for Verification:** This control is the minimum number of questions that users must answer when they attempt to perform security related account actions, such as changing or recovering a password. Users must answer these questions correctly in the same session. E.g. users will be presented with *n* security questions that they have provided responses to. They must answer both correctly on the same page, and submit at the same time, to proceed to account security controls. If a user fails the security questions, they will be presented with another set of questions, until they succeed, or fail 3 times, at which point they will be unable to perform any account based actions that require higher security verification. Users most contact an Administrator to unlock their questions.

**Question Duration** When users are presented with questions that need to be answered for verification, they will be active for a certain period of time. This control specifies the duration of the active period. When a user first visits a page that requires verification, the questions will be generated, and presented to the user. This is the start point of the period. If users reload the page, or visit other pages that require verification, they will be presented the same question, until the end of the period. Once the period has passed, and a user visits a page that requires verification, they will be presented with fresh questions.

**Injection Points for Security Questions** This control allows administrators to set all of the forms that they wish the security questions plugin to inject into. These forms will require answering of security questions before the form can validate, and the action can be completed. Without any forms being selected here, the plugin will not inject into any points, and will not be enabled, so it is critical that desired forms are selected here.

Modify Security Questions
-------------------------
This page allows an admin to set the security questions for use in the Moodle instance. Entering a question at the top, and clicking Submit Question, will add this questions to the active question pool. Questions by default start not deprecated, until the questions are manually deprecated by an Admin. The plugin will not be enabled until the number of active security questions is equal to or higher than the number set in the 'Minimum number of active security questions' security control.

At the bottom of this page, there is a field for entering question ID's. When the form is submitted, if that field is populated with a valid question ID, that question will be deprecated. This removes the question from the active questions pool, after which users won't be able to set new responses to this question, and the question won't be able to be used to authenticate. When a question has been deprecated, if any users are now under the amount of required questions answered, they will be prompted to set addition responses. Admins will be unable to deprecate a question if it puts the amount of active questions under the required amount. More questions must be added first to deprecate a question.

Setting Question Responses
--------------------------
When a user first logs in after the plugin installation, if the plugin is active, with enough questions being set, users will be prompted to set responses to the security questions. They must set enough responses set to satisfy the security control 'Minimum number of User Answered Questions'. Once enough questions have been set, users may navigate away from the page, however if enough questions are not responded to, users will be directed back to the page to answer additional responses.

After initial responses are set, if users wish to add additional responses, or change current responses, they may do so by visiting User Preferences->Edit Security Question Responses, which will lead them back to the set_responses page.

When Questions are Presented
----------------------------
In the above section [Security Controls](#security-controls), there is a secion of controls **Injection Points for Security Questions** that has a list of callbacks that the plugin should inject verification into. Any section that is enabled in this control will have security questions injected into the form definition for these functions. If no forms are selected, users will be prompted to set their questions, and will be able to modify them, but they will never be required to answer questions. Therefore it is essential that forms are selected to be injected into.

Installation
------------
**Requirements:** This plugin will work natively with Moodle from version 3.8 onwards, as this is the version that the hooks required for plugin functionality were added into Moodle core, from tracker MDL-66173. If a previous version is used, the commit from MDL-66173 can be backported to a previous installation, and the plugin will be functional.

Another requirement is for MDL-60470 to be included in the Moodle installation, which was added in Moodle version 3.7. If this commit is not present, users will not be prompted to set their security questions when they login after plugin setup.+
 
**Installation:**
To install the plugin simply drop it into the /path/to/moodle/admin/tool/securityquestions directory. When moodle is accessed it will prompt for installation of the plugin. Press upgrade database now, and the plugin will be installed.

When the plugin is first installed, it will start disabled. To enable the plugin, setup steps must be performed. First, the admin settings must be configured to, with each [Security Control](#security-controls) set to a value. The defaults generally provide a sane baseline. After this, security questions must be set, for users to respond to. It is highly recommended to create **original questions**, and avoid common security questions such as "What is your mother's maiden name?".

Once enough questions have been set, the admin account will be prompted to set its security questions, which indicates that the plugin is almost active. The last thing to check is to go back to the plugin admin settings, and select all of the pages that users should be prompted security questions on.

For more instructions on installation, visit [the Moodle Plugin Installation Guide](https://docs.moodle.org/37/en/Installing_plugins)

Templates
---------
This plugin can make use of template files in order to force configs, and disallow anyone from being able to change the amount of questions required to perform password resets, as well as the amount of questions users are required to respond to. The template files are within the folder `config_policies` inside of the plugin directory. To use these templates, edit your config.php file in the main Moodle directory, and add the lines:

```php
require(__DIR__.'/admin/tool/securityquestions/config_policies/<TEMPLATE HERE>.php');
```

where <TEMPLATE HERE> is the name of template file to use, such as forced-on.php. To verify that a template has been applied, visit the main Admin Settings menu for the plugin. There will be a header notification displaying details about the template. This means that the template is active. Settings on the admin menu will not be able to be changed.



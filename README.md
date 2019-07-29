# Security Questions 2FA

This plugin adds a framework for adding and enforcing security questions for users to perform certain account security actions, such as changing or recovering passwords.

* [Security Controls](#security-controls)
* [Setting Questions](#setting-questions)
* [Setting Responses](#setting-reponses)
* [Installation](#installation)

Security Controls
-----------------
**Minimum Number of Active Security Questions:** This control is the minimum number of active security questions that must be active at one time. If there aren't enough questions active, users will not be prompted to set or answer questions during use of the moodle instance. This allows for diversity of questions and responses, as users do not have to answer all of the questions, in a typical installation. Defaults to 10.

**Minimum Number of User Answered Questions:** This control is the minimum number of active questions that a user must answer, in order to be able to use the Moodle instance. If users do not meet the number of answered active questions, they will be prompted to answer additional questions, until they meet the minimum number of active questions. If a question is deprecated, and will no longer be used for authentication, users will be prompted to answer additional questions until the minimum is met. Defaults to 5.

**Questions Required for Verification:** This control is the minimum number of questions that users must answer when they attempt to perform security related account actions, such as changing or recovering a password. Users must answer these questions correctly in the same session. E.g. users will be presented with *n* security questions that they have provided responses to. They must answer both correctly on the same page, and submit at the same time, to proceed to account security controls. If a user fails the security questions, they will be presented with another set of questions, until they succeed, or fail 3 times, at which point they will be unable to perform any account based actions that require higher security verification. Users most contact an Administrator to unlock their questions.

Modify Security Questions
-------------------------
This page allows an admin to set the security questions for use in the Moodle instance. Entering a question at the top, and clicking Submit Question, will add this questions to the active question pool. Questions by default start not deprecated, until the questions are manually deprecated by an Admin. The plugin will not be enabled until the number of active security questions is equal to or higher than the number set in the 'Minimum number of active security questions' security control.

At the bottom of this page, there is a field for entering question ID's. When the form is submitted, if that field is populated with a valid question ID, that question will be deprecated. This removes the question from the active questions pool, after which users won't be able to set new responses to this question, and the question won't be able to be used to authenticate. When a question has been deprecated, if any users are now under the amount of required questions answered, they will be prompted to set addition responses. Admins will be unable to deprecate a question if it puts the amount of active questions under the required amount. More questions must be added first to deprecate a question.

Setting Question Responses
--------------------------
When a user first logs in after the plugin installation, if the plugin is active, with enough questions being set, users will be prompted to set responses to the security questions. They must set enough responses set to satisfy the security control 'Minimum number of User Answered Questions'. Once enough questions have been set, users may navigate away from the page, however if enough questions are not responded to, users will be directed back to the page to answer additional responses.


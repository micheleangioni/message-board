# MESSAGE BOARD

## Introduction

[...]

## Installation

Message Board can be installed through Composer. Actually it is a private repo, so repo access is needed.

## Configuration

User model must implement `TopGames\MessageBoard\MbUserInterface` and therefore have `getPrimaryId()` and `getUsername()` methods.






-----

Once Achievements has been installed, add `'TopGames\Achievements\AchievementsServiceProvider',` to the app.php file.

Create table migration by running `php artisan achievements:migration`, then run the migration `php artisan migrate`.

Then add the UserAchievementsTrait to the model defined in the `'user_model'` config.php key, like this:

    <?php
    use TopGames\Achievements\UserAchievementsTrait;

    class User extends Eloquent
    {
        use UserAchievementsTrait;
    }



## Usage







In your code, you can fire an event when you want to assign an achievement to an user. Prepare the achievement data through an array like
```$data = array('name' => 'League_Wins_2');``` where `name` is the name on the Achievement.

## Optional

[...]




## Contribution guidelines

Feel free to propose pull requests.

## Contacts

Contact repo author.
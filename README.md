# Learning Platform

A comprehensive learning platform built with Laravel. This platform allows professors to create courses and lessons, and students to enroll and track their progress.

## Features

-   User authentication (students and professors)
-   Course management
-   Lesson content
-   Interactive quizzes with multiple question types
-   Progress tracking
-   Gamification (XP points and badges)
-   Activity streaks

## API Endpoints

### Authentication

-   `POST /api/register` - Register a new user
-   `POST /api/login` - Login a user
-   `POST /api/logout` - Logout a user (requires authentication)
-   `GET /api/user` - Get authenticated user information

## Installation

1. Clone the repository
2. Run `composer install`
3. Copy `.env.example` to `.env` and configure your database
4. Run `php artisan key:generate`
5. Run `php artisan migrate --seed`
6. Run `php artisan serve`

## Test Accounts

-   Admin: admin@example.com / password
-   Student: student@example.com / password

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

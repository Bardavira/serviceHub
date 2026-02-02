# ServiceHub — Laravel + Inertia (Vue) Technical Challenge

ServiceHub is a small helpdesk-style application built as a technical challenge.
It models a simplified company support workflow, where a user can create tickets (optionally attaching files) and background jobs enrich/process ticket data asynchronously.

The project is designed to be fully runnable with Docker and evaluator-friendly: a single command boots everything.

## Challenge Objective

This system represents a simplified support/ticketing domain:

- A Company owns multiple Projects
- Users belong to a company
- Users can create Tickets under a project
- A ticket may optionally include an Attachment
    - If there is an Attachment, a Job is dispatched to enrich the Ticket Details
- After ticket creation, a Queue Job is dispatched to notify the assignee of a new Ticket under their name

## Features Implemented

The following were added as optionals for the challenge
- Docker container for easy setup on any OS
- Automated tests using PHPUnit
- Separate volume for queue working

## Tech Stack

- PHP / Laravel
- MySQL 8
- Inertia.js + Vue
- PHPUnit tests (php artisan test)
- Docker + Docker Compose (multi-container)

## Docker Setup (Single Command)

The entire project runs via Docker, including the following volumes:

- nginx (web server)
- app (Laravel app container)
- queue (Laravel queue worker container)
- db (MySQL 8)
- node (Vite / frontend build server)

To start:

````bash
docker compose up -d --build
````

Then open:

- App: http://localhost:8000

## Folder Structure

````txt
.
├── docker/
│ ├── php/   (PHP image (app + queue))
│ ├── node/  (Node/Vite image)
│ └── nginx/ (Web Server)
├── src/ # Laravel application source code
└── docker-compose.yml
````

## Running Tests

Run PHPUnit tests inside the app container:

````bash
docker compose exec app php artisan test
````

## Seeded Demo Data (Login)

The project includes seeded demo data for evaluator convenience:

- 1 company
- 2 projects
- 1 user (demo login)

After running the containers, login at:

- http://localhost:8000

Credentials are seeded by the database seeder.
If needed, check the seeded user in DatabaseSeeder or UserSeeder inside src/database/seeders.

## Main Routes

- / (Home / Welcome page)
- /dashboard
- /tickets (index)
- /tickets/create
- /tickets/{ticket}


## License

This project was created for a technical challenge / evaluation process.
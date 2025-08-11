# How to run the case study


> **Before using the app, you must create an API key in the database.**  
> All API requests require a valid API key for authentication.

> **API documentation is available at [http://localhost:8000/doc](http://localhost:8000/doc).**

> **Tests can be run by calling: ./vendor/bin/phpunit tests**


> **Note:** The documentation was not clear about the `title` field in the Author entity. I decided to follow the rules and make it required, with a default value of `unknown`.
# Case Study: Backend Engineering (PHP)
---

⚠️ PLEASE DO NOT FORK THIS REPO AS OTHERS MAY SEE YOUR CODE. INSTEAD YOU SHOULD
[USE THIS TEMPLATE](https://github.com/new?template_name=case-study-backend-engineering-php&template_owner=MDPI-AG)
TO CREATE YOUR OWN REPOSITORY.

---

## Getting Started

This repository contains the skeleton of a Symfony 6.4 application that serves as the basis for the case study.

> **We recommend that you spend 2-3 hours on this case study, as it is meant to be a quick evaluation of your skills. If you are not finished in that time, please submit what you have done so far, and we will evaluate your progress.**

The application has been Dockerized and is ready to be used with Docker Compose. If you do not have Docker installed,
please follow the [installation instructions](https://docs.docker.com/get-docker/) to get `Docker Desktop`.

Make sure the following ports are available on your machine (you may need to shut down any services that are
using them):

- Port 8000 for the PHP application server
- Port 3306 for the MySQL database server
- Port 8080 for the PHPMyAdmin interface

Then, start the Docker stack using Docker Compose:

```bash
docker-compose up -d
```

This will start the Dockerized stack, which includes:

- A PHP 8.2 application server running Symfony 6.4, accessible at [http://localhost:8000](http://localhost:8000)
- A MySQL 8.0 database server
- A PHPMyAdmin interface for managing the MySQL database via a GUI, accessible at [http://localhost:8080](http://localhost:8080)

To sign in to PHPMyAdmin, use the following credentials (we know this ain't secure, but it's just a case study!):

- **Username:** `root`
- **Password:** `root`

The application sources are volume-mounted into the PHP container under `/var/www/html` in the container.

To follow the logs from the Docker stack, you can use:

```bash
docker-compose logs -f
```

To log into the Symfony PHP container, you can use:

```bash
docker exec -ti symfony_app bash
```

There you can run the usual Composer and Symfony commands, such as:

```bash
composer install
php bin/console list
```

## Allowed Tools

You can use any tools you like (includ GitHub Copilot, ChatGPT, a proper IDE like PhpStorm / VS Code etc.) to
complete the case study. During the technical interview, you will be asked to explain your code and the decisions
you made.

We recommend spending no more than 2-3 hours on this case study, as it is meant to be a quick evaluation of
your skills.

---

## 📝 Tasks: Build a REST API for Managing "Books"

### Context

You are to implement a small REST API for managing a collection of books in a library system. The goal is to evaluate
your skills with Symfony, API design, entity modeling, and basic authentication.

### Task Description

#### 1. Entities: Book and Author

There is a basic Book entity which is using Doctrine ORM. First, update the database schema so that the Book entity
is actually created in the MySQL database. Then you can import the sample data from `./data/book.sql` into the
database via PHPMyAdmin (or the MySQL CLI if you log to the MySQL docker container).

Additoinally, create a new `Author` entity that has a many-to-many relationship with the Book entity. It should have
the following props:

| Property  | Type   | Details                     |
| --------- | ------ | --------------------------- |
| id        | UUID   | Primary key, auto-generated |
| title     | string | Required, max length 10    |
| firstname | string | Required, max length 100    |
| lastname  | string | Required, max length 100    |

Once you have introduced the new entity, update your database schema agian.

#### 2. Refactor Controller Endpoint

A previous colleague that left the company has implemented a controller method for a one-time data migration.
The engineering manager dislikes this approach for obvious reasons (he does not like this to be exposed in an API
controller). Please refactor the controller method to use another more appropriate approach (command, service. etc.).

Bear in mind that the example data is relatively small, but in a production context, the database could contain
millions of records for books -- please consider this fact in the design. Also, you need to slightly rewrite the
logic to account for the new `Author` entity.

Please be ready to explain your refactoring decisions during the technical interview.

#### 3. Basic API Key Authentication

Requests without the correct API key should return HTTP 401 Unauthorized.

**_Naive Approach:_**

Implement a simple middleware (Symfony event listener or authenticator) that requires all API requests
to include an HTTP header `X-API-KEY` with a predefined API key. We assume here that we have only one
API client, i.e., only one API key.

```
X-API-KEY: your_api_key_here
```

**_Advanced Approach:_**

You are free to implement a more advanced API key authentication mechanism, such as using a database table
to store clients and/or API keys, allowing for multiple clients, or implementing a more secure authentication
method like JWT (JSON Web Tokens) etc.

#### 4. Build some basic REST API Endpoints

Build these endpoints for the Book resource:

| HTTP Method | URL         | Description           | Request Body   | Response                     |
| ----------- | ----------- | --------------------- | -------------- | ---------------------------- |
| GET         | /books      | List all books        | None           | JSON array of books          |
| GET         | /books/{id} | Get details of a book | None           | JSON object of book          |
| POST        | /books      | Create a new book     | JSON book data | JSON created book + HTTP 201 |

For the POST request, the JSON body should include the following fields:

```json
{
  "title": "Book Title",
  "authors": [{
    "firstname": "Author Firstname",
    "lastname": "Author Lastname"
  }],
  "published": "2023-01-01",
  "isbn": "1234567890"
}
```

Make sure the same authors and same books are not created multiple times. If an author already exists,
you should use that existing author instead of creating a new one.

Some best practices to follow:

- stick to good RESTful practices
- error handling, i.e., expect wrong input or services that fail
- return appropriate HTTP status codes (e.g., 201 for created, 404 for not found, 400 for bad request, etc.)
- use Symfony Serializer to convert entities to JSON
- avoid any business logic in the controllers; use services or repositories instead - as appropriate

#### 5. Data Validation

Add data validation to your API endpoints using Symfony Validator component. The validation should include:

- Validate required fields
- Validate fields that are supposed to be unique fields
- Validate the max lengths for strings
- Validate authorships (e.g., author is given, and author must have a first and last name).
- Validate dates

#### 6. Refactor `SearchController`

The `SearchController` is a simple controller that allows searching for books by title or author passed via
the GET `q` param and basic sorting passed via the GET `sort`param. The controller is a mess and may not work
as intended. Please refactor it by making deliberate decisions on how to improve the functionality, code quality,
scoping, readability, maintainability, scaling, etc. We do not expect you to implement a full search engine (like
Solr, Elastic or OpenSearch), but rather rework the existing code base.

Please be ready to explain your refactoring decisions during the technical interview.

#### 7. Deliverables

Please commit your changes to your repository and ensure that the code is clean, well-structured, and follows Symfony
and other PHP best practices (e.g., PSR-12 coding standards, proper naming conventions, etc.). Make sure we can access
your repository (add the `rordi` GitHub user as a collaborator).

Your updated Symfony app source code should include:

- Book and Author entities with their Doctrine mappings
- Repository class or service if needed
- API Controllers and routes
- API Key authentication mechanism
- Validation logic
- Instructions on how to test the API (e.g., example curl commands, or OpenAPI/Swagger documentation if implemented)
- Basic test cases (optional, but a plus)

#### Bonus (optional)

- Advanced API Key authentication with a database table for clients and/or API keys.
- Add pagination on the GET /api/books endpoint.
- Add OpenAPI/Swagger documentation for the API.
- Unit test coverage and application tests for the API endpoints with PHPUnit / WebTestCase.

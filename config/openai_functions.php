<?php

return [
    // Function definitions for OpenAI function-calling
    'functions' => [
        [
            'name' => 'find_movie',
            'description' => 'Extract structured slots to search movies (title, genre, actor, year, language, format, limit).',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'title' => [ 'type' => 'string', 'description' => 'Movie title or partial; empty if not provided.' ],
                    'genre' => [ 'type' => 'string' ],
                    'actor' => [ 'type' => 'string' ],
                    'year' => [ 'type' => 'integer' ],
                    'language' => [ 'type' => 'string' ],
                    'format' => [ 'type' => 'string' ],
                    'sort_by' => [ 'type' => 'string' ],
                    'limit' => [ 'type' => 'integer' ],
                ],
                'required' => [],
            ],
        ],
        [
            'name' => 'find_showtime',
            'description' => 'Extract structured slots for finding showtimes for a movie.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'movie_title' => [ 'type' => 'string' ],
                    'movie_id' => [ 'type' => 'integer' ],
                    'date' => [ 'type' => 'string', 'format' => 'date' ],
                    'time_range' => [ 'type' => 'string' ],
                    'city' => [ 'type' => 'string' ],
                    'theater' => [ 'type' => 'string' ],
                    'room' => [ 'type' => 'string' ],
                    'format' => [ 'type' => 'string' ],
                    'price_max' => [ 'type' => 'number' ],
                    'limit' => [ 'type' => 'integer' ],
                ],
                'required' => [],
            ],
        ],
    ],
];

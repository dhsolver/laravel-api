## API Docs

API documentation is generated using the [OpenAPI 2.0 specification](https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md).

| File          | Description                          |
|---------------|--------------------------------------|
| [docs/api.yaml](api.yaml) | The documentation source (OpenAPI)   |
| [docs/api.html](api.html) | The compiled/generated API docs      |


### Updating the docs

Documentation is generated with [Redoc](https://github.com/Rebilly/ReDoc) using the [redoc-cli](https://github.com/Rebilly/ReDoc/blob/master/cli/README.md) tool.

First, install redoc-cli globally:
```
npm install -g redoc-cli
```

Then you can update the docs/api.yaml file, and run the following command to generate an updated version of the HTML docs:
**Make sure you have the global npm bin directory in your PATH
```
php artisan docs:generate
```

This should display a success message and overwrite the docs/api.html documentation file.

### Change Log
v.1.1.6
 - Updated mobile/tours/{tour_id}/track endpoint (added begin_timestamp, end_timestamp)
 - Updated mobile/stops/{stop_id}/track endpoint (added begin_timestamp, end_timestamp)
 - Fixed minor syntax errors in documentation

v.1.1.5
- Added mobile/tours/anon/{tour_id} endpoint for standalone apps
- Added prize_location field to mobile/tours/{tour_id} endpoint
- Maded required fields for mobile/profile/{user_id} endpoint

v.1.1.4
- Changed created_at from date-time to timestamp for User object
- Added access token field for facebook login endpoint

v.1.1.3
- Changed mobile/tours/{tour_id}/track endpoint schema
- Changed mobile/stops/{stop_id}/track endpoint schema
- Changed 'device_uuid' to 'device_id'

v.1.1.2
- Added prize_location to mobile/tours/{tour_id} endpoint
- Added 'redeemed_prize' action to mobile/tours/{tour_id}/track endpoint

v.1.1.1
- Added user_rank, total_users to the leaderboard
- Deleted tour_id from the leaderboard
- Fixed issue with showing single user multiple times on leaderboards
- Changed mobile/scores to mobile/scores/{user_id} endpoint to show scores of specific user
- Changed mobile/favorites to mobile/favories/{user_id} to show favorited tours of specific user

v.1.1.0
- Added tour_id to the leaderboard
- Fixed issue with multiple scores of the same user showing on leaderboards

v.1.0.9
- Added all-time leaderboard endpoint

v.1.0.8
- Added new favorites query paramter flag to Tour listing endpoint 
- Added favorites count to the user profile resource
- Added is_favorite flag to the Tour resource
- Added debug flag to submit review endpoint

v.1.0.7
- Added 'length' property to the Tour model
- Added zipcode field to signup and edit profile 
- Added endpoints for user favorites
- Added skipped_question flag to the progress tour endpoint

v.1.0.6
- Added prize_time_limit field to the Tour model
- Added UserStats model
- Added stats field to the Profile model
- Modified the text length of prize_instructions on tours from 255 to a text field (60k+)
- Added ScoreCard model
- Added Prize model
- Added LeadboardEntry model
- Added scoring and leaderboard endpoints

v.1.0.5
- Updated change password route to PATCH instead of POST

v 1.0.2
- Added subscribe_override boolean field to user profile model
- Added created_at, avatar_url, subscribe_override fields to the user session model (to match profile)
- Added in_app_id to Tour model
- Added analytics endpoints for tracking tour and stop activity
- Added debug query parameter for tours (allows you to see the current logged in users unpublished tours)

v 1.0.1
- fb_id field added to auth user response
- Previous auth endpoint named 'Profile' changed to 'User Session'
- Added view / edit user profile endpoints
- Added a new Profile resource
- Added endpoint to create/update/delete tour reviews
- Added Review model
- Adding 'rating' field to Tours model
- Added 'latest_reviews' collection to single tour endpoint
- Added endpoint to paginate Tour's reviews
- Added endpoint for user to change their password
